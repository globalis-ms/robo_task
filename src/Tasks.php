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
    use \Robo\Collection\Tasks;

    // standard tasks
    use \Robo\Task\Base\Tasks;
    use \Robo\Task\Development\Tasks;
    use \Robo\Task\Filesystem\Tasks;
    use \Robo\Task\File\Tasks;
    use \Robo\Task\Archive\Tasks;
    use \Robo\Task\Vcs\Tasks;

    // package managers
    use \Robo\Task\Bower\Tasks;
    use \Robo\Task\Npm\Tasks;

    // assets
    use \Robo\Task\Assets\Tasks;

    // 3rd-party tools
    use \Robo\Task\Remote\Tasks;
    use \Robo\Task\Testing\Tasks;
    use \Robo\Task\ApiGen\Tasks;
    use \Robo\Task\Docker\Tasks;

    // task runners
    use \Robo\Task\Gulp\Tasks;

    // shortcuts
    use \Robo\Task\Base\loadShortcuts;
    use \Robo\Task\Filesystem\loadShortcuts;
    use \Robo\Task\Vcs\loadShortcuts;

    //globalis task
    use \Globalis\Robo\Task\Composer\Tasks;
    use \Globalis\Robo\Task\Configuration\Tasks;
    use \Globalis\Robo\Task\File\Tasks;
    use \Globalis\Robo\Task\Filesystem\Tasks;
    use \Globalis\Robo\Task\GitFlow\Tasks;

    /**
     * @param bool $stopOnFail
     */
    protected function stopOnFail($stopOnFail = true)
    {
        Result::$stopOnFail = $stopOnFail;
    }
}
