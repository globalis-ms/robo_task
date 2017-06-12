# Composer Tasks

## Install


Composer Install

``` php
<?php
$this->taskComposerInstall()
     ->optimizeAutoloader()
     ->preferSource()
     ->preferDist()
     ->dryRun()
     ->dev()
     ->noDev()
     ->optimizeAutoloader()
     ->noAutoloader()
     ->noScript()
     ->noSuggest()
     ->noProgress()
     ->run();
?>
```

* `preferSource()`  Forces installation from package sources when possible, including VCS information.
* `preferDist()`  Forces installation from package dist even for dev versions.
* `dryRun()`  Outputs the operations but will not execute anything (implicitly enables --verbose).
* `dev()`  Enables installation of require-dev packages (enabled by default, only present for BC).
* `noDev()`  Disables installation of require-dev packages.
* `optimizeAutoloader()`  Skips autoloader generation
* `noAutoloader()`  Skips autoloader generation.
* `noScript()`  Skips the execution of all scripts defined in composer.json file.
* `noSuggest()`  Do not show package suggestions.
* `noProgress()`  Do not output download progress.
* `quiet()`  Do not output any message
* `noInteraction()`  Do not ask any interactive question
* `profile()`  Display timing and memory usage information
* `workingDir($workingDir)`  Use the given directory as working directory.
* `ainsi()`  Force ANSI output
* `noAinsi()`  Disable ANSI output
* `option($option, $value = null)`  Pass custom option.

## Update


Composer Update

``` php
<?php
$this->taskComposerUpdate()
     ->optimizeAutoloader()
     ->preferSource()
     ->preferDist()
     ->dryRun()
     ->dev()
     ->noDev()
     ->optimizeAutoloader()
     ->noAutoloader()
     ->noScript()
     ->noSuggest()
     ->noProgress()
     ->lock()
     ->withDependencies()
     ->preferStable()
     ->preferLowest()
     ->interactive()
     ->run();
?>
```

* `lock()`  Only updates the lock file hash to suppress warning about the lock file being out of date.
* `withDependencies()`  Add also all dependencies of whitelisted packages to the whitelist.
* `preferStable()`  Prefer stable versions of dependencies.
* `preferLowest()`  Prefer lowest versions of dependencies.
* `interactive()`  Interactive interface with autocompletion to select the packages to update.
* `preferSource()`  Forces installation from package sources when possible, including VCS information.
* `preferDist()`  Forces installation from package dist even for dev versions.
* `dryRun()`  Outputs the operations but will not execute anything (implicitly enables --verbose).
* `dev()`  Enables installation of require-dev packages (enabled by default, only present for BC).
* `noDev()`  Disables installation of require-dev packages.
* `optimizeAutoloader()`  Skips autoloader generation
* `noAutoloader()`  Skips autoloader generation.
* `noScript()`  Skips the execution of all scripts defined in composer.json file.
* `noSuggest()`  Do not show package suggestions.
* `noProgress()`  Do not output download progress.
* `quiet()`  Do not output any message
* `noInteraction()`  Do not ask any interactive question
* `profile()`  Display timing and memory usage information
* `workingDir($workingDir)`  Use the given directory as working directory.
* `ainsi()`  Force ANSI output
* `noAinsi()`  Disable ANSI output
* `option($option, $value = null)`  Pass custom option.
