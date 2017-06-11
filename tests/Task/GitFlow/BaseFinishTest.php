<?php
namespace Globalis\Robo\Tests\Task\GitFlow;

use Globalis\Robo\Tests\Util;

class BaseFinishTest extends \PHPUnit\Framework\TestCase
{
    protected function getBaseMock()
    {
        return new BaseFinishStub('test');
    }

    public function testRebaseFlag()
    {
        $task = $this->getBaseMock();
        $this->assertSame(true, Util::getProtectedProperty($task, 'rebaseFlag'));
        $this->assertSame($task, $task->rebaseFlag(false));
        $this->assertSame(false, Util::getProtectedProperty($task, 'rebaseFlag'));
    }

    public function testDeleteBranchAfter()
    {
        $task = $this->getBaseMock();
        $this->assertSame(true, Util::getProtectedProperty($task, 'deleteBranchAfter'));
        $this->assertSame($task, $task->deleteBranchAfter(false));
        $this->assertSame(false, Util::getProtectedProperty($task, 'deleteBranchAfter'));
    }

    public function testPushFlag()
    {
        $task = $this->getBaseMock();
        $this->assertSame(true, Util::getProtectedProperty($task, 'pushFlag'));
        $this->assertSame($task, $task->pushFlag(false));
        $this->assertSame(false, Util::getProtectedProperty($task, 'pushFlag'));
    }
}

class BaseFinishStub extends \Globalis\Robo\Task\GitFlow\BaseFinish
{
    public function run()
    {
        return true;
    }
}
