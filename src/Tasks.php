<?php

namespace Globalis\Robo;

use Robo\Contract\IOAwareInterface;
use Robo\Contract\BuilderAwareInterface;
use League\Container\ContainerAwareInterface;

class Tasks implements BuilderAwareInterface, IOAwareInterface, ContainerAwareInterface
{
    use \League\Container\ContainerAwareTrait;
    use \Robo\Common\IO;

    // Tasks
    use \Robo\TaskAccessor;
    use \Robo\Collection\loadTasks;

    // standard tasks
    use \Robo\Task\Base\loadTasks;
    use \Robo\Task\Development\loadTasks;
    use \Robo\Task\Filesystem\loadTasks;
    use \Robo\Task\File\loadTasks;
    use \Robo\Task\Archive\loadTasks;
    use \Robo\Task\Vcs\loadTasks;

    // package managers
    use \Robo\Task\Bower\loadTasks;
    use \Robo\Task\Npm\loadTasks;

    // assets
    use \Robo\Task\Assets\loadTasks;

    // 3rd-party tools
    use \Robo\Task\Remote\loadTasks;
    use \Robo\Task\Testing\loadTasks;
    use \Robo\Task\ApiGen\loadTasks;
    use \Robo\Task\Docker\loadTasks;

    // task runners
    use \Robo\Task\Gulp\loadTasks;

    // shortcuts
    use \Robo\Task\Base\loadShortcuts;
    use \Robo\Task\Filesystem\loadShortcuts;
    use \Robo\Task\Vcs\loadShortcuts;

    //globalis task
    use \Globalis\Robo\Task\Composer\loadTasks;
    use \Globalis\Robo\Task\Configuration\loadTasks;
    use \Globalis\Robo\Task\File\loadTasks;
    use \Globalis\Robo\Task\Filesystem\loadTasks;
    use \Globalis\Robo\Task\GitFlow\loadTasks;

    /**
     * @param bool $stopOnFail
     */
    protected function stopOnFail($stopOnFail = true)
    {
        Result::$stopOnFail = $stopOnFail;
    }
}
