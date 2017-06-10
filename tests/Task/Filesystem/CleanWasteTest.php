<?php

namespace Globalis\Robo\Tests\Task\Filesystem;

use Globalis\Robo\Tests\Util;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;

class CleanWasteTest extends \PHPUnit_Framework_TestCase
{

    use \Globalis\Robo\Task\Filesystem\loadTasks;
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

    public function testwastePatterns()
    {
        $command = new \Globalis\Robo\Task\Filesystem\CleanWaste(['/tmp']);
        $command->wastePatterns(['test']);
        $this->assertEquals(['test'], $this->getProtectedProperty($command, 'wastePatterns'));
    }

    /**
     * @dataProvider baseWasteFile
     */
    public function testIsWasteFile($filename, $result)
    {
        $command = new \Globalis\Robo\Task\Filesystem\CleanWaste(['/tmp']);
        $this->assertSame($result, $this->invokeMethod($command, 'isWasteFile', [$filename]));
    }

    public function baseWasteFile()
    {
        return [
            ['.DS_Store', true],
            ['Thumbs.db', true],
            ['vim~', true],
            ['._test', true],
            ['test', false],
            ['test.php', false],
        ];
    }

    public function testRun()
    {
        // Create test data
        $dataFolder = sys_get_temp_dir() . "/globalis-robo-tasks-tests-clean-waste" . uniqid();
        mkdir($dataFolder);
        file_put_contents($dataFolder . '/._test', '');
        $command = $this->taskCleanWaste([$dataFolder])
            ->run();
        $this->assertNotContains('._test', scandir($dataFolder));
        Util::rmDir($dataFolder);
    }
}
