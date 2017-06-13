<?php
namespace Globalis\Robo\Task\Composer;

/**
 * Composer Custom
 *
 * ``` php
 * <?php
 * $this->taskComposer('required')
 *      ->option('--dev')
 *      ->arg('globalis/robo-task:dev-master')
 *      ->arg('vendor/package')
 *      ->run();
 * ?>
 * ```
 */
class Custom extends Base
{
    protected $command;

    public function __construct($command)
    {
        $this->command = $command;
    }
}
