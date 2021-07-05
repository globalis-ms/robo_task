<?php

namespace Globalis\Robo\Tests\Core;

use Globalis\Robo\Core\Command;

class CommandTest extends \PHPUnit\Framework\TestCase
{

    public function testGetCommand()
    {
        $command = new Command('test');
        $this->assertSame('test', $command->getCommand());

        $command = new Command('test');
        $command->arg('test');
        $this->assertSame('test test', $command->getCommand());

        $command = new Command('test');
        $command->arg('test');
        $command->option('--test');
        $this->assertSame('test test --test', $command->getCommand());
    }

    public function testGetProcess()
    {
        $command = new Command('test');
        $this->assertInstanceOf('Symfony\\Component\\Process\\Process', $command->getProcess());
    }

    public function testPipeNot()
    {
        $command = new Command('test');
        $newCommand = $command->pipe('toto');
        $this->assertSame('test | toto', $newCommand->getCommand());
    }

    public function testPipeNotSameInstance()
    {
        $command = new Command('test');
        $newCommand = $command->pipe('toto');
        $this->assertNotSame($command, $newCommand);
        $this->assertInstanceOf(get_class($command), $newCommand);
    }

    public function testExecute()
    {
        $command = new Command('php');
        $command->option('-r', 'echo \'*\';');
        $result = $command->execute();
        $this->assertInstanceOf('Symfony\\Component\\Process\\Process', $result);
        $this->assertSame('*', $result->getOutput());
    }

    /**
     * @covers \Globalis\Robo\Core\Command::execute
     */
    public function testExecuteThrowTaskException()
    {
        $this->expectException(\Exception::class);
        $command = new Command('commanddoesnotexist');
        $command->execute();
    }

    public function testExecuteWithoutException()
    {
        $command = new Command('php');
        $command->option('-r', 'echo \'*\';');
        $result = $command->executeWithoutException();
        $this->assertInstanceOf('Symfony\\Component\\Process\\Process', $result);
        $this->assertSame('*', $result->getOutput());
    }

    public function testExecuteWithoutExceptionNotThrowTaskException()
    {
        $command = new Command('commanddoesnotexist');
        try {
            $result = $command->executeWithoutException();
            $this->assertSame(false, $result->isSuccessful());
        } catch (\Robo\Exception\TaskException $e) {
            $this->fail('A Robo\Exception\TaskException should have been raised');
        }
    }
}
