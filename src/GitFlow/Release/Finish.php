<?php
namespace Globalis\Robo\Task\GitFlow\Release;

use Globalis\Robo\Task\GitFlow\BaseFinish;
use Robo\Result;

/**
 * Finish a release
 *
 * ``` php
 * <?php
 * $this->taskReleaseFinish('version', 'GitPath')
 *  ->developBranch('develop')
 *  ->masterBranch('master')
 *  ->repository('origin')
 *  ->fetchFlag(true)
 *  ->rebaseFlag(true)
 *  ->deleteBranchAfter(true)
 *  ->prefixBranch('release_')
 *  ->noTag(false)
 *  ->tagMessage(null)
 *  ->pushFlag(true)
 *  ->run();
 * ?>
 * ```
 */
class Finish extends BaseFinish
{
    protected $prefixBranch = 'release_';

    protected $noTag = false;

    protected $tagMessage = null;

    /**
     * Set noTag flag, tag master branch if is false
     *
     * @param  boolean $noTag
     * @return $this
     */
    public function noTag($noTag)
    {
        $this->noTag = $noTag;
        return $this;
    }

    /**
     *  Set tag message
     *
     * @param  string $tagMessage
     * @return $this
     */
    public function tagMessage($tagMessage)
    {
        $this->tagMessage = $tagMessage;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $branch = $this->prefixBranch . $this->name;

        if ($this->tagExists($this->name)) {
            $this->printTaskError(sprintf("Tag '%s' already exists. Pick another name.", $this->name));
            return false;
        }

        if (!$this->branchExists($branch)) {
            $this->printTaskError(sprintf("Branch '%s' does not exist and is required.", $branch));
            return false;
        }

        if ($this->fetchFlag) {
            $this->fetchAll();
        }

        if (!$this->branchExists($branch)) {
            $this->printTaskError(sprintf("Branch '%s' does not exist and is required.", $branch));
            return false;
        }

        if ($this->remoteBranchExists($this->repository, $branch) && !$this->branchesEqual($branch, $this->repository . '/' . $branch)) {
            $this->printTaskError(sprintf("Branches '%s' and '%s' have diverged", $branch, $this->repository . '/' . $branch));
            return false;
        }

        if (!$this->branchExists($this->masterBranch)) {
            $this->printTaskError(sprintf("Branch '%s' does not exist and is required.", $this->masterBranch));
            return false;
        }

        if ($this->remoteBranchExists($this->repository, $this->masterBranch) && !$this->branchesEqual($this->masterBranch, $this->repository . '/' . $this->masterBranch)) {
            $this->printTaskError(sprintf("Branches '%s' and '%s' have diverged",  $this->masterBranch, $this->repository . '/' . $this->masterBranch));
            return false;
        }

        if (!$this->branchExists($this->developBranch)) {
            $this->printTaskError(sprintf("Branch '%s' does not exist and is required.", $this->developBranch));
            return false;
        }

        if ($this->remoteBranchExists($this->repository, $this->developBranch) && !$this->branchesEqual($this->developBranch, $this->repository . '/' . $this->developBranch)) {
            $this->printTaskError(sprintf("Branches '%s' and '%s' have diverged",  $this->developBranch, $this->repository . '/' . $this->developBranch));
            return false;
        }

        // merge into Master
        if (!$this->isBranchMergeInto($branch, $this->masterBranch)) {
            $this->checkout($this->masterBranch);
            $process= $this->getBaseCommand('merge')
                ->option('--no-ff')
                ->arg($branch)
                ->executeWithoutException();

            if (!$process->isSuccessful()) {
                $this->printTaskWarning("There were merge conflicts. To resolve the merge conflict manually, use:");
                $this->printTaskWarning(" - git mergetool");
                $this->printTaskWarning(" - git commit");
                return false;
            }
            $this->printTaskSuccess("The release branch '{branch}' was merged into '{base}'", ['branch' => $branch, 'base' => $this->masterBranch]);
        }

        // Create Tag
        if ($this->noTag === false && !$this->tagExists($this->name)) {
            $this->checkout($this->masterBranch);
            $this->createTag($this->name, $this->tagMessage);
            $this->printTaskSuccess("The release was tagged '{tag}'", ['tag' => $this->name]);
        }

        if (!$this->isBranchMergeInto($branch, $this->developBranch)) {
            // merge into Develop
            $this->checkout($this->developBranch);
            $process = $this->getBaseCommand('merge')
                ->option('--no-ff')
                ->arg($branch)
                ->executeWithoutException();

            if (!$process->isSuccessful()) {
                $this->printTaskWarning("There were merge conflicts. To resolve the merge conflict manually, use:");
                $this->printTaskWarning(" - git mergetool");
                $this->printTaskWarning(" - git commit");
                return false;
            }
            $this->printTaskSuccess("The release branch '{branch}' was merged into '{base}'", ['branch' => $branch, 'base' => $this->developBranch]);
        }

        if ($this->deleteBranchAfter) {
            $this->deleteLocalBranch($branch);
            $this->printTaskSuccess('The release branch "{branch}" has been removed', ['branch' => $branch]);
        } else {
            $this->printTaskInfo('The release branch "{branch}" is still available"', ['branch' => $branch]);
        }

        if ($this->pushFlag) {
            $this->push($this->repository, $this->developBranch);
            $this->push($this->repository, $this->masterBranch);

            if ($this->deleteBranchAfter) {
                if ($this->remoteBranchExists($this->repository, $branch)) {
                    $this->deleteRemoteBranch($this->repository, $branch);
                }
            }

            if ($this->noTag === false) {
                $this->pushTags($this->repository);
            }
            $this->printTaskSuccess("'{developBranch}', '{masterBranch}' and tags have been pushed to '{repository}'", ['developBranch' => $this->developBranch, 'masterBranch' => $this->masterBranch, 'repository' => $this->repository]);
        }
        return Result::success($this);
    }
}
