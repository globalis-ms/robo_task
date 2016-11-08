<?php
namespace Globalis\Robo\Task\File;

trait loadTasks
{
    /**
     * @param $file
     * @return ReplacePlacehoders
     */
    protected function taskReplacePlacehoders($file)
    {
        return $this->task(ReplacePlacehoders::class, $file);
    }
}
