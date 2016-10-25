<?php
namespace Globalis\Robo\Task\GitFlow;

use Globalis\Robo\Task\Core\Command;
use Symfony\Component\Process\Process;

trait Common
{
    protected function getBaseCommand($subCommand)
    {
        return new Command($this->pathToGit . ' ' . $subCommand);
    }

    protected function push($remote, $branch)
    {
        $this->getBaseCommand('push')
            ->args([$remote, $branch])
            ->execute();
    }

    protected function pushTags($remote)
    {
        $this->getBaseCommand('push')
            ->option('--tags')
            ->arg($remote)
            ->execute();
    }

    protected function rebase($distBranch)
    {
        $process = $this->getBaseCommand('rebase')
            ->option('-q')
            ->args($distBranch)
            ->executeWithoutException();
        return $process->isSuccessful();
    }

    protected function fetchAll()
    {
        $process = $this->getBaseCommand('fetch')
            ->option('-q')
            ->option('--all')
            ->execute();
    }

    protected function checkout($branchName)
    {
        $process = $this->getBaseCommand('checkout')
            ->option('-q')
            ->arg($branchName)
            ->executeWithoutException();
        return $process->isSuccessful();
    }

    protected function isBranchMergeInto($subject, $branch)
    {
        $process = $this->getBaseCommand('branch')
            ->option('--no-color')
            ->option('--contains', $subject)
            ->execute();
        $branches = explode(PHP_EOL, $process->getOutput());
        foreach ($branches as $key => $value) {
            //First delete * char
            $value = preg_replace('/^\*\s*/', '', $value);
            $branches[$key] = trim($value);
        }
        return in_array($branch, $branches);
    }

    protected function isCleanWorkingTree()
    {
        $process = $this->getBaseCommand('diff')
            ->option('--no-ext-diff')
            ->option('--ignore-submodules')
            ->option('--quiet')
            ->executeWithoutException();
        return $process->isSuccessful();
    }

    protected function getTags()
    {
        $process = $this->getBaseCommand('tag')
            ->execute();
        return explode(PHP_EOL, trim($process->getOutput()));
    }

    protected function tagExists($tag)
    {
        return in_array($tag, $this->getTags());
    }

    protected function createTag($tagName, $tagMessage = null)
    {
        $process = $this->getBaseCommand('tag');
        if ($tagMessage) {
            $process->option('-m', $tagMessage);
        }
        $process->arg($tagName)
            ->execute();
    }

    protected function createBranch($branchName, $baseBranch)
    {
        $process = $this->getBaseCommand('checkout')
            ->option('-b')
            ->args([$branchName, $baseBranch])
            ->execute();
    }

    protected function deleteLocalBranch($branch)
    {
        $this->getBaseCommand('branch')
            ->option('-d')
            ->arg($branch)
            ->execute();
    }

    protected function deleteRemoteBranch($remote, $branch = null)
    {
        if ($branch === null) {
            $remote = $this->remote;
        }
        $this->getBaseCommand('push')
            ->arg($remote)
            ->arg(':refs/heads/' . $branch)
            ->execute();
    }

    protected function branchesEqual($branchIn, $branchOut)
    {
        $process = $this->getBaseCommand('rev-parse')
            ->arg($branchIn)
            ->execute();
        $commit1 = trim($process->getOutput());
        $process = $this->getBaseCommand('rev-parse')
            ->arg($branchOut)
            ->execute();
        $commit2 = trim($process->getOutput());
        return ($commit1 === $commit2);
    }

    protected function getRemotes()
    {
        $process = $this->getBaseCommand('remote')
            ->execute();
        $remotes = explode(PHP_EOL, trim($process->getOutput()));
        foreach ($remotes as $key => $remote) {
            $remotes[$key] = trim($remote);
        }
        return $remotes;
    }

    protected function getAllBranches()
    {
        $branches = $this->getLocalBranches();
        $remotes = $this->getRemotes();
        $remoteBranches = $this->getRemoteBranches();
        foreach ($remoteBranches as $key => $value) {
            foreach ($remotes as $remote) {
                if ($value = preg_replace('/^' . preg_quote($remote) . '\//', '', $value, 1)) {
                    $branches[] = $value;
                    break;
                }
            }
        }
        return array_unique($branches);
    }

    protected function branchExists($branch)
    {
        return in_array($branch, $this->getAllBranches());
    }

    protected function getRemoteBranches()
    {
        $process = $this->getBaseCommand('branch')
            ->option('-r')
            ->option('--no-color')
            ->execute();
        $branches = explode(PHP_EOL, $process->getOutput());
        foreach ($branches as $key => $value) {
            //First delete * char
            $branch = preg_replace('/^\*\s*/', '', $value);
            $branches[$key] = trim($branch);
        }
        return $branches;
    }

    protected function remoteBranchExists($remote, $branch)
    {
        return in_array($remote . '/' . $branch, $this->getRemoteBranches());
    }

    protected function getLocalBranches()
    {
        $process = $this->getBaseCommand('branch')
            ->option('--no-color')
            ->execute();
        $branches = explode(PHP_EOL, $process->getOutput());
        foreach ($branches as $key => $value) {
            //First delete * char
            $branch = preg_replace('/^\*\s*/', '', $value);
            $branches[$key] = trim($branch);
        }
        return $branches;
    }

    protected function localBranchExists($branch)
    {
        return in_array($branch, $this->getLocalBranches());
    }
}
