<?php

namespace Globalis\Robo\Task\Composer;

/**
 * Composer Update
 *
 * ``` php
 * <?php
 * $this->taskComposerUpdate()
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
 *      ->lock()
 *      ->withDependencies()
 *      ->preferStable()
 *      ->preferLowest()
 *      ->interactive()
 *      ->run();
 * ?>
 * ```
 */
class Update extends Install
{
    public function __construct()
    {
        $this->command = 'update';
    }

    /**
     * Only updates the lock file hash to suppress warning about the lock file being out of date.
     *
     * @return $this
     */
    public function lock()
    {
        $this->option('--lock');
        return $this;
    }

    /**
     * Add also all dependencies of whitelisted packages to the whitelist.
     *
     * @return $this
     */
    public function withDependencies()
    {
        $this->option('--with-dependencies');
        return $this;
    }

    /**
     * Prefer stable versions of dependencies.
     *
     * @return $this
     */
    public function preferStable()
    {
        $this->option('--prefer-stable');
        return $this;
    }

    /**
     * Prefer lowest versions of dependencies.
     *
     * @return $this
     */
    public function preferLowest()
    {
        $this->option('--prefer-lowest');
        return $this;
    }

    /**
     * Interactive interface with autocompletion to select the packages to update.
     *
     * @return $this
     */
    public function interactive()
    {
        $this->option('--interactive');
        return $this;
    }
}
