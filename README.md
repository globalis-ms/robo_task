# Globalis Robo Tasks

[![Build Status](https://travis-ci.org/globalis-ms/robo_task.svg?branch=master)](https://travis-ci.org/globalis-ms/robo_task)
[![Latest Stable Version](https://poser.pugx.org/globalis/robo-task/v/stable)](https://packagist.org/packages/globalis/robo-task)
[![Latest Unstable Version](https://poser.pugx.org/consolidation/robo/v/unstable.png)](https://packagist.org/packages/globalis/robo-task)
[![PHP 7 ready](https://php7ready.timesplinter.ch/globalis-ms/robo_task/master/badge.svg)](https://travis-ci.org/globalis-ms/robo_task)
[![License](https://poser.pugx.org/globalis/robo-task/license)](https://packagist.org/packages/globalis/robo-task)

**[Robo](http://robo.li) tasks collection:**

* [Composer tasks](docs/Composer.md)
* [Configuration tasks](docs/Configuration.md)
* [File tasks](docs/File.md)
* [Filesystem tasks](docs/Filesystem.md)
* [GitFlow tasks](docs/GitFlow.md)

## Installing

### Phar

[Download robo.phar >](https://github.com/globalis-ms/robo_task/releases/download/1.0.4/robo.phar)

```
wget https://github.com/globalis-ms/robo_task/releases/download/1.0.4/robo.phar
```

To install globally put `robo.phar` in `/usr/bin`.

```
chmod +x robo.phar && sudo mv robo.phar /usr/bin/robo
```

Now you can use it just like `robo`.

### Composer

* Run `composer require globalis/robo-task`
* Use `vendor/bin/robo` to execute Robo tasks.
