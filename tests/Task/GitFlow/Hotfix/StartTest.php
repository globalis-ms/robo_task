<?php
namespace Globalis\Robo\Tests\Task\GitFlow\Hotfix;

use Globalis\Robo\Tests\Util;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;

class StartTest extends \PHPUnit_Framework_TestCase
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
        static::$workDir = sys_get_temp_dir() . "/globalis-robo-tasks-tests-git-flow-start-hotfix" . uniqid();
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
        // Delete hotix branch
        Util::runProcess('git checkout master');
        Util::runProcess('git reset --hard origin/master');
        Util::runProcess('git branch -D hotfix_foo');
        Util::runProcess('git tag -d foo');
    }

    // Scaffold the collection builder
    public function collectionBuilder()
    {
        $emptyRobofile = new \Robo\Tasks;
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
        file_put_contents(static::$localWorkDir . '/test', 'Test', FILE_APPEND);
        Util::runProcess('git add .', static::$localWorkDir);
        Util::runProcess('git commit -m "test"', static::$localWorkDir);

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
                static::$localWorkDir
            )->getOutput())
        );
    }
}
