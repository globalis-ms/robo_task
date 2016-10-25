<?php
namespace Globalis\Robo\Task\GitFlow\Feature;

use Globalis\Robo\Task\GitFlow\Base;
use Robo\Result;

/**
 * Finish a new Feature
 *
 * ``` php
 * <?php
 * $this->taskFeatureFinish('BranchName', 'GitPath')
 *  ->developBranch('develop')
 *  ->repository('origin')
 *  ->fetchFlag(true)
 *  ->rebaseFlag(true)
 *  ->deleteBranchAfter(true)
 *  ->prefixBranch('feature_')
 *  ->pushFlag(true)
 *  ->run();
 * ?>
 * ```
 */
class Finish extends Base
{
    protected $fetchFlag = true;
    protected $rebaseFlag = true;
    protected $pushFlag = true;
    protected $deleteBranchAfter = true;
    protected $prefixBranch = 'feature_';

    public function fetchFlag($fetchFlag)
    {
        $this->fetchFlag = $fetch;
        return $this;
    }

    public function rebaseFlag($rebaseFlag)
    {
        $this->rebaseFlag = $rebaseFlag;
        return $this;
    }

    public function deleteBranchAfter($deleteBranchAfter)
    {
        $this->deleteBranchAfter = $deleteBranchAfter;
        return $this;
    }

    public function prefixBranch($prefixBranch)
    {
        $this->prefixBranch = $prefixBranch;
        return $this;
    }

    public function pushFlag($pushFlag)
    {
        $this->pushFlag = $pushFlag;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $branch = $this->prefixBranch . $this->name;

        if (!$this->branchExists($branch)) {
            $this->printTaskError(sprintf("Branch '%s' does not exist and is required.", $branch));
            return false;
        }

        if ($this->fetchFlag) {
            $this->fetchAll();
        }

        if (!$this->branchExists($branch)) {
            $this->printTaskError("Branch '$branch' does not exist and is required.");
            return false;
        }

        if ($this->remoteBranchExists($this->repository, $branch) && !$this->branchesEqual($branch, $this->repository . '/' . $branch)) {
            $this->printTaskError(sprintf("Branches '%s' and '%s' have diverged", $branch, $this->repository . '/' . $branch));
            return false;
        }

        if (!$this->branchExists($this->developBranch)) {
            $this->printTaskError("Branch '$branch' does not exist and is required.");
            return false;
        }

        if ($this->remoteBranchExists($this->repository, $this->developBranch) && !$this->branchesEqual($this->developBranch, $this->repository . '/' . $this->developBranch)) {
            $this->printTaskError(sprintf("Branches '%s' and '%s' have diverged", $this->developBranch, $this->repository . '/' . $this->developBranch));
            return false;
        }

        $optMerge = '--no-ff';
        if ($this->rebaseFlag) {
            $optMerge = '--ff';
            // Rebase develop in base
            $this->printTaskInfo('Try to rebase {branch}', ['branch' => $branch]);
            if (!$this->isCleanWorkingTree()) {
                $this->printTaskError("Working tree contains unstaged changes. Aborting.");
                return false;
            }
            $this->checkout($branch);
            if (!$this->rebase($this->developBranch)) {
                $this->printTaskWarning("Finish was aborted due to conflicts during rebase.");
                $this->printTaskWarning("Please finish the rebase manually now.");
                return false;
            }
        }
        // Merge into BASE
        $this->checkout($this->developBranch);
        $process = $this->getBaseCommand('merge')
            ->option($optMerge)
            ->arg($branch)
            ->executeWithoutException();

        if (!$process->isSuccessful()) {
            $this->printTaskWarning("There were merge conflicts. To resolve the merge conflict manually, use:");
            $this->printTaskWarning(" - git mergetool");
            $this->printTaskWarning(" - git commit");
            return false;
        }
        $this->printTaskSuccess("The feature branch '{branch}' was merged into '{base}'", ['branch' => $branch, 'base' => $this->developBranch]);

        if ($this->deleteBranchAfter) {
            $this->deleteLocalBranch($branch);
            $this->printTaskSuccess("The feature branch '{branch}' has been removed", ['branch' => $branch]);
        } else {
            $this->printTaskInfo("The feature branch '{branch}' is still available", ['branch' => $branch]);
        }

        $this->printTaskSuccess("The feature branch '{branch}' was merged into '{base}'", ['branch' => $branch, 'base' => $this->developBranch]);

        if ($this->pushFlag) {
            $this->push($this->repository, $this->developBranch);

            if ($this->deleteBranchAfter) {
                if ($this->remoteBranchExists($this->repository, $branch)) {
                    $this->deleteRemoteBranch($this->repository, $branch);
                }
            }

            $this->printTaskSuccess("'{developBranch}' has been pushed to '{repository}'", ['developBranch' => $this->developBranch, 'repository' => $this->repository]);
        }
        return Result::success($this);
    }
}
