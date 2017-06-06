<?php

namespace Globalis\Robo\Tests\Task\Composer;

class InstallTest extends \PHPUnit_Framework_TestCase
{
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

    /**
     * @dataProvider options
     */
    public function testComposerOptions($function, $result, $args = [])
    {
        $command = new \Globalis\Robo\Task\Composer\Install();
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
            ['preferSource', '--prefer-source'],
            ['preferDist', '--prefer-dist'],
            ['dryRun', '--dry-run'],
            ['dev', '--dev'],
            ['noDev', '--no-dev'],
            ['optimizeAutoloader', '--optimize-autoloader'],
            ['noAutoloader', '--no-autoloader'],
            ['noScript', '--no-script'],
            ['noSuggest', '--no-suggest'],
            ['noProgress', '--no-progress'],
        ];
    }
}
