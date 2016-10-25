# File Tasks
## ReplacePlaceholders


Performs search and replace inside a files.

``` php
<?php
$this->taskReplacePlacehoders('VERSION')
 ->from('0.2.0')
 ->to('0.3.0')
 ->startDelimiter('##')
 ->endDelimiter('##')
 ->run();
?>
```

* `from($from)`  Set string(s) to be replaced
* `to($to)`  Set value(s) to be set as a replacement
* `endDelimiter($delimiter)`  Set end delimiter
* `startDelimiter($delimiter)`  Set start delimiter

