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
         'empty' => true,
     ],
     'config_key_2' => [
         'question' => 'question ?',
         'formatter' => function ($value) {
             $formatValue = trim($value);
             return $formatValue;
         },
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
* `force($bool = true)`  Force question
* `emptyPattern($emptyPattern)`  Empty pattern
