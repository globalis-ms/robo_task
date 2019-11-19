<?php

namespace Globalis\Robo\Tests\Task\Composer;

use Globalis\Robo\Tests\Util;
use Globalis\Robo\Task\Composer\Custom;

class CustomTest extends \PHPUnit\Framework\TestCase
{

    public function testConstructor()
    {
        $command = new Custom('test');
        $this->assertSame('test', Util::getProtectedProperty($command, 'command'));
    }
}
