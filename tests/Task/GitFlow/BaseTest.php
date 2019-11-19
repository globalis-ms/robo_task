<?php
namespace Globalis\Robo\Tests\Task\GitFlow;

use Globalis\Robo\Tests\Util;

class BaseTest extends \PHPUnit\Framework\TestCase
{
    protected function getBaseMock()
    {
        return new BaseStub('test');
    }

    public function testConstructor()
    {
        $task = new BaseStub('test');
        $this->assertSame('test', Util::getProtectedProperty($task, 'name'));
        $this->assertSame('git', Util::getProtectedProperty($task, 'pathToGit'));
        $this->assertSame('origin', Util::getProtectedProperty($task, 'repository'));
        $this->assertSame('develop', Util::getProtectedProperty($task, 'developBranch'));
        $this->assertSame('master', Util::getProtectedProperty($task, 'masterBranch'));
        $this->assertSame('', Util::getProtectedProperty($task, 'prefixBranch'));
        $this->assertSame(true, Util::getProtectedProperty($task, 'fetchFlag'));

        $task = new BaseStub('test', 'git_cmd');
        $this->assertSame('git_cmd', Util::getProtectedProperty($task, 'pathToGit'));
    }

    public function testRepository()
    {
        $task = $this->getBaseMock();
        $this->assertSame($task, $task->repository('foo'));
        $this->assertSame('foo', Util::getProtectedProperty($task, 'repository'));
    }

    public function testDevelopBranch()
    {
        $task = $this->getBaseMock();
        $this->assertSame($task, $task->developBranch('foo'));
        $this->assertSame('foo', Util::getProtectedProperty($task, 'developBranch'));
    }

    public function testMasterBranch()
    {
        $task = $this->getBaseMock();
        $this->assertSame($task, $task->masterBranch('foo'));
        $this->assertSame('foo', Util::getProtectedProperty($task, 'masterBranch'));
    }

    public function testPrefixBranch()
    {
        $task = $this->getBaseMock();
        $this->assertSame($task, $task->prefixBranch('foo'));
        $this->assertSame('foo', Util::getProtectedProperty($task, 'prefixBranch'));
    }

    public function testFetchFlag()
    {
        $task = $this->getBaseMock();
        $this->assertSame($task, $task->fetchFlag(false));
        $this->assertSame(false, Util::getProtectedProperty($task, 'fetchFlag'));
    }

    public function testGetGit()
    {
        $task = $this->getBaseMock();
        $this->assertInstanceOf('Globalis\Robo\Core\GitCommand', $cmd = Util::invokeMethod($task, 'getGit'));
        $this->assertSame($cmd, Util::invokeMethod($task, 'getGit'));
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
