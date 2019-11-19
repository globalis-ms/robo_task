<?php

namespace Globalis\Robo\Task\GitFlow;

use Globalis\Robo\Core\GitCommand;
use Robo\Exception\TaskException;
use Robo\Task\BaseTask;

abstract class Base extends BaseTask
{
    protected $pathToGit;

    protected $repository = 'origin';

    protected $developBranch = 'develop';

    protected $masterBranch = 'master';

    protected $prefixBranch = '';

    protected $fetchFlag = true;

    protected $name;

    protected $gitCommand;

    public function __construct($name, $pathToGit = 'git')
    {
        $this->name = $name;
        $this->pathToGit = $pathToGit;
    }

    /**
     * Set main repository
     *
     * @param  string $repository
     * @return $this
     */
    public function repository($repository)
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * Set develop branch name
     *
     * @param  string $developBranch
     * @return $this
     */
    public function developBranch($developBranch)
    {
        $this->developBranch = $developBranch;
        return $this;
    }

    /**
     * Set master branch name
     *
     * @param  string $masterBranch
     * @return $this
     */
    public function masterBranch($masterBranch)
    {
        $this->masterBranch = $masterBranch;
        return $this;
    }

    /**
     * Set prefix branch
     *
     * @param  string $prefixBranch
     * @return $this
     */
    public function prefixBranch($prefixBranch)
    {
        $this->prefixBranch = $prefixBranch;
        return $this;
    }

    /**
     * Set fetch flag, fetch all if is true
     *
     * @param  bool $fetchFlag
     * @return $this
     */
    public function fetchFlag($fetchFlag)
    {
        $this->fetchFlag = $fetchFlag;
        return $this;
    }

    protected function assertBranchNotExists($branch)
    {
        if ($this->branchExists($branch)) {
            throw new TaskException($this, sprintf("Branch '%s' already exists. Pick another name.", $branch));
        }
    }

    protected function assertBranchExists($branch)
    {
        if (!$this->branchExists($branch)) {
            throw new TaskException($this, sprintf("Branch '%s' does not exist and is required.", $branch));
        }
    }

    protected function assertRemoteBranchExists($branch)
    {
        if (!$this->remoteBranchExists($this->repository, $branch)) {
            throw new TaskException($this, sprintf("Branch '%s' does not exist and is required.", $this->repository . '/' . $branch));
        }
    }

    protected function assertRemoteBranchEquals($branch)
    {
        if ($this->remoteBranchExists($this->repository, $branch) && !$this->branchesEqual($branch, $this->repository . '/' . $branch)) {
            throw new TaskException($this, sprintf("Branches '%s' and '%s' have diverged.", $branch, $this->repository . '/' . $branch));
        }
    }

    protected function assertTagNotExists($tag)
    {
        if ($this->tagExists($tag)) {
            throw new TaskException($this, sprintf("Tag '%s' already exists. Pick another name.", $tag));
        }
    }

    protected function getGit()
    {
        if ($this->gitCommand === null) {
            $this->gitCommand = new GitCommand($this->pathToGit);
        }

        return $this->gitCommand;
    }

    public function __call($method, $parameters)
    {
        return $this->getGit()->$method(...$parameters);
    }
}
