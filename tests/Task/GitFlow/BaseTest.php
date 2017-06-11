<?php
namespace Globalis\Robo\Tests\Task\GitFlow;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    protected function getBaseMock()
    {
        return new BaseStub('test');
    }

    protected function getProtectedProperty($object, $property)
    {
        $reflection = new \ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        return $reflection_property->getValue($object);
    }

    public function invokeMethod($object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testConstructor()
    {
        $task = new BaseStub('test');
        $this->assertSame('test', $this->getProtectedProperty($task, 'name'));
        $this->assertSame('git', $this->getProtectedProperty($task, 'pathToGit'));
        $this->assertSame('origin', $this->getProtectedProperty($task, 'repository'));
        $this->assertSame('develop', $this->getProtectedProperty($task, 'developBranch'));
        $this->assertSame('master', $this->getProtectedProperty($task, 'masterBranch'));
        $this->assertSame('', $this->getProtectedProperty($task, 'prefixBranch'));
        $this->assertSame(true, $this->getProtectedProperty($task, 'fetchFlag'));

        $task = new BaseStub('test', 'git_cmd');
        $this->assertSame('git_cmd', $this->getProtectedProperty($task, 'pathToGit'));
    }

    public function testRepository()
    {
        $task = $this->getBaseMock();
        $this->assertSame($task, $task->repository('foo'));
        $this->assertSame('foo', $this->getProtectedProperty($task, 'repository'));
    }

    public function testDevelopBranch()
    {
        $task = $this->getBaseMock();
        $this->assertSame($task, $task->developBranch('foo'));
        $this->assertSame('foo', $this->getProtectedProperty($task, 'developBranch'));
    }

    public function testMasterBranch()
    {
        $task = $this->getBaseMock();
        $this->assertSame($task, $task->masterBranch('foo'));
        $this->assertSame('foo', $this->getProtectedProperty($task, 'masterBranch'));
    }

    public function testPrefixBranch()
    {
        $task = $this->getBaseMock();
        $this->assertSame($task, $task->prefixBranch('foo'));
        $this->assertSame('foo', $this->getProtectedProperty($task, 'prefixBranch'));
    }

    public function testFetchFlag()
    {
        $task = $this->getBaseMock();
        $this->assertSame($task, $task->fetchFlag(false));
        $this->assertSame(false, $this->getProtectedProperty($task, 'fetchFlag'));
    }

    public function testGetGit()
    {
        $task = $this->getBaseMock();
        $this->assertInstanceOf('Globalis\Robo\Core\GitCommand', $cmd = $task->getGit());
        $this->assertSame($cmd, $task->getGit());
    }

    public function testCallGitCommand()
    {
        // Create a stub for the SomeClass class.
        $gitCommandStub = $this->getMockBuilder(Globalis\Robo\Core\GitCommand::class)
            ->setMethods(['doSomething'])
            ->getMock();
        // Configure the stub.
        $gitCommandStub->expects($this->once())
            ->method('doSomething')
            ->with($this->equalTo('foo'));

        $task = $this->getBaseMock();
        $reflection = new \ReflectionClass($task);
        $reflection_property = $reflection->getProperty('gitCommand');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($task, $gitCommandStub);
        $task->doSomething('foo');
    }
}

class BaseStub extends \Globalis\Robo\Task\GitFlow\Base
{
    public function run()
    {
        return true;
    }
}
