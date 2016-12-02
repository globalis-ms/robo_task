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
     ->run();
?>
```

* `preferSource()`  Forces installation from package sources when possible, including VCS information.
* `preferDist()`  Forces installation from package dist even for dev versions.
* `dryRun()`  Outputs the operations but will not execute anything (implicitly enables --verbose).
* `dev()`   Enables installation of require-dev packages (enabled by default, only present for BC).
* `noDev()`   Disables installation of require-dev packages.
* `optimizeAutoloader()`   Optimize autoloader during autoloader dump.
* `quiet()`  Do not output any message
* `noInteraction()`  Do not ask any interactive question
* `profile()`  Display timing and memory usage information
* `workingDir($workingDir)`  Use the given directory as working directory.
* `ainsi()`  Force ANSI output
* `noAinsi()`  Disable ANSI output
* `option($option, $value = null)`  Pass custom option.

