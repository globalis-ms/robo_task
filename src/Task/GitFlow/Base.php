<?php
namespace Globalis\Robo\Task\GitFlow;

use Robo\Exception\TaskException;
use Robo\Task\BaseTask;

abstract class Base extends BaseTask
{
    use Common;

    protected $pathToGit;

    protected $repository = 'origin';

    protected $developBranch = 'develop';

    protected $masterBranch = 'master';

    protected $prefixBranch = '';

    protected $fetchFlag = true;

    protected $name;

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
}
