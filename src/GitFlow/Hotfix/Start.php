<?php
namespace Globalis\Robo\Task\GitFlow\Hotfix;

use Globalis\Robo\Task\GitFlow\Base;
use Robo\Result;

/**
 * Start a new Feature
 *
 * ``` php
 * <?php
 * $this->taskHotfixStart('Version', 'GitPath')
 *  ->masterBranch('master')
 *  ->repository('origin')
 *  ->fetchFlag(true)
 *  ->prefixBranch('hotfix_')
 *  ->run();
 * ?>
 * ```
 */
class Start extends Base
{
    protected $fetchFlag = true;

    protected $prefixBranch = "hotfix_";

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
            $this->printTaskError(sprintf("Branch '%s'  already exists. Pick another name.", $branch));
            return false;
        }

        if (!$this->branchExists($this->masterBranch)) {
            $this->printTaskError(sprintf("Branch '%s' does not exist and is required.", $this->masterBranch));
            return false;
        }

        if ($this->remoteBranchExists($this->repository, $this->masterBranch) && !$this->branchesEqual($this->masterBranch, $this->repository . '/' . $this->masterBranch)) {
            $this->printTaskError(sprintf("Branches '%s' and '%s' have diverged", $this->masterBranch, $this->repository . '/' . $this->masterBranch));
            return false;
        }

        $this->createBranch($branch, $this->masterBranch);
        $this->printTaskSuccess("A new branch '{branch}' was created, based on '{base}'", ['branch' => $this->name, 'base' => $this->masterBranch]);
        return Result::success($this);
    }
}
