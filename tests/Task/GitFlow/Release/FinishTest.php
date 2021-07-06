<?php

namespace Globalis\Robo\Tests\Task\GitFlow\Release;

use Globalis\Robo\Tests\Util;
use Globalis\Robo\Tests\GitWorkDir;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\TaskAccessor;
use Robo\Robo;

class FinishTest extends \PHPUnit\Framework\TestCase implements ContainerAwareInterface
{
    use \Globalis\Robo\Task\GitFlow\Tasks;
    use TaskAccessor;
    use ContainerAwareTrait;

    protected $git;

    // Set up the Robo container so that we can create tasks in our tests.
    protected function setUp(): void
    {
        Robo::createContainer();
        $container = Robo::getContainer();
        Robo::finalizeContainer($container);
        $this->setContainer($container);

        $this->git = GitWorkDir::getOrNew('git-flow-release-finish');
        $this->git->toLocalDir();
        // Create feature branch
        Util::runProcess('git checkout -b release_foo develop');
        file_put_contents($this->git->localWorkDir() . '/test', 'foo', FILE_APPEND);
        Util::runProcess('git add .');
        Util::runProcess('git commit -m "test_release_foo"');
        Util::runProcess('git push origin release_foo');
    }

    protected function tearDown(): void
    {
        // Delete feature branch
        $this->git->toRemoteDir();
        Util::runProcessWithoutException('git branch -D release_foo');

        $this->git->toLocalDir();
        Util::runProcess('git checkout master');
        Util::runProcess('git reset --hard origin/master');
        Util::runProcess('git checkout develop');
        Util::runProcess('git reset --hard origin/develop');
        Util::runProcessWithoutException('git branch -D release_foo');
        Util::runProcessWithoutException('git tag -d foo');
        Util::runProcess('git push origin :refs/tags/foo');
    }

    // Scaffold the collection builder
    public function collectionBuilder()
    {
        $emptyRobofile = new \Robo\Tasks();
        $this->getContainer()->extend('collectionBuilder')->addArgument($emptyRobofile);
        return $this->getContainer()->get('collectionBuilder', true);
    }


    public function testRunReleaseBranchNotExist()
    {
        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branch 'release_bar' does not exist and is required.");
        $this->taskReleaseFinish('bar')
            ->run();
    }

    public function testRunTagExists()
    {
        Util::runProcess('git tag foo');
        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Tag 'foo' already exists. Pick another name.");
        $this->taskReleaseFinish('foo')
            ->run();
    }

    public function testRunReleaseBranchDiverge()
    {
        file_put_contents($this->git->localWorkDir() . '/test', 'foo', FILE_APPEND);
        Util::runProcess('git add .');
        Util::runProcess('git commit -m "test"');

        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branches 'release_foo' and 'origin/release_foo' have diverged");
        $this->taskReleaseFinish('foo')
            ->run();
    }

    public function testRunMasterBranchNotExist()
    {
        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branch 'bar' does not exist and is required.");
        $this->taskReleaseFinish('foo')
            ->masterBranch('bar')
            ->run();
    }

    public function testRunMasterBranchDiverge()
    {
        Util::runProcess('git checkout master');
        file_put_contents($this->git->localWorkDir() . '/test', 'foo', FILE_APPEND);
        Util::runProcess('git add .');
        Util::runProcess('git commit -m "test"');

        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branches 'master' and 'origin/master' have diverged");
        $this->taskReleaseFinish('foo')
            ->run();
    }

    public function testRunDevelopBranchNotExist()
    {
        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branch 'bar' does not exist and is required.");
        $this->taskReleaseFinish('foo')
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
        $this->taskReleaseFinish('foo')
            ->run();
    }

    public function testRunWithDeleteBranch()
    {
        $this->taskReleaseFinish('foo')
            ->tagMessage('bar')
            ->run();
        $r = trim(Util::runProcess("git branch -a")->getOutput());
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
        $r = trim(Util::runProcess("git tag -n --list foo")->getOutput());
        $this->assertSame(
            [
                'foo',
                'bar'
            ],
            array_values(
                array_filter(
                    explode(
                        ' ',
                        $r
                    )
                )
            )
        );
    }

    public function testRunWithoutDeleteBranch()
    {
        $this->taskReleaseFinish('foo')
            ->deleteBranchAfter(false)
            ->noTag(true)
            ->run();
        $r = trim(
            Util::runProcess("git branch -a")->getOutput()
        );
        $this->assertSame(
            [
                '* develop',
                '  master',
                '  release_foo',
                '  remotes/origin/develop',
                '  remotes/origin/master',
                '  remotes/origin/release_foo',
            ],
            explode(
                PHP_EOL,
                $r
            )
        );

        $r = trim(Util::runProcess("git tag -n --list foo")->getOutput());
        $this->assertSame(
            '',
            $r
        );
    }
}
