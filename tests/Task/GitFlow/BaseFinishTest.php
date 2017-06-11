<?php
namespace Globalis\Robo\Tests\Task\GitFlow;

class BaseFinishTest extends \PHPUnit_Framework_TestCase
{
    protected function getBaseMock()
    {
        return new BaseFinishStub('test');
    }

    protected function getProtectedProperty($object, $property)
    {
        $reflection = new \ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        return $reflection_property->getValue($object);
    }

    public function testRebaseFlag()
    {
        $task = $this->getBaseMock();
        $this->assertSame(true, $this->getProtectedProperty($task, 'rebaseFlag'));
        $this->assertSame($task, $task->rebaseFlag(false));
        $this->assertSame(false, $this->getProtectedProperty($task, 'rebaseFlag'));
    }

    public function testDeleteBranchAfter()
    {
        $task = $this->getBaseMock();
        $this->assertSame(true, $this->getProtectedProperty($task, 'deleteBranchAfter'));
        $this->assertSame($task, $task->deleteBranchAfter(false));
        $this->assertSame(false, $this->getProtectedProperty($task, 'deleteBranchAfter'));
    }

    public function testPushFlag()
    {
        $task = $this->getBaseMock();
        $this->assertSame(true, $this->getProtectedProperty($task, 'pushFlag'));
        $this->assertSame($task, $task->pushFlag(false));
        $this->assertSame(false, $this->getProtectedProperty($task, 'pushFlag'));
    }
}

class BaseFinishStub extends \Globalis\Robo\Task\GitFlow\BaseFinish
{
    public function run()
    {
        return true;
    }
}
