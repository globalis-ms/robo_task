<?php

namespace Globalis\Robo\Task\Filesystem;

trait loadTasks
{
    /**
     * @param $dirs
     * @return CopyReplaceDir
     */
    protected function taskCopyReplaceDir($dirs)
    {
        return $this->task(CopyReplaceDir::class, $dirs);
    }

    /**
     * @param $dirs
     * @return CleanWaste
     */
    protected function taskCleanWaste($dirs)
    {
        return $this->task(CleanWaste::class, $dirs);
    }
}
