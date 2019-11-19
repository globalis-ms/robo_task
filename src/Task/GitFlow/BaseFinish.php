<?php

namespace Globalis\Robo\Task\GitFlow;

abstract class BaseFinish extends Base
{
    protected $rebaseFlag = true;

    protected $deleteBranchAfter = true;

    protected $pushFlag = true;

    /**
     * Set rebase flag, do a rebase if is true
     *
     * @param  bool $rebaseFlag
     * @return $this
     */
    public function rebaseFlag($rebaseFlag)
    {
        $this->rebaseFlag = $rebaseFlag;
        return $this;
    }

    /**
     * Set delete branch flag, delete branch if is true
     *
     * @param  boolng $deleteBranchAfter
     * @return $this
     */
    public function deleteBranchAfter($deleteBranchAfter)
    {
        $this->deleteBranchAfter = $deleteBranchAfter;
        return $this;
    }

    /**
     * Set push flag, push if is true
     *
     * @param  bool $pushFlag
     * @return $this
     */
    public function pushFlag($pushFlag)
    {
        $this->pushFlag = $pushFlag;
        return $this;
    }
}
