<?php

namespace Globalis\Robo\Tests\Task\Composer;

use Globalis\Robo\Tests\Util;

class UpdateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider options
     */
    public function testComposerOptions($function, $result, $args = [])
    {
        $command = new \Globalis\Robo\Task\Composer\Update();
        $command->{$function}(...$args);
        if (empty($args)) {
            $this->assertSame([$result => null], Util::getProtectedProperty($command, 'options'));
        } else {
            $this->assertSame([$result => join($args)], Util::getProtectedProperty($command, 'options'));
        }
    }

    public function options()
    {
        return [
            ['lock', '--lock'],
            ['withDependencies', '--with-dependencies'],
            ['preferStable', '--prefer-stable'],
            ['preferLowest', '--prefer-lowest'],
            ['interactive', '--interactive'],
        ];
    }
}
