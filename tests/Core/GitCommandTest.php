<?php
namespace Globalis\Robo\Tests\Core;

use Globalis\Robo\Core\GitCommand;
use Globalis\Robo\Tests\Util;

class GitCommandTest extends \PHPUnit_Framework_TestCase
{
    protected static $baseCwd;
    protected static $workDir;
    protected static $localWorkDir;
    protected static $remoteWorkDir;

    public static function setUpBeforeClass()
    {
        static::$baseCwd = getcwd();
        // Build tmp work dir
        static::$workDir = sys_get_temp_dir() . "/globalis-robo-tasks-tests-git" . uniqid();
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

    public function testConstructor()
    {
        $cmd = new GitCommand();
        $this->assertSame('git', $cmd->pathToGit);
        $cmd = new GitCommand('test');
        $this->assertSame('test', $cmd->pathToGit);
    }

    public function testGetRemoteBranches()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $this->assertEquals(
            [
                'origin/master'
            ],
            $cmd->getRemoteBranches()
        );
    }

    public function testRemoteBranchExists()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $this->assertEquals(false, $cmd->remoteBranchExists('bar', 'foo'));
        $this->assertEquals(true, $cmd->remoteBranchExists('origin', 'master'));
    }

    public function testGetLocalBranches()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $this->assertEquals(
            [
                'develop',
                'master',
            ],
            $cmd->getLocalBranches()
        );
    }

    public function testLocalBranchExists()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $this->assertEquals(false, $cmd->localBranchExists('foo'));
        $this->assertEquals(true, $cmd->localBranchExists('master'));
    }

    public function testGetAllBranches()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $this->assertEquals(
            [
                'develop',
                'master',
            ],
            $cmd->getAllBranches()
        );
    }

    public function testBranchExists()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $this->assertEquals(false, $cmd->branchExists('foo'));
        $this->assertEquals(true, $cmd->branchExists('master'));
    }

    public function testGetRemotes()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $this->assertEquals(['origin'], $cmd->getRemotes('foo'));
    }

    public function testBranchesEqual()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $this->assertEquals(true, $cmd->branchesEqual('master', 'develop'));

        Util::runProcess('git checkout develop', static::$localWorkDir);
        file_put_contents(static::$localWorkDir . '/testsBranchesEqual', 'Test');
        Util::runProcess('git add .', static::$localWorkDir);
        Util::runProcess('git commit -m "test"', static::$localWorkDir);

        $this->assertEquals(false, $cmd->branchesEqual('master', 'develop'));

        Util::runProcess('git reset HEAD~', static::$localWorkDir);
        Util::rmDir(static::$localWorkDir . '/testsBranchesEqual');
    }

    public function testCheckout()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $this->assertEquals(true, $cmd->checkout('master'));
        $this->assertEquals(
            'master',
            trim(Util::runProcess(
                "git branch | grep \* | cut -d ' ' -f2",
                static::$localWorkDir
            )->getOutput())
        );

        $this->assertEquals(true, $cmd->checkout('develop'));
        $this->assertEquals(
            'develop',
            trim(Util::runProcess(
                "git branch | grep \* | cut -d ' ' -f2",
                static::$localWorkDir
            )->getOutput())
        );

        $this->assertEquals(false, $cmd->checkout('foo'));
    }

    /**
     * @depends testLocalBranchExists
     */
    public function testCreateBranch()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $cmd->createBranch('tmp', 'master');
        $this->assertEquals(true, $cmd->localBranchExists('tmp'));
    }

    /**
     * @depends testCreateBranch
     * @depends testRemoteBranchExists
     */
    public function testPush()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $cmd->push('origin', 'tmp');
        $this->assertEquals(true, $cmd->remoteBranchExists('origin', 'tmp'));
    }

    /**
     * @depends testPush
     */
    public function testDeleteBranch()
    {
        $this->toLocalDir();
        Util::runProcess('git checkout master', static::$localWorkDir);

        $cmd = new GitCommand();
        $cmd->deleteRemoteBranch('origin', 'tmp');
        $this->assertEquals(false, $cmd->remoteBranchExists('origin', 'tmp'));

        $cmd->deleteLocalBranch('tmp');
        $this->assertEquals(false, $cmd->localBranchExists('tmp'));
    }

    public function testCreateTag()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $this->assertEquals(true, $cmd->createTag('test_tag', 'test_tag'));
        $this->expectException(\Exception::class);
        $cmd->createTag('test_tag');
    }

    /**
     * @depends testCreateTag
     */
    public function testGetTags()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $this->assertEquals(['test_tag'], $cmd->getTags());
    }

    /**
     * @depends testCreateTag
     */
    public function testTagExists()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $this->assertEquals(true, $cmd->tagExists('test_tag'));
        $this->assertEquals(false, $cmd->tagExists('foo'));
    }

    /**
     * @depends testTagExists
     */
    public function testPushTags()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $cmd->pushTags('origin');
        $this->toRemoteDir();
        $this->assertEquals(true, $cmd->tagExists('test_tag'));
    }

    public function testIsCleanWorkingTree()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $this->assertEquals(true, $cmd->isCleanWorkingTree());
        file_put_contents(static::$localWorkDir . '/test', 'test_is_cleanworking_tree');
        $this->assertEquals(false, $cmd->isCleanWorkingTree());
        Util::runProcess('git checkout test');
    }

    public function testIsBranchMergeInto()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $this->assertEquals(true, $cmd->isBranchMergeInto('develop', 'master'));

        Util::runProcess('git checkout develop');
        file_put_contents(static::$localWorkDir . '/test', 'test_is_branch_merge_into');
        Util::runProcess('git add . && git commit -m "test"');
        $this->assertEquals(false, $cmd->isBranchMergeInto('develop', 'master'));
        Util::runProcess('git reset HEAD~ && git checkout test');
    }

    public function testRebase()
    {
        $this->toLocalDir();
        $cmd = new GitCommand();
        $cmd->fetchAll();
        $this->assertEquals(true, $cmd->rebase('master'));
        $this->assertEquals(false, $cmd->rebase('foo'));
    }
}
