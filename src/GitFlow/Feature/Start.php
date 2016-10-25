<?php
namespace Globalis\Robo\Task\GitFlow\Feature;

use Globalis\Robo\Task\GitFlow\Base;
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
    protected $fetchFlag = true;
    protected $prefixBranch = 'feature_';

    public function fetchFlag($fetchFlag)
    {
        $this->fetchFlag = $fetch;
        return $this;
    }

    public function prefixBranch($prefixBranch)
    {
        $this->prefixBranch = $prefixBranch;
        return $this;
    }

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
            $this->printTaskError(sprintf("Branch '%s'  already exists. Pick another name.", $branch));
            return false;
        }

        if (!$this->branchExists($this->developBranch)) {
            $this->printTaskError("Branch '$this->developBranch' does not exist and is required.");
            return false;
        }

        if ($this->remoteBranchExists($this->repository, $this->developBranch) && !$this->branchesEqual($this->developBranch, $this->repository . '/' . $this->developBranch)) {
            $this->printTaskError(sprintf("Branches '%s' and '%s' have diverged", $this->developBranch, $this->repository . '/' . $this->developBranch));
            return false;
        }

        $this->createBranch($branch, $this->developBranch);
        $this->printTaskSuccess(sprintf("A new branch '%s' was created, based on '%s'", $branch, $this->developBranch));
        return Result::success($this);
    }
}
