<?php
namespace Globalis\Robo\Tests\Task\GitFlow\Release;

use Globalis\Robo\Tests\Util;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;

class StartTest extends \PHPUnit\Framework\TestCase
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
        static::$workDir = sys_get_temp_dir() . "/globalis-robo-tasks-tests-git-flow-start-release" . uniqid();
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
        // Delete release branch
        Util::runProcess('git checkout develop');
        Util::runProcess('git reset --hard origin/develop');
        Util::runProcessWithoutException('git branch -D release_foo');
        Util::runProcessWithoutException('git tag -d foo');
    }

    // Scaffold the collection builder
    public function collectionBuilder()
    {
        $emptyRobofile = new \Robo\Tasks;
        return $this->getContainer()->get('collectionBuilder', [$emptyRobofile]);
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
        file_put_contents(static::$localWorkDir . '/test', 'Test', FILE_APPEND);
        Util::runProcess('git add .', static::$localWorkDir);
        Util::runProcess('git commit -m "test"', static::$localWorkDir);

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
                static::$localWorkDir
            )->getOutput())
        );
    }
}
