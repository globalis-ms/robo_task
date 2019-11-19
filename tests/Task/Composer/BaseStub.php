<?php

namespace Globalis\Robo\Tests\Task\Composer;

class BaseStub extends \Globalis\Robo\Task\Composer\Base
{
    public function __construct()
    {
        $this->command = 'test';
    }
}
