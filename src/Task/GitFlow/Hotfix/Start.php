<?php
namespace Globalis\Robo\Task\GitFlow\Hotfix;

use Globalis\Robo\Task\GitFlow\Base;
use Robo\Exception\TaskException;
use Robo\Result;

/**
 * Start a new Hotfix
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
    protected $prefixBranch = "hotfix_";

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if ($this->fetchFlag) {
            $this->fetchAll();
        }

        if ($this->tagExists($this->name)) {
            throw new TaskException($this, sprintf("Tag '%s' already exists. Pick another name.", $this->name));
        }

        $branch = $this->prefixBranch . $this->name;

        if ($this->branchExists($branch)) {
            throw new TaskException($this, sprintf("Branch '%s' already exists. Pick another name.", $branch));
        }

        if (!$this->branchExists($this->masterBranch)) {
            throw new TaskException($this, sprintf("Branch '%s' does not exist and is required.", $this->masterBranch));
        }

        if ($this->remoteBranchExists($this->repository, $this->masterBranch) && !$this->branchesEqual($this->masterBranch, $this->repository . '/' . $this->masterBranch)) {
            throw new TaskException($this, sprintf("Branches '%s' and '%s' have diverged.", $this->masterBranch, $this->repository . '/' . $this->masterBranch));
        }

        $this->createBranch($branch, $this->masterBranch);
        $this->printTaskSuccess("A new branch '{branch}' was created, based on '{base}'", ['branch' => $this->name, 'base' => $this->masterBranch]);
        return Result::success($this);
    }
}
