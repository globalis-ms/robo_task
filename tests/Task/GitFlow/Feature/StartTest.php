<?php
namespace Globalis\Robo\Tests\Task\GitFlow\Feature;

use Globalis\Robo\Tests\Util;
use Globalis\Robo\Tests\GitWorkDir;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Output\NullOutput;
use Robo\TaskAccessor;
use Robo\Robo;

class StartTest extends \PHPUnit\Framework\TestCase
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

        $this->git = GitWorkDir::getOrNew('git-flow-feature-start');
        $this->git->toLocalDir();
        // Delete feature branch
        Util::runProcess('git branch -a');
        Util::runProcess('git checkout develop');
        Util::runProcess('git reset --hard origin/develop');
        Util::runProcessWithoutException('git branch -D feature_foo');
    }

    // Scaffold the collection builder
    public function collectionBuilder()
    {
        $emptyRobofile = new \Robo\Tasks;
        return $this->getContainer()->get('collectionBuilder', [$emptyRobofile]);
    }

    public function testRunFeatureBranchExists()
    {
        // Create feature branch
        Util::runProcess('git checkout -b feature_foo develop');

        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branch 'feature_foo' already exists. Pick another name.");
        $this->taskFeatureStart('foo')
            ->run();
    }

    public function testRunDevelopBranchNotExist()
    {
        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branch 'bar' does not exist and is required.");
        $this->taskFeatureStart('foo')
            ->developBranch('bar')
            ->run();
    }

    public function testRunDevelopBranchDiverge()
    {
        Util::runProcess('git checkout develop');
        file_put_contents($this->git->localWorkDir() . '/test', 'Test', FILE_APPEND);
        Util::runProcess('git add .', $this->git->localWorkDir());
        Util::runProcess('git commit -m "test"', $this->git->localWorkDir());

        $this->expectException(\Robo\Exception\TaskException::class);
        $this->expectExceptionMessage("Branches 'develop' and 'origin/develop' have diverged.");
        $this->taskFeatureStart('foo')
            ->run();
    }

    public function testRun()
    {
        $this->taskFeatureStart('foo')
            ->run();
        $this->assertEquals(
            'feature_foo',
            trim(Util::runProcess(
                "git branch | grep \* | cut -d ' ' -f2",
                $this->git->localWorkDir()
            )->getOutput())
        );
    }
}
