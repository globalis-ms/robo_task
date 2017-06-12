# GitFlow Tasks


## FeatureFinish


Finish a Feature

``` php
<?php
$this->taskFeatureFinish('BranchName', 'GitPath')
 ->developBranch('develop')
 ->repository('origin')
 ->fetchFlag(true)
 ->rebaseFlag(true)
 ->deleteBranchAfter(true)
 ->prefixBranch('feature_')
 ->pushFlag(true)
 ->run();
?>
```

* `rebaseFlag($rebaseFlag)`  Set rebase flag, do a rebase if is true
* `deleteBranchAfter($deleteBranchAfter)`  Set delete branch flag, delete branch if is true
* `pushFlag($pushFlag)`  Set push flag, push if is true
* `repository($repository)`  Set main repository
* `developBranch($developBranch)`  Set develop branch name
* `masterBranch($masterBranch)`  Set master branch name
* `prefixBranch($prefixBranch)`  Set prefix branch
* `fetchFlag($fetchFlag)`  Set fetch flag, fetch all if is true

## FeatureStart


Start a new Feature

``` php
<?php
$this->taskFeatureStart('BranchName', 'GitPath')
 ->developBranch('develop')
 ->repository('origin')
 ->prefixBranch('feature_')
 ->fetchFlag(true)
 ->run();
?>
```

* `repository($repository)`  Set main repository
* `developBranch($developBranch)`  Set develop branch name
* `masterBranch($masterBranch)`  Set master branch name
* `prefixBranch($prefixBranch)`  Set prefix branch
* `fetchFlag($fetchFlag)`  Set fetch flag, fetch all if is true

## HotfixFinish


Finish a Hotfix

``` php
<?php
$this->taskHotfixFinish('BranchName', 'GitPath')
 ->developBranch('develop')
 ->masterBranch('master')
 ->repository('origin')
 ->fetchFlag(true)
 ->rebaseFlag(true)
 ->deleteBranchAfter(true)
 ->prefixBranch('hotfix_')
 ->noTag(false)
 ->tagMessage(null)
 ->pushFlag(true)
 ->run();
?>
```

* `noTag($noTag)`  Set noTag flag, tag master branch if is false
* `tagMessage($tagMessage)`   Set tag message
* `rebaseFlag($rebaseFlag)`  Set rebase flag, do a rebase if is true
* `deleteBranchAfter($deleteBranchAfter)`  Set delete branch flag, delete branch if is true
* `pushFlag($pushFlag)`  Set push flag, push if is true
* `repository($repository)`  Set main repository
* `developBranch($developBranch)`  Set develop branch name
* `masterBranch($masterBranch)`  Set master branch name
* `prefixBranch($prefixBranch)`  Set prefix branch
* `fetchFlag($fetchFlag)`  Set fetch flag, fetch all if is true

## HotfixStart


Start a new Hotfix

``` php
<?php
$this->taskHotfixStart('Version', 'GitPath')
 ->masterBranch('master')
 ->repository('origin')
 ->fetchFlag(true)
 ->prefixBranch('hotfix_')
 ->run();
?>
```

* `repository($repository)`  Set main repository
* `developBranch($developBranch)`  Set develop branch name
* `masterBranch($masterBranch)`  Set master branch name
* `prefixBranch($prefixBranch)`  Set prefix branch
* `fetchFlag($fetchFlag)`  Set fetch flag, fetch all if is true

## ReleaseFinish


Finish a release

``` php
<?php
$this->taskReleaseFinish('version', 'GitPath')
 ->developBranch('develop')
 ->masterBranch('master')
 ->repository('origin')
 ->fetchFlag(true)
 ->rebaseFlag(true)
 ->deleteBranchAfter(true)
 ->prefixBranch('release_')
 ->noTag(false)
 ->tagMessage(null)
 ->pushFlag(true)
 ->run();
?>
```

* `noTag($noTag)`  Set noTag flag, tag master branch if is false
* `tagMessage($tagMessage)`   Set tag message
* `rebaseFlag($rebaseFlag)`  Set rebase flag, do a rebase if is true
* `deleteBranchAfter($deleteBranchAfter)`  Set delete branch flag, delete branch if is true
* `pushFlag($pushFlag)`  Set push flag, push if is true
* `repository($repository)`  Set main repository
* `developBranch($developBranch)`  Set develop branch name
* `masterBranch($masterBranch)`  Set master branch name
* `prefixBranch($prefixBranch)`  Set prefix branch
* `fetchFlag($fetchFlag)`  Set fetch flag, fetch all if is true

## ReleaseStart


Start a new Release

``` php
<?php
$this->taskReleaseStart('Version', 'GitPath')
 ->developBranch('develop')
 ->repository('origin')
 ->fetchFlag(true)
 ->prefixBranch('release_')
 ->run();
?>
```

* `repository($repository)`  Set main repository
* `developBranch($developBranch)`  Set develop branch name
* `masterBranch($masterBranch)`  Set master branch name
* `prefixBranch($prefixBranch)`  Set prefix branch
* `fetchFlag($fetchFlag)`  Set fetch flag, fetch all if is true
