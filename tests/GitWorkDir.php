<?php

namespace Globalis\Robo\Tests;

use Symfony\Component\Process\Process;

class GitWorkDir
{
    public static $instance;

    protected $baseCwd;

    protected $name;

    protected $workDir;

    protected $gitEmail;

    protected $gitName;

    protected $localWorkDir;

    protected $remoteWorkDir;

    public function __construct($workDir, $name = null)
    {
        $this->name = $name;
        $this->workDir = $workDir;
        $this->baseCwd = getcwd();

        // Build workDir
        mkdir($this->workDir);

        // Initialise remote
        $this->remoteWorkDir = $this->workDir . '/remote';
        mkdir($this->remoteWorkDir);
        Util::runProcess('git init --bare', $this->remoteWorkDir);

        // Initialise local
        $this->localWorkDir = $this->workDir . '/local';
        Util::runProcess('git clone ' . $this->remoteWorkDir . ' local', $this->workDir);

        // Prepare git local config
        Util::runProcess('git config user.email "you@example.com"', $this->localWorkDir);
        Util::runProcess('git config user.name "Your Name"', $this->localWorkDir);

        file_put_contents($this->localWorkDir . '/test', 'Test');
        Util::runProcess('git add .', $this->localWorkDir);
        Util::runProcess('git commit -m "test"', $this->localWorkDir);
        Util::runProcess('git push origin master', $this->localWorkDir);
        Util::runProcess('git checkout -b develop master', $this->localWorkDir);
        Util::runProcess('git push origin develop', $this->localWorkDir);
    }

    public static function getOrNew($name)
    {
        if (static::$instance) {
            if (static::$instance->name() === $name) {
                return static::$instance;
            }
        }
        return static::$instance = new static(sys_get_temp_dir() . "/globalis-robo-tasks-tests-git" . uniqid(), $name);
    }

    public function name()
    {
        return $this->name;
    }

    public function localWorkDir()
    {
        return $this->localWorkDir;
    }

    public function toLocalDir()
    {
        chdir($this->localWorkDir . '/');
    }

    public function remoteWorkDir()
    {
        return $this->remoteWorkDir;
    }

    public function toRemoteDir()
    {
        chdir($this->remoteWorkDir . '/');
    }

    public function restoreCwd()
    {
        chdir($this->baseCwd);
    }

    public function __destroy()
    {
        $this->restoreCwd();
        Util::rmDir($this->workDir);
    }
}
