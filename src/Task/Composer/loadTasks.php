<?php
namespace Globalis\Robo\Task\Composer;

trait loadTasks
{
    /**
     * @return Install
     */
    protected function taskComposerInstall()
    {
        return $this->task(Install::class);
    }

    /**
     * @return Update
     */
    protected function taskComposerUpdate()
    {
        return $this->task(Update::class);
    }
}
