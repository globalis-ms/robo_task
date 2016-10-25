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

    protected $name;

    public function __construct($name, $pathToGit = 'git')
    {
        $this->name = $name;
        $this->pathToGit = $pathToGit;
    }


    public function name($name)
    {
        $this->name =$name;
        return $this;
    }

    public function repository($repository)
    {
        $this->repository = $repository;
        return $this;
    }

    public function developBranch($developBranch)
    {
        $this->developBranch = $developBranch;
        return $this;
    }

    public function masterBranch($masterBranch)
    {
        $this->masterBranch = $masterBranch;
        return $this;
    }
}
