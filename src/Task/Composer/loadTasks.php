<?php
namespace Globalis\Robo\Task\Composer;

trait loadTasks
{
    /**
     * @param $dirs
     * @return CopyReplaceDir
     */
    protected function taskComposerInstall()
    {
        return $this->task(Install::class);
    }
}
