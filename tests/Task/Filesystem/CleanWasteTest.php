<?php

namespace Globalis\Robo\Tests\Task\Filesystem;

use Globalis\Robo\Tests\Util;
use Globalis\Robo\Task\Filesystem\CleanWaste;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;

class CleanWasteTest extends \PHPUnit\Framework\TestCase
{
    use \Globalis\Robo\Task\Configuration\Tasks;
    use TaskAccessor;
    use ContainerAwareTrait;

    // Set up the Robo container so that we can create tasks in our tests.
    protected function setUp(): void
    {
        $container = Robo::createDefaultContainer(null, new NullOutput());
        $this->setContainer($container);
    }

    // Scaffold the collection builder
    public function collectionBuilder()
    {
        $emptyRobofile = new \Robo\Tasks();
        return $this->getContainer()->get('collectionBuilder', [$emptyRobofile]);
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
