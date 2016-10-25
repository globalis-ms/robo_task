# Filesystem Tasks
## CleanWaste


Deletes all files from specified dir, ignoring git files.

``` php
<?php
$this->taskCleanDir(['tmp','logs'])
->wastePatterns([
    "/\.DS_Store/",
    "/Thumbs\.db/",
    "/.*~/",
    "/\._.*\/",
 ])->run();
?>
```

* `wastePatterns(array $wastePatterns)`  Set waste patterns to delete

## CopyReplaceDir


Copies one dir into another and replace variables

``` php
<?php
$this->taskCopyReplaceDir(['dist/config' => 'config']
 ->from(array('##dbname##', '##dbhost##'))
 ->to(array('robo', 'localhost'))
 ->startDelimiter('##')
 ->endDelimiter('##')
 ->dirPermissions(0755)
 ->filePermissions(0644)
 ->exclude([file, file])
 ->run();
?>
```

* `from($from)`   Set string(s) to be replaced
* `to($to)`  Set value(s) to be set as a replacement
* `endDelimiter($delimiter)`  Set end delimiter
* `startDelimiter($delimiter)`  Set start delimiter
* `dirPermissions($value)`  Sets the default folder permissions for the destination if it doesn't exist
* `filePermissions($value)`  Sets the default file permissions for the destination if it doesn't exist
* `exclude($exclude = null)`  List files to exclude.

