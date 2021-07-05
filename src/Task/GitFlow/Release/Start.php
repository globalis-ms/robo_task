<?php

namespace Globalis\Robo\Task\GitFlow\Release;

use Globalis\Robo\Task\GitFlow\Base;
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

        $this->assertTagNotExists($this->name);

        $branch = $this->prefixBranch . $this->name;

        $this->assertBranchNotExists($branch);

        $this->assertBranchExists($this->developBranch);
        $this->assertRemoteBranchEquals($this->developBranch);

        $this->createBranch($branch, $this->developBranch);
        $this->printTaskSuccess("A new branch '{branch}' was created, based on '{base}'", ['branch' => $this->name, 'base' => $this->developBranch]);
        return Result::success($this);
    }
}
