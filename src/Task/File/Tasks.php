<?php

namespace Globalis\Robo\Task\File;

trait Tasks
{
    /**
     * @param $file
     * @return ReplacePlaceholders
     */
    protected function taskReplacePlaceholders($file)
    {
        return $this->task(ReplacePlaceholders::class, $file);
    }
}
