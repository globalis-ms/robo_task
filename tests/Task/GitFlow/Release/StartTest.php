<?php

namespace Globalis\Robo\Tests\Task\GitFlow\Release;

use Globalis\Robo\Tests\Util;
use Globalis\Robo\Tests\GitWorkDir;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;

class StartTest extends \PHPUnit\Framework\TestCase implements ContainerAwareInterface
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

        $this->git = GitWorkDir::getOrNew('git-flow-release-start');
        $this->git->toLocalDir();
        // Delete release branch
        Util::runProcess('git checkout develop');
        Util::runProcess('git reset --hard origin/develop');
        Util::runProcessWithoutException('git branch -D release_foo');
        Util::runProcessWithoutException('git tag -d foo');
    }

    // Scaffold the collection builder
    public function collectionBuilder()
    {
        $emptyRobofile = new \Robo\Tasks();
        $this->getContainer()->extend('collectionBuilder')->addArgument($emptyRobofile);
        return $this->getContainer()->get('collectionBuilder', true);
    }

    public function testRunReleaseBranchExists()
    {
        // Create release branch
        Util::runProcess('git checkout -b release_foo develop');

        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branch 'release_foo' already exists. Pick another name.");
        $this->taskReleaseStart('foo')
            ->run();
    }

    public function testRunTagExists()
    {
        // Create release branch
        Util::runProcess('git tag foo');

        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Tag 'foo' already exists. Pick another name.");
        $this->taskReleaseStart('foo')
            ->run();
    }

    public function testRunDevelopBranchNotExist()
    {
        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branch 'bar' does not exist and is required.");
        $this->taskReleaseStart('foo')
            ->developBranch('bar')
            ->run();
    }

    public function testRunDevelopBranchDiverge()
    {
        Util::runProcess('git checkout develop');
        file_put_contents($this->git->localWorkDir() . '/test', 'Test', FILE_APPEND);
        Util::runProcess('git add .', $this->git->localWorkDir());
        Util::runProcess('git commit -m "test"', $this->git->localWorkDir());

        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branches 'develop' and 'origin/develop' have diverged.");
        $this->taskReleaseStart('foo')
            ->run();
    }

    public function testRun()
    {
        $this->taskReleaseStart('foo')
            ->run();
        $this->assertEquals(
            'release_foo',
            trim(Util::runProcess(
                "git branch | grep \* | cut -d ' ' -f2",
                $this->git->localWorkDir()
            )->getOutput())
        );
    }
}
