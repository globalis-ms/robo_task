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

        if ($this->branchExists($branch)) {
            throw new TaskException($this, sprintf("Branch '%s' already exists. Pick another name.", $branch));
        }

        if (!$this->branchExists($this->developBranch)) {
            throw new TaskException($this, "Branch '$this->developBranch' does not exist and is required.");
        }

        if ($this->remoteBranchExists($this->repository, $this->developBranch) && !$this->branchesEqual($this->developBranch, $this->repository . '/' . $this->developBranch)) {
            throw new TaskException($this, sprintf("Branches '%s' and '%s' have diverged.", $this->developBranch, $this->repository . '/' . $this->developBranch));
        }

        $this->createBranch($branch, $this->developBranch);
        $this->printTaskSuccess(sprintf("A new branch '%s' was created, based on '%s'", $branch, $this->developBranch));
        return Result::success($this);
    }
}
