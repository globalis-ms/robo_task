<?php
namespace Globalis\Robo\Task\GitFlow\Feature;

use Globalis\Robo\Task\GitFlow\Base;
use Robo\Exception\TaskException;
use Robo\Result;

/**
 * Start a new Feature
 *
 * ``` php
 * <?php
 * $this->taskFeatureStart('BranchName', 'GitPath')
 *  ->developBranch('develop')
 *  ->repository('origin')
 *  ->prefixBranch('feature_')
 *  ->fetchFlag(true)
 *  ->run();
 * ?>
 * ```
 */
class Start extends Base
{
    protected $prefixBranch = 'feature_';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if ($this->fetchFlag) {
            $this->fetchAll();
        }

        $branch = $this->prefixBranch . $this->name;

        $this->assertBranchNotExists($branch);

        $this->assertBranchExists($this->developBranch);
        $this->assertRemoteBranchEquals($this->developBranch);

        $this->createBranch($branch, $this->developBranch);
        $this->printTaskSuccess(sprintf("A new branch '%s' was created, based on '%s'", $branch, $this->developBranch));
        return Result::success($this);
    }
}
