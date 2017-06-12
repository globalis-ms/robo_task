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

        $this->assertTagNotExists($this->name);

        $branch = $this->prefixBranch . $this->name;

        $this->assertBranchNotExists($branch);

        $this->assertBranchExists($this->masterBranch);
        $this->assertRemoteBranchEquals($this->masterBranch);

        $this->createBranch($branch, $this->masterBranch);
        $this->printTaskSuccess("A new branch '{branch}' was created, based on '{base}'", ['branch' => $this->name, 'base' => $this->masterBranch]);
        return Result::success($this);
    }
}
