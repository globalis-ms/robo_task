<?php

namespace Globalis\Robo\Tests\Task\Configuration;

use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    use \Globalis\Robo\Task\Configuration\loadTasks;
    use TaskAccessor;
    use ContainerAwareTrait;

    // Set up the Robo container so that we can create tasks in our tests.
    public function setup()
    {
        $container = Robo::createDefaultContainer(null, new NullOutput());
        $this->setContainer($container);
    }

    // Scaffold the collection builder
    public function collectionBuilder()
    {
        $emptyRobofile = new \Robo\Tasks;
        return $this->getContainer()->get('collectionBuilder', [$emptyRobofile]);
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

    public function testDefaultTaskValues()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $this->assertEquals([], $this->getProtectedProperty($command, 'settings'));
        $this->assertEquals([], $this->getProtectedProperty($command, 'config'));
        $this->assertEquals([], $this->getProtectedProperty($command, 'localConfig'));
        $this->assertEquals([], $this->getProtectedProperty($command, 'configDefinition'));
        $this->assertEquals([], $this->getProtectedProperty($command, 'localConfigDefinition'));
        $this->assertEquals(false, $this->getProtectedProperty($command, 'force'));
    }

    public function testGetUserHome()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $_SERVER['HOMEDRIVE'] = '/tmp/';
        $_SERVER['HOMEPATH'] = 'test';
        // Env first
        putenv('HOME=test');
        $this->assertSame('test', $this->invokeMethod($command, 'getUserHome'));
        putenv('HOME');
        // Server vars
        $this->assertSame('/tmp/test', $this->invokeMethod($command, 'getUserHome'));
        unset($_SERVER['HOMEDRIVE'], $_SERVER['HOMEPATH']);
        // None
        $this->assertSame(null, $this->invokeMethod($command, 'getUserHome'));
    }
    public function testInitConfig()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $command->initConfig(['test']);
        $this->assertEquals(['test'], $this->getProtectedProperty($command, 'configDefinition'));
    }

    public function testInitSettings()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $command->initSettings(['test']);
        $this->assertEquals(['test'], $this->getProtectedProperty($command, 'settings'));
    }

    public function testInitLocal()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $command->initLocal(['test']);
        $this->assertEquals(['test'], $this->getProtectedProperty($command, 'localConfigDefinition'));
    }

    public function testLocalFilePath()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $command->localFilePath('test');
        $this->assertEquals('test', $this->getProtectedProperty($command, 'localConfigFilePath'));
    }

    public function testConfigFilePath()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $command->configFilePath('test');
        $this->assertEquals('test', $this->getProtectedProperty($command, 'configFilePath'));
    }

    public function testForce()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $command->force();
        $this->assertEquals(true, $this->getProtectedProperty($command, 'force'));
    }

    /**
     * @dataProvider checkConfigProvider
     */
    public function testcheckConfig($definition, $config, $result)
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $this->assertSame($result, $this->invokeMethod($command, 'checkConfig', [$definition, $config]));
    }

    public function checkConfigProvider()
    {
        return [
            [
                [
                    'test' => [
                        'question' => 'Test ?'
                    ]
                ],
                [
                    'test' => 'ok'
                ],
                true
            ],
            [
                [
                    'test' => [
                        'question' => 'Test ?'
                    ]
                ],
                [],
                false
            ],
            [
                [
                    'test' => [
                        'question' => 'Test ?'
                    ],
                    'test2' => [
                        'question' => 'Test ?'
                    ]
                ],
                [
                    'test' => 1
                ],
                false
            ],
            [
                [
                    'test' => [
                        'question' => 'Test ?'
                    ],
                    'test2' => [
                        'question' => 'Test ?'
                    ]
                ],
                [
                    'test' => 1,
                    'test2' => 2
                ],
                true
            ],
        ];
    }

    public function testSaveConfig()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $data = [
            'test' => 'test',
            'foo' => 1,
            'bar' => 'foo',
        ];
        $tmpFile = tempnam(sys_get_temp_dir(), 'TestReplacePlaceholers');

        $this->invokeMethod($command, 'saveConfig', [$data, $tmpFile]);
        $test = include $tmpFile;
        $this->assertSame($data, $test);
    }

    /**
     * @expectedException Robo\Exception\TaskException
     */
    public function testSaveConfigThrowTaskException()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $data = [
            'test' => 'test',
            'foo' => 1,
            'bar' => 'foo',
        ];
        $this->invokeMethod($command, 'saveConfig', [$data, '/notadir/notfoundfile']);
    }
}
