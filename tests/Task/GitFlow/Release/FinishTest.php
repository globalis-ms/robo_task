<?php
namespace Globalis\Robo\Tests\Task\GitFlow\Release;

use Globalis\Robo\Tests\Util;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;

class FinishTest extends \PHPUnit_Framework_TestCase
{

    use \Globalis\Robo\Task\GitFlow\loadTasks;
    use TaskAccessor;
    use ContainerAwareTrait;

    protected static $baseCwd;
    protected static $workDir;
    protected static $gitEmail;
    protected static $gitName;
    protected static $localWorkDir;
    protected static $remoteWorkDir;

    public static function setUpBeforeClass()
    {
        static::$baseCwd = getcwd();
        // Build tmp work dir
        static::$workDir = sys_get_temp_dir() . "/globalis-robo-tasks-tests-git-flow-finish-release" . uniqid();
        mkdir(static::$workDir);

        // Initialise remote
        static::$remoteWorkDir = static::$workDir . '/remote';
        mkdir(static::$remoteWorkDir);
        Util::runProcess('git init --bare', static::$remoteWorkDir);

        // Initialise local
        static::$localWorkDir = static::$workDir . '/local';
        Util::runProcess('git clone ' . static::$remoteWorkDir . ' local', static::$workDir);

        // Prepare git local config
        Util::runProcess('git config user.email "you@example.com"', static::$localWorkDir);
        Util::runProcess('git config user.name "Your Name"', static::$localWorkDir);

        file_put_contents(static::$localWorkDir . '/test', 'Test');
        Util::runProcess('git add .', static::$localWorkDir);
        Util::runProcess('git commit -m "test"', static::$localWorkDir);
        Util::runProcess('git push origin master', static::$localWorkDir);
        Util::runProcess('git checkout -b develop master', static::$localWorkDir);
        Util::runProcess('git push origin develop', static::$localWorkDir);
    }

    public static function tearDownAfterClass()
    {
        chdir(static::$baseCwd);
        Util::rmDir(static::$workDir);
    }

    protected function toLocalDir()
    {
        chdir(static::$localWorkDir .'/');
    }

    protected function toRemoteDir()
    {
        chdir(static::$remoteWorkDir .'/');
    }

    // Set up the Robo container so that we can create tasks in our tests.
    public function setUp()
    {
        $container = Robo::createDefaultContainer(null, new NullOutput());
        $this->setContainer($container);

        $this->toLocalDir();
        // Create feature branch
        Util::runProcess('git checkout -b release_foo develop');
        file_put_contents(static::$localWorkDir . '/test', 'foo', FILE_APPEND);
        Util::runProcess('git add .');
        Util::runProcess('git commit -m "test_release_foo"');
        Util::runProcess('git push origin release_foo');
    }

    public function tearDown()
    {
        // Delete feature branch
        $this->toRemoteDir();
        Util::runProcess('git branch -D release_foo');

        $this->toLocalDir();
        Util::runProcess('git checkout master');
        Util::runProcess('git reset --hard origin/master');
        Util::runProcess('git checkout develop');
        Util::runProcess('git reset --hard origin/develop');
        Util::runProcess('git branch -D release_foo');
        Util::runProcess('git tag -d foo');
        Util::runProcess('git push origin :refs/tags/foo');
    }

    // Scaffold the collection builder
    public function collectionBuilder()
    {
        $emptyRobofile = new \Robo\Tasks;
        return $this->getContainer()->get('collectionBuilder', [$emptyRobofile]);
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
        file_put_contents(static::$localWorkDir . '/test', 'foo', FILE_APPEND);
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
        file_put_contents(static::$localWorkDir . '/test', 'foo', FILE_APPEND);
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
        file_put_contents(static::$localWorkDir . '/test', 'foo', FILE_APPEND);
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
