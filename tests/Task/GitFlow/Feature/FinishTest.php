<?php

namespace Globalis\Robo\Tests\Task\GitFlow\Feature;

use Globalis\Robo\Tests\Util;
use Globalis\Robo\Tests\GitWorkDir;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;

class FinishTest extends \PHPUnit\Framework\TestCase
{
    use \Globalis\Robo\Task\GitFlow\loadTasks;
    use TaskAccessor;
    use ContainerAwareTrait;

    protected $git;

    // Set up the Robo container so that we can create tasks in our tests.
    public function setUp()
    {
        $container = Robo::createDefaultContainer(null, new NullOutput());
        $this->setContainer($container);

        $this->git = GitWorkDir::getOrNew('git-flow-feature-finish');
        $this->git->toLocalDir();
        // Create feature branch
        Util::runProcess('git checkout -b feature_foo develop');
        file_put_contents($this->git->localWorkDir() . '/test', 'foo', FILE_APPEND);
        Util::runProcess('git add .');
        Util::runProcess('git commit -m "test_feature_foo"');
        Util::runProcess('git push origin feature_foo');
    }

    public function tearDown()
    {
        // Delete feature branch
        $this->git->toRemoteDir();
        Util::runProcessWithoutException('git branch -D feature_foo');

        $this->git->toLocalDir();
        Util::runProcess('git checkout develop');
        Util::runProcess('git reset --hard origin/develop');
        Util::runProcessWithoutException('git branch -D feature_foo');
    }

    // Scaffold the collection builder
    public function collectionBuilder()
    {
        $emptyRobofile = new \Robo\Tasks();
        return $this->getContainer()->get('collectionBuilder', [$emptyRobofile]);
    }

    public function testRunFeatureBranchNotExist()
    {
        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branch 'feature_bar' does not exist and is required.");
        $this->taskFeatureFinish('bar')
            ->run();
    }

    public function testRunFeatureBranchDiverge()
    {
        file_put_contents($this->git->localWorkDir() . '/test', 'foo', FILE_APPEND);
        Util::runProcess('git add .');
        Util::runProcess('git commit -m "test"');

        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branches 'feature_foo' and 'origin/feature_foo' have diverged");
        $this->taskFeatureFinish('foo')
            ->run();
    }

    public function testRunDevelopBranchNotExist()
    {
        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branch 'bar' does not exist and is required.");
        $this->taskFeatureFinish('foo')
            ->developBranch('bar')
            ->run();
    }

    public function testRunDevelopBranchDiverge()
    {
        Util::runProcess('git checkout develop');
        file_put_contents($this->git->localWorkDir() . '/test', 'foo', FILE_APPEND);
        Util::runProcess('git add .');
        Util::runProcess('git commit -m "test"');

        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branches 'develop' and 'origin/develop' have diverged");
        $this->taskFeatureFinish('foo')
            ->run();
    }

    public function testRunWithDeleteBranch()
    {
        $this->taskFeatureFinish('foo')
            ->run();
        $r = trim(
            Util::runProcess("git branch -a")->getOutput()
        );
        $this->assertSame(
            [
                '* develop',
                '  master',
                '  remotes/origin/develop',
                '  remotes/origin/master',
            ],
            explode(
                PHP_EOL,
                $r
            )
        );
    }

    public function testRunWithoutDeleteBranch()
    {
        $this->taskFeatureFinish('foo')
            ->deleteBranchAfter(false)
            ->run();
        $r = trim(
            Util::runProcess("git branch -a")->getOutput()
        );
        $this->assertSame(
            [
                '* develop',
                '  feature_foo',
                '  master',
                '  remotes/origin/develop',
                '  remotes/origin/feature_foo',
                '  remotes/origin/master',
            ],
            explode(
                PHP_EOL,
                $r
            )
        );
    }
}
