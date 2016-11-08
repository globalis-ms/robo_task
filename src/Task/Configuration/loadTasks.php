<?php
namespace Globalis\Robo\Task\Configuration;

trait loadTasks
{
    /**
     * @param $dirs
     * @return CopyReplaceDir
     */
    protected function taskConfiguration()
    {
        return $this->task(Configuration::class, []);
    }
}
