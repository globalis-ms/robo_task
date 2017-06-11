<?php
namespace Globalis\Robo\Tests\Task\File;

use Globalis\Robo\Tests\Util;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;

class ReplacePlaceholdersTest extends \PHPUnit\Framework\TestCase
{

    use \Globalis\Robo\Task\File\loadTasks;
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

    public function testDefaultTaskValues()
    {
        $command = new \Globalis\Robo\Task\File\ReplacePlaceholders('test.me');
        $this->assertEquals('test.me', Util::getProtectedProperty($command, 'filename'));
        $this->assertEquals('##', Util::getProtectedProperty($command, 'startDelimiter'));
        $this->assertEquals('##', Util::getProtectedProperty($command, 'endDelimiter'));
        $this->assertEquals([], Util::getProtectedProperty($command, 'from'));
        $this->assertEquals([], Util::getProtectedProperty($command, 'to'));
    }

    public function testFromSetter()
    {
        $command = new \Globalis\Robo\Task\File\ReplacePlaceholders('test.me');
        $command->from('test');
        $this->assertEquals('test', Util::getProtectedProperty($command, 'from'));
    }

    public function testToSetter()
    {
        $command = new \Globalis\Robo\Task\File\ReplacePlaceholders('test.me');
        $command->to('test');
        $this->assertEquals('test', Util::getProtectedProperty($command, 'to'));
    }

    public function testEndDelimiterSetter()
    {
        $command = new \Globalis\Robo\Task\File\ReplacePlaceholders('test.me');
        $command->endDelimiter('test');
        $this->assertEquals('test', Util::getProtectedProperty($command, 'endDelimiter'));
    }

    public function testStartDelimiterSetter()
    {
        $command = new \Globalis\Robo\Task\File\ReplacePlaceholders('test.me');
        $command->startDelimiter('test');
        $this->assertEquals('test', Util::getProtectedProperty($command, 'startDelimiter'));
    }

    /**
     * @dataProvider runProvider
     */
    public function testRun($delimiterStart, $delimiterEnd, $from, $to, $contentStart, $contentEnd)
    {
        // Create tmp file
        $tmpFile = tempnam(sys_get_temp_dir(), 'globalis-robo-tasks-tests-replace-placeholers');
        file_put_contents($tmpFile, $contentStart);
        $command = $this->taskReplacePlaceholders($tmpFile);
        $command->startDelimiter($delimiterStart)
            ->endDelimiter($delimiterEnd)
            ->from($from)
            ->to($to)
            ->run();
        $this->assertEquals($contentEnd, file_get_contents($tmpFile));
        // Delete tmpFile
        Util::rmDir($tmpFile);
    }

    public function runProvider()
    {
        return  [
            ['AA', 'BB', '', '', '', ''],
            ['AA', 'BB', 'FROM', 'TO', 'AAFROMBB', 'TO'],
            ['AA', 'BB', 'FROM', 'TO', 'TESTAAFROMBBTEST', 'TESTTOTEST'],
            ['AA', 'AA', 'FROM', 'TO', "AAFROMAA\nLOREM", "TO\nLOREM"],
            ['AA', 'BB', ['FROM', 'LOREM'], 'TO', 'AAFROMBBAALOREMBB', 'TOTO'],
            ['AA', 'AA', ['FROM', 'LOREM'], ['TO', 'IPSUM'], "AAFROMAA\nFOO=AALOREMAA", "TO\nFOO=IPSUM"],
        ];
    }
}
