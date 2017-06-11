<?php
namespace Globalis\Robo\Tests\Task\GitFlow\Feature;

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
        static::$workDir = sys_get_temp_dir() . "/globalis-robo-tasks-tests-git-flow-finish-feature" . uniqid();
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
        Util::runProcess('git branch develop master', static::$localWorkDir);
        Util::runProcess('git checkout develop', static::$localWorkDir);
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
        Util::runProcess('git branch feature_foo develop');
        Util::runProcess('git checkout feature_foo');
        file_put_contents(static::$localWorkDir . '/test', 'foo', FILE_APPEND);
        Util::runProcess('git add .');
        Util::runProcess('git commit -m "test_feature_foo"');
        Util::runProcess('git push origin feature_foo');
    }

    public function tearDown()
    {
        // Delete feature branch
        $this->toRemoteDir();
        Util::runProcess('git branch -D feature_foo');

        $this->toLocalDir();
        Util::runProcess('git checkout develop');
        Util::runProcess('git reset --hard origin/develop');
        Util::runProcess('git branch -D feature_foo');
    }

    // Scaffold the collection builder
    public function collectionBuilder()
    {
        $emptyRobofile = new \Robo\Tasks;
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
        file_put_contents(static::$localWorkDir . '/test', 'foo', FILE_APPEND);
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
        file_put_contents(static::$localWorkDir . '/test', 'foo', FILE_APPEND);
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
