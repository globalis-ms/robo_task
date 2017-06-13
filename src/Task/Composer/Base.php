<?php
namespace Globalis\Robo\Task\Composer;

use Composer\Console\Application;
use Composer\Factory;
use Robo\Result;
use Robo\Task\BaseTask;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Process\ProcessUtils;

abstract class Base extends BaseTask
{
    protected $command;

    protected $options = [];

    protected $arguments = [];

    /**
     * Do not output any message
     *
     * @return $this
     */
    public function quiet()
    {
        $this->option('-q');
        return $this;
    }

    /**
     * Do not ask any interactive question
     *
     * @return $this
     */
    public function noInteraction()
    {
        $this->option('--no-interaction');
        return $this;
    }

    /**
     * Display timing and memory usage information
     *
     * @return $this
     */
    public function profile()
    {
        $this->option('--profile');
        return $this;
    }

    /**
     * Use the given directory as working directory.
     *
     * @param  string $workingDir
     * @return $this
     */
    public function workingDir($workingDir)
    {
        $this->option('--working-dir', $workingDir);
        return $this;
    }

    /**
     * Force ANSI output
     *
     * @return $this
     */
    public function ainsi()
    {
        $this->option('--ansi');
        return $this;
    }

    /**
     * Disable ANSI output
     *
     * @return $this
     */
    public function noAinsi()
    {
        $this->option('--no-ansi');
        return $this;
    }

    /**
     * Pass custom option.
     *
     * @param  string $option
     * @param  string $value
     * @return $this
     */
    public function option($option, $value = null)
    {
        $this->options[$option] = $value;
        return $this;
    }

    /**
     * Pass argument to executable. Its value will be automatically escaped.
     *
     * @param string $arg
     * @return $this
     */
    public function arg($arg)
    {
        $this->arguments[] = ProcessUtils::escapeArgument($arg);
        return  $this;
    }

    protected function getInput()
    {
        return array_merge(['command' => $this->command], $this->options, $this->arguments);
    }

    protected function getCommand()
    {
        $cmd = '';
        foreach ($this->getInput() as $key => $value) {
            if ($key === 'command') {
                $cmd .= ' ' . $value;
            } elseif (!empty($value)) {
                if (is_int($key)) {
                    $cmd .= ' ' . $value;
                } else {
                    $cmd .= ' ' . $key . '=' . $value;
                }
            } else {
                $cmd .= ' ' . $key;
            }
        }
        return 'composer' . $cmd;
    }

    public function run()
    {
        $this->printTaskInfo('Composer Packages: {command}', ['command' => $this->getCommand()]);

        if (function_exists('ini_set')) {
            $memoryInBytes = function ($value) {
                $unit = strtolower(substr($value, -1, 1));
                $value = (int) $value;
                switch ($unit) {
                    case 'g':
                        $value *= 1024;
                        // no break (cumulative multiplier)
                    case 'm':
                        $value *= 1024;
                        // no break (cumulative multiplier)
                    case 'k':
                        $value *= 1024;
                }
                return $value;
            };
            $memoryLimit = trim(ini_get('memory_limit'));
            // Increase memory_limit if it is lower than 1GB
            if ($memoryLimit != -1 && $memoryInBytes($memoryLimit) < 1024 * 1024 * 1024) {
                @ini_set('memory_limit', '1G');
            }
            unset($memoryInBytes, $memoryLimit);
        }

        $input = new ArgvInput(explode(' ', $this->getCommand()));
        try {
            $application = new Application();
            $application->setAutoExit(false);
            $application->run($input);
        } catch (Exception $e) {
            return new Result($this, $e->getCode(), $e->getMessage());
        }

        return Result::success($this, 'Composer ' . $this->command);
    }
}
