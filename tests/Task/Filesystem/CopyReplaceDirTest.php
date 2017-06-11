<?php

namespace Globalis\Robo\Tests\Task\Filesystem;

use Globalis\Robo\Tests\Util;
use Globalis\Robo\Task\Filesystem\CopyReplaceDir;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;

class CopyReplaceDirTest extends \PHPUnit_Framework_TestCase
{

    use \Globalis\Robo\Task\Filesystem\loadTasks;
    use TaskAccessor;
    use ContainerAwareTrait;

    protected $baseTestFolder;

    protected $dataFolder;

    protected $copyFolder;

    // Set up the Robo container so that we can create tasks in our tests.
    public function setup()
    {
        $container = Robo::createDefaultContainer(null, new NullOutput());
        $this->setContainer($container);
    }

    public function tearDown()
    {
        if ($this->baseTestFolder) {
            Util::rmDir($this->baseTestFolder);
        }
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

    protected function invokeMethod($object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testConstructor()
    {
        $command = new CopyReplaceDir('/tmp');
        $this->assertEquals(['/tmp'], $this->getProtectedProperty($command, 'dirs'));
        $command = new CopyReplaceDir(['/tmp', '/test']);
        $this->assertEquals(['/tmp', '/test'], $this->getProtectedProperty($command, 'dirs'));
        $this->assertEquals([], $this->getProtectedProperty($command, 'exclude'));
        $this->assertEquals('#', $this->getProtectedProperty($command, 'startDelimiter'));
        $this->assertEquals('#', $this->getProtectedProperty($command, 'endDelimiter'));
        $this->assertEquals(0755, $this->getProtectedProperty($command, 'dirPermissions'));
        $this->assertEquals(0644, $this->getProtectedProperty($command, 'filePermissions'));
    }

    public function testFrom()
    {
        $command = new CopyReplaceDir('/tmp');
        $command->from(['foo']);
        $this->assertSame(['foo'], $this->getProtectedProperty($command, 'from'));
    }

    public function testTo()
    {
        $command = new CopyReplaceDir('/tmp');
        $command->to(['foo']);
        $this->assertSame(['foo'], $this->getProtectedProperty($command, 'to'));
    }

    public function testEndDelimiter()
    {
        $command = new CopyReplaceDir('/tmp');
        $command->endDelimiter('foo');
        $this->assertSame('foo', $this->getProtectedProperty($command, 'endDelimiter'));
    }

    public function testStartDelimiter()
    {
        $command = new CopyReplaceDir('/tmp');
        $command->startDelimiter('foo');
        $this->assertSame('foo', $this->getProtectedProperty($command, 'startDelimiter'));
    }

    public function testDirPermissions()
    {
        $command = new CopyReplaceDir('/tmp');
        $command->dirPermissions(0555);
        $this->assertSame(0555, $this->getProtectedProperty($command, 'dirPermissions'));
    }

    public function testFilePermissions()
    {
        $command = new CopyReplaceDir('/tmp');
        $command->filePermissions(0555);
        $this->assertSame(0555, $this->getProtectedProperty($command, 'filePermissions'));
    }

    public function testExclude()
    {
        $command = new CopyReplaceDir('/tmp');
        $command->exclude(['/foo']);
        $this->assertSame(['/foo'], $this->getProtectedProperty($command, 'exclude'));
    }

    public function testBaseWithoutReplaceRun()
    {
        $this->buildTestDir();
        $this->taskCopyReplaceDir([$this->dataFolder => $this->copyFolder])
            ->from('not_exist')
            ->to('exist')
            ->run();
        $this->assertStringEqualsFile(
            $this->copyFolder . '/test',
            "foo:#token_foo##token_bar#\nbar:#token_bar##token_foo#"
        );
        $this->assertStringEqualsFile(
            $this->copyFolder . '/foo/test',
            'foo:#token_bar#'
        );
        $this->assertStringEqualsFile(
            $this->copyFolder . '/bar/test',
            'bar:#token_foo#'
        );
    }

    public function testBaseWithReplaceRun()
    {
        $this->buildTestDir();
        $this->taskCopyReplaceDir([$this->dataFolder => $this->copyFolder])
            ->from([
                'token_foo',
                'token_bar',
            ])
            ->to([
                'foo',
                'bar',
            ])
            ->run();
        $this->assertStringEqualsFile(
            $this->copyFolder . '/test',
            "foo:foobar\nbar:barfoo"
        );
        $this->assertStringEqualsFile(
            $this->copyFolder . '/foo/test',
            'foo:bar'
        );
        $this->assertStringEqualsFile(
            $this->copyFolder . '/bar/test',
            'bar:foo'
        );
    }

    public function testRunWithCustomSeparator()
    {
        $this->buildTestDir("$$");
        $this->taskCopyReplaceDir([$this->dataFolder => $this->copyFolder])
            ->from([
                'token_foo',
                'token_bar',
            ])
            ->to([
                'foo',
                'bar',
            ])
            ->startDelimiter('$$')
            ->endDelimiter('$$')
            ->run();
        $this->assertStringEqualsFile(
            $this->copyFolder . '/test',
            "foo:foobar\nbar:barfoo"
        );
        $this->assertStringEqualsFile(
            $this->copyFolder . '/foo/test',
            'foo:bar'
        );
        $this->assertStringEqualsFile(
            $this->copyFolder . '/bar/test',
            'bar:foo'
        );
    }

    public function testRunWithExclude()
    {
        $this->buildTestDir();
        $this->taskCopyReplaceDir([$this->dataFolder => $this->copyFolder])
            ->from([
                'token_foo',
                'token_bar',
            ])
            ->to([
                'foo',
                'bar',
            ])
            ->exclude(['test'])
            ->run();
        $this->assertFileNotExists($this->copyFolder . '/test');
        $this->assertFileNotExists($this->copyFolder . '/foo/test');
        $this->assertFileNotExists($this->copyFolder . '/bar/test');
    }

    public function testRunWithBadSourceDirectory()
    {
        $this->expectException(\Robo\Exception\TaskException::class);
        $this->taskCopyReplaceDir(['/bad_directory' => '/bad_directory'])
            ->run();
    }

    public function testRunWithBadDistFile()
    {
        $this->buildTestDir();
        $this->expectException(\Robo\Exception\TaskException::class);
        file_put_contents($this->copyFolder . '/test', '');
        chmod($this->copyFolder . '/test', 0111);
        $this->taskCopyReplaceDir([$this->dataFolder => $this->copyFolder])
            ->run();
    }

    protected function buildTestDir($separator = '#')
    {
        // Delete test folder if exist
        if ($this->baseTestFolder) {
            Util::rmDir($this->baseTestFolder);
            $this->baseTestFolder = null;
        }

        // Create test data
        $this->baseTestFolder = sys_get_temp_dir() . "/globalis-robo-tasks-tests-copy-replace-dir" . uniqid();
        $this->dataFolder = $this->baseTestFolder . "/data/";
        $this->copyFolder = $this->baseTestFolder . "/copy/";
        mkdir($this->baseTestFolder);
        mkdir($this->dataFolder);
        mkdir($this->copyFolder);

        file_put_contents(
            $this->dataFolder . '/test',
            "foo:" . $separator . "token_foo" . $separator . $separator . "token_bar" . $separator . "\nbar:" . $separator . "token_bar" . $separator . $separator . "token_foo" . $separator
        );
        mkdir($this->dataFolder .'/foo');
        file_put_contents(
            $this->dataFolder . '/foo/test',
            "foo:" . $separator . "token_bar" . $separator
        );
        mkdir($this->dataFolder .'/bar');
        file_put_contents(
            $this->dataFolder . '/bar/test',
            "bar:" . $separator . "token_foo" . $separator
        );
    }
}
