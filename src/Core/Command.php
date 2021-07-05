<?php

namespace Globalis\Robo\Core;

use Symfony\Component\Process\Process;

class Command
{
    protected $command;

    public function __construct($command)
    {
        $this->command = $command;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getProcess()
    {
        $process = Process::fromShellCommandline($this->getCommand());
        $process->setTimeout(null);
        return $process;
    }

    public function pipe($command)
    {
        return new self($this->getCommand() . ' | ' . $command);
    }

    public function executeWithoutException()
    {
        $process = $this->getProcess();
        $process->run();
        return $process;
    }

    public function execute()
    {
        $process = $this->getProcess();
        $process->run();
        if (!$process->isSuccessful()) {
            throw new \Exception($this->getErrorProcessMessage($process));
        }
        return $process;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getErrorProcessMessage(Process $process)
    {
        $error = sprintf(
            'The command "%s" failed.' . "\n\nExit Code: %s(%s)\n\nWorking directory: %s",
            $process->getCommandLine(),
            $process->getExitCode(),
            $process->getExitCodeText(),
            $process->getWorkingDirectory()
        );
        if (!$process->isOutputDisabled()) {
            if ($process->getOutput()) {
                $error .= sprintf("\n\nOutput:\n================\n%s", $process->getOutput());
            }

            if ($process->getErrorOutput()) {
                $error .= sprintf("\n\nError Output:\n================\n%s", $process->getErrorOutput());
            }
        }
        return $error;
    }
}
