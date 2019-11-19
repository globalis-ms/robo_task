<?php

namespace Globalis\Robo\Tests\Task\Composer;

use Globalis\Robo\Tests\Util;

class BaseTest extends \PHPUnit\Framework\TestCase
{
    protected function getBaseMock()
    {
        return new BaseStub();
    }

    public function testOption()
    {
        $command = $this->getBaseMock();
        $command->option('test');
        $options = ['test' => null];
        $this->assertSame($options, Util::getProtectedProperty($command, 'options'));

        $options['test'] = 'test';
        $command->option('test', 'test');
        $this->assertSame($options, Util::getProtectedProperty($command, 'options'));


        $options['foo'] = 'bar';
        $options['bar'] = null;
        $command->option('foo', 'bar');
        $command->option('bar');
        $this->assertSame($options, Util::getProtectedProperty($command, 'options'));
    }

    public function testGetInput()
    {
        $command = $this->getBaseMock();
        $this->assertSame(['command' => 'test'], Util::invokeMethod($command, 'getInput'));

        $command->option('bar');
        $command->option('foo', 'bar');
        $command->quiet();
        $this->assertSame(
            [
                'command' => 'test',
                'bar' => null,
                'foo' => 'bar',
                '-q' => null
            ],
            Util::invokeMethod($command, 'getInput')
        );
    }

    public function testGetCommand()
    {
        $command = $this->getBaseMock();
        $this->assertSame('composer test', Util::invokeMethod($command, 'getCommand'));

        $command->option('bar')
            ->option('foo', 'bar')
            ->arg('foobar:1.2')
            ->arg('barfoo')
            ->quiet();
        $this->assertSame(
            "composer test bar foo=bar -q foobar:1.2 barfoo",
            Util::invokeMethod($command, 'getCommand')
        );
    }

    /**
     * @dataProvider options
     */
    public function testComposerDefaultOptions($function, $result, $args = [])
    {
        $command = $this->getBaseMock();
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
            ['quiet', '-q'],
            ['noInteraction', '--no-interaction'],
            ['profile', '--profile'],
            ['workingDir', '--working-dir', ['test']],
            ['ainsi', '--ansi'],
            ['noAinsi', '--no-ansi'],
        ];
    }
}
