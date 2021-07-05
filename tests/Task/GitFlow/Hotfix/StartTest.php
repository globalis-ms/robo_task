<?php

namespace Globalis\Robo\Tests\Task\GitFlow\Hotfix;

use Globalis\Robo\Tests\Util;
use Globalis\Robo\Tests\GitWorkDir;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;

class StartTest extends \PHPUnit\Framework\TestCase
{
    use \Globalis\Robo\Task\GitFlow\Tasks;
    use TaskAccessor;
    use ContainerAwareTrait;

    protected $git;

    // Set up the Robo container so that we can create tasks in our tests.
    protected function setUp(): void
    {
        $container = Robo::createDefaultContainer(null, new NullOutput());
        $this->setContainer($container);

        $this->git = GitWorkDir::getOrNew('git-flow-hotfix-start');
        $this->git->toLocalDir();
        // Delete hotix branch
        Util::runProcess('git checkout master');
        Util::runProcess('git reset --hard origin/master');
        Util::runProcessWithoutException('git branch -D hotfix_foo');
        Util::runProcessWithoutException('git tag -d foo');
    }

    // Scaffold the collection builder
    public function collectionBuilder()
    {
        $emptyRobofile = new \Robo\Tasks();
        return $this->getContainer()->get('collectionBuilder', [$emptyRobofile]);
    }

    public function testRunFeatureBranchExists()
    {
        // Create feature branch
        Util::runProcess('git checkout -b hotfix_foo master');

        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branch 'hotfix_foo' already exists. Pick another name.");
        $this->taskHotfixStart('foo')
            ->run();
    }

    public function testRunMasterBranchNotExist()
    {
        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branch 'bar' does not exist and is required.");
        $this->taskHotfixStart('foo')
            ->masterBranch('bar')
            ->run();
    }

    public function testRunTagExists()
    {
        // Create release branch
        Util::runProcess('git tag foo');

        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Tag 'foo' already exists. Pick another name.");
        $this->taskHotfixStart('foo')
            ->run();
    }

    public function testRunMasterBranchDiverge()
    {
        Util::runProcess('git checkout master');
        file_put_contents($this->git->localWorkDir() . '/test', 'Test', FILE_APPEND);
        Util::runProcess('git add .', $this->git->localWorkDir());
        Util::runProcess('git commit -m "test"', $this->git->localWorkDir());

        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branches 'master' and 'origin/master' have diverged.");
        $this->taskHotfixStart('foo')
            ->run();
    }

    public function testRun()
    {
        $this->taskHotfixStart('foo')
            ->run();
        $this->assertEquals(
            'hotfix_foo',
            trim(Util::runProcess(
                "git branch | grep \* | cut -d ' ' -f2",
                $this->git->localWorkDir()
            )->getOutput())
        );
    }
}
