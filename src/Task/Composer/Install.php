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
 *      ->noAutoloader()
 *      ->noScript()
 *      ->noSuggest()
 *      ->noProgress()
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
     * Enables installation of require-dev packages (enabled by default, only present for BC).
     *
     * @return $this
     */
    public function dev()
    {
        $this->option('--dev');
        return $this;
    }

    /**
     * Disables installation of require-dev packages.
     *
     * @return $this
     */
    public function noDev()
    {
        $this->option('--no-dev');
        return $this;
    }

    /**
     * Skips autoloader generation
     *
     * @return $this
     */
    public function optimizeAutoloader()
    {
        $this->option('--optimize-autoloader');
        return $this;
    }

    /**
     * Skips autoloader generation.
     *
     * @return $this
     */
    public function noAutoloader()
    {
        $this->option('--no-autoloader');
        return $this;
    }

    /**
     * Skips the execution of all scripts defined in composer.json file.
     *
     * @return $this
     */
    public function noScript()
    {
        $this->option('--no-script');
        return $this;
    }

    /**
     * Do not show package suggestions.
     *
     * @return $this
     */
    public function noSuggest()
    {
        $this->option('--no-suggest');
        return $this;
    }

    /**
     * Do not output download progress.
     *
     * @return $this
     */
    public function noProgress()
    {
        $this->option('--no-progress');
        return $this;
    }
}
