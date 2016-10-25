<?php
namespace Globalis\Robo\Task\GitFlow\Release;

use Globalis\Robo\Task\GitFlow\Base;
use Robo\Result;

/**
 * Start a new Feature
 *
 * ``` php
 * <?php
 * $this->taskReleaseStart('Version', 'GitPath')
 *  ->developBranch('develop')
 *  ->repository('origin')
 *  ->fetchFlag(true)
 *  ->prefixBranch('relase_')
 *  ->run();
 * ?>
 * ```
 */
class Start extends Base
{
    protected $fetchFlag = true;

    protected $prefixBranch = "release_";

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

        if ($this->tagExists($this->name)) {
            $this->printTaskError(sprintf("Tag '%s' already exists. Pick another name.", $this->name));
            return false;
        }

        $branch = $this->prefixBranch . $this->name;

        if ($this->branchExists($branch)) {
            $this->printTaskError(sprintf("Branch '%s' already exists. Pick another name.", $branch));
            return false;
        }

        if (!$this->branchExists($this->developBranch)) {
            $this->printTaskError(sprintf("Branch '%s' does not exist and is required.", $this->developBranch));
            return false;
        }

        if ($this->remoteBranchExists($this->repository, $this->developBranch) && !$this->branchesEqual($this->developBranch, $this->repository . '/' . $this->developBranch)) {
            $this->printTaskError(sprintf("Branches '%s' and '%s' have diverged", $this->developBranch, $this->repository . '/' . $this->developBranch));
            return false;
        }

        $this->createBranch($branch, $this->developBranch);
        $this->printTaskSuccess("A new branch '{branch}' was created, based on '{base}'", ['branch' => $this->name, 'base' => $this->developBranch]);
        return Result::success($this);
    }
}
