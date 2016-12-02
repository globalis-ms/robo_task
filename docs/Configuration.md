# Configuration Tasks
## Configuration


Configuration variables

``` php
<?php
$this->taskConfiguration()
 ->initConfig(
    'config_key' => [
       'question' => 'question ?',
       'default' => 'ddd',
       'choices' => ['choice'],
    ]
 ])
 ->initSettings([
      'config_key' => 'value'
  ]),
 ->initLocal([
     'config_key' => [
         'question' => 'question ?',
         'empty' => false,
     ]
 ]),
 ->localFilePath($localFilePath)
 ->configFilePath($configFilePath)
 ->force()
 ->emptyPattern('empty')
 ->run();
?>
```

* `initConfig(array $config)`  Init config variables
* `initSettings(array $config)`  Init settings variables
* `initLocal(array $config)`  Init settings variables
* `localFilePath($filePath)`  Set local file path, Default User Home
* `configFilePath($filePath)`  Set config file path, default Project Dir / .my_config
* `force()`  Force question
* `emptyPattern($emptyPattern)`  Empty pattern
* `setInput($input)`  @inheritdoc
* `setOutput($output)`  @inheritdoc

