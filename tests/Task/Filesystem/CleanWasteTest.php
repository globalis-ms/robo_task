<?php

namespace Globalis\Robo\Tests\Task\Filesystem;

use Globalis\Robo\Tests\Util;
use Globalis\Robo\Task\Filesystem\CleanWaste;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Robo\TaskAccessor;
use Robo\Robo;

class CleanWasteTest extends \PHPUnit\Framework\TestCase implements ContainerAwareInterface
{
    use \Globalis\Robo\Task\Filesystem\Tasks;
    use TaskAccessor;
    use ContainerAwareTrait;

    // Set up the Robo container so that we can create tasks in our tests.
    protected function setUp(): void
    {
        Robo::createContainer();
        $container = Robo::getContainer();
        $this->setContainer($container);
    }

    // Scaffold the collection builder
    public function collectionBuilder()
    {
        $emptyRobofile = new \Robo\Tasks();
        $container = $this->getContainer();
        $collectionBuilderDefinition = $container->extend('collectionBuilder');
        $collectionBuilderDefinition->addArgument($emptyRobofile);
        return $container->get('collectionBuilder', true);
    }


    public function testWastePatterns()
    {
        $command = new CleanWaste(['/tmp']);
        $command->wastePatterns(['test']);
        $this->assertEquals(['test'], Util::getProtectedProperty($command, 'wastePatterns'));
    }

    /**
     * @dataProvider baseWasteFile
     */
    public function testIsWasteFile($filename, $result)
    {
        $command = new CleanWaste(['/tmp']);
        $this->assertSame($result, Util::invokeMethod($command, 'isWasteFile', [$filename]));
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
        $this->taskCleanWaste([$dataFolder])
            ->run();
        $this->assertNotContains('._test', scandir($dataFolder));
        Util::rmDir($dataFolder);
    }
}
