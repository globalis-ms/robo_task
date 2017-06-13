# Globalis Robo Tasks

**[Robo](http://robo.li) tasks collection:**

* [Composer tasks](docs/Composer.md)
* [Configuration tasks](docs/Configuration.md)
* [File tasks](docs/File.md)
* [Filesystem tasks](docs/Filesystem.md)
* [GitFlow tasks](docs/GitFlow.md)

## Installing

### Phar

[Download robo.phar >](https://github.com/globalis-ms/robo_task/releases/download/1.0.2/robo.phar)

```
wget https://github.com/globalis-ms/robo_task/releases/download/1.0.2/robo.phar
```

To install globally put `robo.phar` in `/usr/bin`.

```
chmod +x robo.phar && sudo mv robo.phar /usr/bin/robo
```

Now you can use it just like `robo`.

### Composer

* Run `composer require globalis/robo-task`
* Use `vendor/bin/robo` to execute Robo tasks.
