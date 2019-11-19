<?php

namespace Globalis\Robo\Task\GitFlow;

use Globalis\Robo\Task\GitFlow\Feature\Start as FeatureStart;
use Globalis\Robo\Task\GitFlow\Feature\Finish as FeatureFinish;
use Globalis\Robo\Task\GitFlow\Hotfix\Start as HotfixStart;
use Globalis\Robo\Task\GitFlow\Hotfix\Finish as HotfixFinish;
use Globalis\Robo\Task\GitFlow\Release\Start as ReleaseStart;
use Globalis\Robo\Task\GitFlow\Release\Finish as ReleaseFinish;

trait loadTasks
{
    /**
     * @param $version
     * @param $gitPath
     * @return \Globalis\Robo\Task\GitFlow\Release\Start
     */
    protected function taskReleaseStart($version, $gitPath = 'git')
    {
        return $this->task(ReleaseStart::class, $version, $gitPath);
    }

    /**
     * @param $version
     * @param $gitPath
     * @return \Globalis\Robo\Task\GitFlow\Release\Finish
     */
    protected function taskReleaseFinish($version, $gitPath = 'git')
    {
        return $this->task(ReleaseFinish::class, $version, $gitPath);
    }

    /**
     * @param $version
     * @param $gitPath
     * @return \Globalis\Robo\Task\GitFlow\Hotfix\Start
     */
    protected function taskHotfixStart($version, $gitPath = 'git')
    {
        return $this->task(HotfixStart::class, $version, $gitPath);
    }

    /**
     * @param $version
     * @param $gitPath
     * @return \Globalis\Robo\Task\GitFlow\Hotfix\Finish
     */
    protected function taskHotfixFinish($version, $gitPath = 'git')
    {
        return $this->task(HotfixFinish::class, $version, $gitPath);
    }

    /**
     * @param $name
     * @param $gitPath
     * @return \Globalis\Robo\Task\GitFlow\Feature\Start
     */
    protected function taskFeatureStart($name, $gitPath = 'git')
    {
        return $this->task(FeatureStart::class, $name, $gitPath);
    }

    /**
     * @param $name
     * @param $gitPath
     * @return \Globalis\Robo\Task\GitFlow\Feature\Finish
     */
    protected function taskFeatureFinish($name, $gitPath = 'git')
    {
        return $this->task(FeatureFinish::class, $name, $gitPath);
    }
}
