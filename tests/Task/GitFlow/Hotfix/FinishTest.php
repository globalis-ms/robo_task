<?php
namespace Globalis\Robo\Tests\Task\GitFlow\Hotfix;

use Globalis\Robo\Tests\Util;
use Globalis\Robo\Tests\GitWorkDir;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;

class FinishTest extends \PHPUnit\Framework\TestCase
{

    use \Globalis\Robo\Task\GitFlow\loadTasks;
    use TaskAccessor;
    use ContainerAwareTrait;

    protected $git;

    // Set up the Robo container so that we can create tasks in our tests.
    public function setUp()
    {
        $container = Robo::createDefaultContainer(null, new NullOutput());
        $this->setContainer($container);

        $this->git = GitWorkDir::getOrNew('git-flow-hotfix-finish');
        $this->git->toLocalDir();
        // Create feature branch
        Util::runProcess('git checkout -b hotfix_foo master');
        file_put_contents($this->git->localWorkDir() . '/test', 'foo', FILE_APPEND);
        Util::runProcess('git add .');
        Util::runProcess('git commit -m "test_hotix_foo"');
        Util::runProcess('git push origin hotfix_foo');
    }

    public function tearDown()
    {
        // Delete feature branch
        $this->git->toRemoteDir();
        Util::runProcessWithoutException('git branch -D hotfix_foo');

        $this->git->toLocalDir();
        Util::runProcess('git checkout master');
        Util::runProcess('git reset --hard origin/master');
        Util::runProcess('git checkout develop');
        Util::runProcess('git reset --hard origin/develop');
        Util::runProcessWithoutException('git branch -D hotfix_foo');
        Util::runProcessWithoutException('git tag -d foo');
        Util::runProcess('git push origin :refs/tags/foo');
    }

    // Scaffold the collection builder
    public function collectionBuilder()
    {
        $emptyRobofile = new \Robo\Tasks;
        return $this->getContainer()->get('collectionBuilder', [$emptyRobofile]);
    }

    public function testRunHotfixBranchNotExist()
    {
        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branch 'hotfix_bar' does not exist and is required.");
        $this->taskHotfixFinish('bar')
            ->run();
    }

    public function testRunTagExists()
    {
        Util::runProcess('git tag foo');
        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Tag 'foo' already exists. Pick another name.");
        $this->taskHotfixFinish('foo')
            ->run();
    }

    public function testRunHotfixBranchDiverge()
    {
        file_put_contents($this->git->localWorkDir() . '/test', 'foo', FILE_APPEND);
        Util::runProcess('git add .');
        Util::runProcess('git commit -m "test"');

        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branches 'hotfix_foo' and 'origin/hotfix_foo' have diverged");
        $this->taskHotfixFinish('foo')
            ->run();
    }

    public function testRunMasterBranchNotExist()
    {
        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branch 'origin/bar' does not exist and is required.");
        $this->taskHotfixFinish('foo')
            ->masterBranch('bar')
            ->run();
    }

    public function testRunMasterBranchDiverge()
    {
        Util::runProcess('git checkout master');
        file_put_contents($this->git->localWorkDir() . '/test', 'foo', FILE_APPEND);
        Util::runProcess('git add .');
        Util::runProcess('git commit -m "test"');

        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branches 'master' and 'origin/master' have diverged");
        $this->taskHotfixFinish('foo')
            ->run();
    }

    public function testRunDevelopBranchNotExist()
    {
        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branch 'origin/bar' does not exist and is required.");
        $this->taskHotfixFinish('foo')
            ->developBranch('bar')
            ->run();
    }

    public function testRunDevelopBranchDiverge()
    {
        Util::runProcess('git checkout develop');
        file_put_contents($this->git->localWorkDir() . '/test', 'foo', FILE_APPEND);
        Util::runProcess('git add .');
        Util::runProcess('git commit -m "test"');

        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branches 'develop' and 'origin/develop' have diverged");
        $this->taskHotfixFinish('foo')
            ->run();
    }

    public function testRunWithDeleteBranch()
    {
        $this->taskHotfixFinish('foo')
            ->tagMessage('bar')
            ->run();
        $r = trim(Util::runProcess("git branch -a")->getOutput());
        $this->assertSame(
            [
                '* develop',
                '  master',
                '  remotes/origin/develop',
                '  remotes/origin/master',
            ],
            explode(
                PHP_EOL,
                $r
            )
        );
        $r = trim(Util::runProcess("git tag -n --list foo")->getOutput());
        $this->assertSame(
            [
                'foo',
                'bar'
            ],
            array_values(
                array_filter(
                    explode(
                        ' ',
                        $r
                    )
                )
            )
        );
    }

    public function testRunWithoutDeleteBranch()
    {
        $this->taskHotfixFinish('foo')
            ->deleteBranchAfter(false)
            ->noTag(true)
            ->run();
        $r = trim(
            Util::runProcess("git branch -a")->getOutput()
        );
        $this->assertSame(
            [
                '* develop',
                '  hotfix_foo',
                '  master',
                '  remotes/origin/develop',
                '  remotes/origin/hotfix_foo',
                '  remotes/origin/master',
            ],
            explode(
                PHP_EOL,
                $r
            )
        );

        $r = trim(Util::runProcess("git tag -n --list foo")->getOutput());
        $this->assertSame(
            '',
            $r
        );
    }
}
