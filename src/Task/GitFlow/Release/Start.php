<?php
namespace Globalis\Robo\Task\GitFlow\Release;

use Globalis\Robo\Task\GitFlow\Base;
use Robo\Exception\TaskException;
use Robo\Result;

/**
 * Start a new Release
 *
 * ``` php
 * <?php
 * $this->taskReleaseStart('Version', 'GitPath')
 *  ->developBranch('develop')
 *  ->repository('origin')
 *  ->fetchFlag(true)
 *  ->prefixBranch('release_')
 *  ->run();
 * ?>
 * ```
 */
class Start extends Base
{
    protected $prefixBranch = "release_";

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

        if (!$this->branchExists($this->developBranch)) {
            throw new TaskException($this, sprintf("Branch '%s' does not exist and is required.", $this->developBranch));
        }

        if ($this->remoteBranchExists($this->repository, $this->developBranch) && !$this->branchesEqual($this->developBranch, $this->repository . '/' . $this->developBranch)) {
            throw new TaskException($this, sprintf("Branches '%s' and '%s' have diverged.", $this->developBranch, $this->repository . '/' . $this->developBranch));
        }

        $this->createBranch($branch, $this->developBranch);
        $this->printTaskSuccess("A new branch '{branch}' was created, based on '{base}'", ['branch' => $this->name, 'base' => $this->developBranch]);
        return Result::success($this);
    }
}
