<?php

namespace Globalis\Robo\Tests\Task\Composer;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    protected function getBaseMock()
    {
        return new BaseStub();
    }

    protected function getProtectedProperty($object, $property)
    {
        $reflection = new \ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        return $reflection_property->getValue($object);
    }

    public function invokeMethod($object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testOption()
    {
        $command = $this->getBaseMock();
        $command->option('test');
        $options = ['test' => null];
        $this->assertSame($options, $this->getProtectedProperty($command, 'options'));

        $options['test'] = 'test';
        $command->option('test', 'test');
        $this->assertSame($options, $this->getProtectedProperty($command, 'options'));


        $options['foo'] = 'bar';
        $options['bar'] = null;
        $command->option('foo', 'bar');
        $command->option('bar');
        $this->assertSame($options, $this->getProtectedProperty($command, 'options'));
    }

    public function testGetInput()
    {
        $command = $this->getBaseMock();
        $this->assertSame(['command' => 'test'], $this->invokeMethod($command, 'getInput'));

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
            $this->invokeMethod($command, 'getInput')
        );
    }

    public function getCommand()
    {
        $command = $this->getBaseMock();
        $this->assertSame('composer test', $this->invokeMethod($command, 'getCommand'));

        $command->option('bar');
        $command->option('foo', 'bar');
        $command->quiet();
        $this->assertSame(
            'composer test bar foo=bar -q',
            $this->invokeMethod($command, 'getCommand')
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
            $this->assertSame([$result => null], $this->getProtectedProperty($command, 'options'));
        } else {
            $this->assertSame([$result => join($args)], $this->getProtectedProperty($command, 'options'));
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


class BaseStub extends \Globalis\Robo\Task\Composer\Base
{
    public function __construct()
    {
        $this->command = 'test';
    }
}
