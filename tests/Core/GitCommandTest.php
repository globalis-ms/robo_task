<?php

namespace Globalis\Robo\Tests\Core;

use Globalis\Robo\Core\GitCommand;
use Globalis\Robo\Tests\GitWorkDir;
use Globalis\Robo\Tests\Util;

class GitCommandTest extends \PHPUnit\Framework\TestCase
{
    protected $git;

    public function setUp()
    {
        $this->git = GitWorkDir::getOrNew('git-command');
        $this->git->toLocalDir();
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
        $cmd = new GitCommand();
        $this->assertEquals(
            [
                'origin/develop',
                'origin/master',
            ],
            $cmd->getRemoteBranches()
        );
    }

    public function testRemoteBranchExists()
    {
        $cmd = new GitCommand();
        $this->assertEquals(false, $cmd->remoteBranchExists('bar', 'foo'));
        $this->assertEquals(true, $cmd->remoteBranchExists('origin', 'master'));
    }

    public function testGetLocalBranches()
    {
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
        $cmd = new GitCommand();
        $this->assertEquals(false, $cmd->localBranchExists('foo'));
        $this->assertEquals(true, $cmd->localBranchExists('master'));
    }

    public function testGetAllBranches()
    {
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
        $cmd = new GitCommand();
        $this->assertEquals(false, $cmd->branchExists('foo'));
        $this->assertEquals(true, $cmd->branchExists('master'));
    }

    public function testGetRemotes()
    {
        $cmd = new GitCommand();
        $this->assertEquals(['origin'], $cmd->getRemotes('foo'));
    }

    public function testBranchesEqual()
    {
        $cmd = new GitCommand();
        $this->assertEquals(true, $cmd->branchesEqual('master', 'develop'));

        Util::runProcess('git checkout develop');
        file_put_contents($this->git->localWorkDir() . '/testsBranchesEqual', 'Test');
        Util::runProcess('git add .');
        Util::runProcess('git commit -m "test"');

        $this->assertEquals(false, $cmd->branchesEqual('master', 'develop'));

        Util::runProcess('git reset HEAD~');
        Util::rmDir($this->git->localWorkDir() . '/testsBranchesEqual');
    }

    public function testCheckout()
    {
        $cmd = new GitCommand();
        $this->assertEquals(true, $cmd->checkout('master'));
        $this->assertEquals(
            'master',
            trim(Util::runProcess("git branch | grep \* | cut -d ' ' -f2")->getOutput())
        );

        $this->assertEquals(true, $cmd->checkout('develop'));
        $this->assertEquals(
            'develop',
            trim(Util::runProcess("git branch | grep \* | cut -d ' ' -f2")->getOutput())
        );

        $this->assertEquals(false, $cmd->checkout('foo'));
    }

    /**
     * @depends testLocalBranchExists
     */
    public function testCreateBranch()
    {
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
        $cmd = new GitCommand();
        $cmd->push('origin', 'tmp');
        $this->assertEquals(true, $cmd->remoteBranchExists('origin', 'tmp'));
    }

    /**
     * @depends testPush
     */
    public function testDeleteBranch()
    {
        Util::runProcess('git checkout master');

        $cmd = new GitCommand();
        $cmd->deleteRemoteBranch('origin', 'tmp');
        $this->assertEquals(false, $cmd->remoteBranchExists('origin', 'tmp'));

        $cmd->deleteLocalBranch('tmp');
        $this->assertEquals(false, $cmd->localBranchExists('tmp'));
    }

    public function testCreateTag()
    {
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
        $cmd = new GitCommand();
        $this->assertEquals(['test_tag'], $cmd->getTags());
    }

    /**
     * @depends testCreateTag
     */
    public function testTagExists()
    {
        $cmd = new GitCommand();
        $this->assertEquals(true, $cmd->tagExists('test_tag'));
        $this->assertEquals(false, $cmd->tagExists('foo'));
    }

    /**
     * @depends testTagExists
     */
    public function testPushTags()
    {
        $cmd = new GitCommand();
        $cmd->pushTags('origin');
        $this->git->toRemoteDir();
        $this->assertEquals(true, $cmd->tagExists('test_tag'));
    }

    public function testIsCleanWorkingTree()
    {
        $cmd = new GitCommand();
        $this->assertEquals(true, $cmd->isCleanWorkingTree());
        file_put_contents($this->git->localWorkDir() . '/test', 'test_is_cleanworking_tree');
        $this->assertEquals(false, $cmd->isCleanWorkingTree());
        Util::runProcess('git checkout test');
    }

    public function testIsBranchMergeInto()
    {
        $cmd = new GitCommand();
        $this->assertEquals(true, $cmd->isBranchMergeInto('develop', 'master'));

        Util::runProcess('git checkout develop');
        file_put_contents($this->git->localWorkDir() . '/test', 'test_is_branch_merge_into');
        Util::runProcess('git add . && git commit -m "test"');
        $this->assertEquals(false, $cmd->isBranchMergeInto('develop', 'master'));
        Util::runProcess('git reset HEAD~ && git checkout test');
    }

    public function testRebase()
    {
        $cmd = new GitCommand();
        $cmd->fetchAll();
        $this->assertEquals(true, $cmd->rebase('master'));
        $this->assertEquals(false, $cmd->rebase('foo'));
    }
}
