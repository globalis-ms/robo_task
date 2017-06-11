<?php
namespace Globalis\Robo\Task\GitFlow\Feature;

use Globalis\Robo\Task\GitFlow\BaseFinish;
use Robo\Exception\TaskException;
use Robo\Result;

/**
 * Finish a Feature
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
class Finish extends BaseFinish
{
    protected $prefixBranch = 'feature_';

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $branch = $this->prefixBranch . $this->name;

        if (!$this->branchExists($branch)) {
            throw new TaskException($this, sprintf("Branch '%s' does not exist and is required.", $branch));
        }

        if ($this->fetchFlag) {
            $this->fetchAll();
        }

        if ($this->remoteBranchExists($this->repository, $branch) && !$this->branchesEqual($branch, $this->repository . '/' . $branch)) {
            throw new TaskException($this, sprintf("Branches '%s' and '%s' have diverged", $branch, $this->repository . '/' . $branch));
        }

        if (!$this->branchExists($this->developBranch)) {
            throw new TaskException($this, "Branch '$this->developBranch' does not exist and is required.");
        }

        if ($this->remoteBranchExists($this->repository, $this->developBranch) && !$this->branchesEqual($this->developBranch, $this->repository . '/' . $this->developBranch)) {
            throw new TaskException($this, sprintf("Branches '%s' and '%s' have diverged", $this->developBranch, $this->repository . '/' . $this->developBranch));
        }

        $optMerge = '--no-ff';
        if ($this->rebaseFlag) {
            $optMerge = '--ff';
            // Rebase develop in base
            $this->printTaskInfo('Try to rebase {branch}', ['branch' => $branch]);
            if (!$this->isCleanWorkingTree()) {
                throw new TaskException($this, "Working tree contains unstaged changes. Aborting.");
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

        if ($this->pushFlag) {
            $this->push($this->repository, $this->developBranch);
            $this->printTaskSuccess("'{developBranch}' has been pushed to '{repository}'", ['developBranch' => $this->developBranch, 'repository' => $this->repository]);
        }

        if ($this->deleteBranchAfter) {
            $this->deleteLocalBranch($branch);
            if ($this->pushFlag && $this->remoteBranchExists($this->repository, $branch)) {
                $this->deleteRemoteBranch($this->repository, $branch);
            }
            $this->printTaskSuccess("The feature branch '{branch}' has been removed", ['branch' => $branch]);
        } else {
            $this->printTaskInfo("The feature branch '{branch}' is still available", ['branch' => $branch]);
        }

        $this->printTaskSuccess("The feature branch '{branch}' was merged into '{base}'", ['branch' => $branch, 'base' => $this->developBranch]);

        return Result::success($this);
    }
}
