<?php
namespace Globalis\Robo\Tests\Task\Configuration;

use Globalis\Robo\Tests\Util;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{

    use \Globalis\Robo\Task\Configuration\loadTasks;
    use TaskAccessor;
    use ContainerAwareTrait;

    // Set up the Robo container so that we can create tasks in our tests.
    public function setUp(): void
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

    public function testDefaultTaskValues()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $this->assertEquals([], Util::getProtectedProperty($command, 'settings'));
        $this->assertEquals([], Util::getProtectedProperty($command, 'configData'));
        $this->assertEquals([], Util::getProtectedProperty($command, 'localConfig'));
        $this->assertEquals([], Util::getProtectedProperty($command, 'configDefinition'));
        $this->assertEquals([], Util::getProtectedProperty($command, 'localConfigDefinition'));
        $this->assertEquals(false, Util::getProtectedProperty($command, 'force'));
    }

    public function testGetUserHome()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $_SERVER['HOMEDRIVE'] = '/tmp/';
        $_SERVER['HOMEPATH'] = 'test';
        // Env first
        putenv('HOME=test');
        $this->assertSame('test', Util::invokeMethod($command, 'getUserHome'));
        putenv('HOME');
        // Server vars
        $this->assertSame('/tmp/test', Util::invokeMethod($command, 'getUserHome'));
        unset($_SERVER['HOMEDRIVE'], $_SERVER['HOMEPATH']);
        // None
        $this->assertSame(null, Util::invokeMethod($command, 'getUserHome'));
    }

    public function testInitConfig()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $command->initConfig(['test']);
        $this->assertEquals(['test'], Util::getProtectedProperty($command, 'configDefinition'));
    }

    public function testInitSettings()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $command->initSettings(['test']);
        $this->assertEquals(['test'], Util::getProtectedProperty($command, 'settings'));
    }

    public function testInitLocal()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $command->initLocal(['test']);
        $this->assertEquals(['test'], Util::getProtectedProperty($command, 'localConfigDefinition'));
    }

    public function testLocalFilePath()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $command->localFilePath('test');
        $this->assertEquals('test', Util::getProtectedProperty($command, 'localConfigFilePath'));
    }

    public function testConfigFilePath()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $command->configFilePath('test');
        $this->assertEquals('test', Util::getProtectedProperty($command, 'configFilePath'));
    }

    public function testForce()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $command->force();
        $this->assertEquals(true, Util::getProtectedProperty($command, 'force'));
        $command->force(false);
        $this->assertEquals(false, Util::getProtectedProperty($command, 'force'));
        $command->force(true);
        $this->assertEquals(true, Util::getProtectedProperty($command, 'force'));
    }

    /**
     * @dataProvider checkConfigProvider
     */
    public function testcheckConfig($definition, $config, $result)
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $this->assertSame($result, Util::invokeMethod($command, 'checkConfig', [$definition, $config]));
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
        $tmpFile = tempnam(sys_get_temp_dir(), 'globalis-robo-tasks-tests-configuration');
        Util::invokeMethod($command, 'saveConfig', [$data, $tmpFile]);
        $test = include $tmpFile;
        $this->assertSame($data, $test);
        Util::rmDir($tmpFile);
    }

    public function testSaveConfigCreateFileIFNotExist()
    {
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $data = [
            'test' => 'test',
            'foo' => 1,
            'bar' => 'foo',
        ];
        $tmpFolder = sys_get_temp_dir() . '/globalis-robo-tasks-tests-configuration' . uniqid();
        mkdir($tmpFolder);
        Util::invokeMethod($command, 'saveConfig', [$data, $tmpFolder . '/test']);
        $test = include $tmpFolder . '/test';
        $this->assertSame($data, $test);
        Util::rmDir($tmpFolder);
    }

    public function testSaveConfigThrowTaskException()
    {
        $this->expectException(\Robo\Exception\TaskException::class);
        $command = new \Globalis\Robo\Task\Configuration\Configuration();
        $data = [
            'test' => 'test',
            'foo' => 1,
            'bar' => 'foo',
        ];
        Util::invokeMethod($command, 'saveConfig', [$data, '/notadir/notfoundfile']);
    }
}
