<?php

namespace Globalis\Robo\Task\Composer;

/**
 * Composer Install
 *
 * ``` php
 * <?php
 * $this->taskComposerInstall()
 *      ->optimizeAutoloader()
 *      ->preferSource()
 *      ->preferDist()
 *      ->dryRun()
 *      ->dev()
 *      ->noDev()
 *      ->optimizeAutoloader()
 *      ->run();
 * ?>
 * ```
 */
class Install extends Base
{
    public function __construct()
    {
        $this->command = 'install';
    }

    /**
     * Forces installation from package sources when possible, including VCS information.
     *
     * @return $this
     */
    public function preferSource()
    {
        $this->option('--prefer-source');
        return $this;
    }

    /**
     * Forces installation from package dist even for dev versions.
     *
     * @return $this
     */
    public function preferDist()
    {
        $this->option('--prefer-dist');
        return $this;
    }

    /**
     * Outputs the operations but will not execute anything (implicitly enables --verbose).
     *
     * @return $this
     */
    public function dryRun()
    {
        $this->option('--dry-run');
        return $this;
    }

    /**
     *  Enables installation of require-dev packages (enabled by default, only present for BC).
     *
     * @return $this
     */
    public function dev()
    {
        $this->option('--dev');
        return $this;
    }

    /**
     *  Disables installation of require-dev packages.
     *
     * @return $this
     */
    public function noDev()
    {
        $this->option('--no-dev');
        return $this;
    }

    /**
     *  Optimize autoloader during autoloader dump.
     *
     * @return $this
     */
    public function optimizeAutoloader()
    {
        $this->option('--optimize-autoloader');
        return $this;
    }
}
