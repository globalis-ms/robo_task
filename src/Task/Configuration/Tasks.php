<?php

namespace Globalis\Robo\Task\Configuration;

trait Tasks
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
