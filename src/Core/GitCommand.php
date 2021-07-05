<?php

namespace Globalis\Robo\Core;

use Globalis\Robo\Core\Command;
use Symfony\Component\Process\Process;

class GitCommand
{
    public $pathToGit;

    public function __construct($pathToGit = 'git')
    {
        $this->pathToGit = $pathToGit;
    }

    public function getBaseCommand($subCommand)
    {
        return new Command($this->pathToGit . ' ' . $subCommand);
    }

    public function push($remote, $branch)
    {
        $this->getBaseCommand('push ' . $remote . ' ' . $branch)
            ->execute();
    }

    public function pushTags($remote)
    {
        $this->getBaseCommand('push --tags ' . $remote)
            ->execute();
    }

    public function rebase($distBranch)
    {
        $process = $this->getBaseCommand('rebase -q ' . $distBranch)
            ->executeWithoutException();
        return $process->isSuccessful();
    }

    public function fetchAll()
    {
        $this->getBaseCommand('fetch -q --all')
            ->execute();
    }

    public function checkout($branchName)
    {
        $process = $this->getBaseCommand('checkout -q ' . $branchName)
            ->executeWithoutException();
        return $process->isSuccessful();
    }

    public function isBranchMergeInto($subject, $branch)
    {
        $process = $this->getBaseCommand('branch --no-color --contains ' . $subject)
            ->execute();
        $branches = explode(PHP_EOL, $process->getOutput());
        foreach ($branches as $key => $value) {
            //First delete * char
            $value = preg_replace('/^\*\s*/', '', $value);
            $branches[$key] = trim($value);
        }
        return in_array($branch, $branches);
    }

    public function isCleanWorkingTree()
    {
        $process = $this->getBaseCommand('diff --no-ext-diff --ignore-submodules --quiet --ignore-all-space --ignore-blank-lines --ignore-space-at-eol')
            ->executeWithoutException();
        return $process->isSuccessful();
    }

    public function getTags()
    {
        $process = $this->getBaseCommand('tag')
            ->execute();
        return explode(PHP_EOL, trim($process->getOutput()));
    }

    public function tagExists($tag)
    {
        return in_array($tag, $this->getTags());
    }

    public function createTag($tagName, $tagMessage = null)
    {
        $stringCommand = 'tag';
        if ($tagMessage) {
            $stringCommand .= '-m ' . $tagMessage;
        }
        $stringCommand .= ' ' . $tagName;
        return $this->getBaseCommand($stringCommand)
            ->execute()
            ->isSuccessful();
    }

    public function createBranch($branchName, $baseBranch)
    {
        $this->getBaseCommand('branch ' . $branchName . ' ' . $baseBranch)
            ->execute();
        $this->getBaseCommand('checkout ' . $branchName)
            ->execute();
    }

    public function deleteLocalBranch($branch)
    {
        $this->getBaseCommand('branch -d ' . $branch)
            ->execute();
    }

    public function deleteRemoteBranch($remote, $branch)
    {
        $this->getBaseCommand('push ' . $remote . ' :refs/heads/' . $branch)
            ->execute();
    }

    public function branchesEqual($branchIn, $branchOut)
    {
        $process = $this->getBaseCommand('rev-parse ' . $branchIn)
            ->execute();
        $commit1 = trim($process->getOutput());
        $process = $this->getBaseCommand('rev-parse ' . $branchOut)
            ->execute();
        $commit2 = trim($process->getOutput());
        return ($commit1 === $commit2);
    }

    public function getRemotes()
    {
        $process = $this->getBaseCommand('remote')
            ->execute();
        $remotes = explode(PHP_EOL, trim($process->getOutput()));
        foreach ($remotes as $key => $remote) {
            $remotes[$key] = trim($remote);
        }
        return $remotes;
    }

    public function getAllBranches()
    {
        $branches = $this->getLocalBranches();
        $remotes = $this->getRemotes();
        $remoteBranches = $this->getRemoteBranches();
        foreach ($remoteBranches as $value) {
            foreach ($remotes as $remote) {
                if ($value = preg_replace('/^' . preg_quote($remote) . '\//', '', $value, 1)) {
                    $branches[] = $value;
                    break;
                }
            }
        }
        return array_unique($branches);
    }

    public function branchExists($branch)
    {
        return in_array($branch, $this->getAllBranches());
    }

    public function getRemoteBranches()
    {
        $process = $this->getBaseCommand('branch -r --no-color')
            ->execute();
        $branches = explode(PHP_EOL, $process->getOutput());
        foreach ($branches as $key => $value) {
            //First delete * char
            $branch = preg_replace('/^\*\s*/', '', $value);
            $branches[$key] = trim($branch);
        }
        return array_filter($branches);
    }

    public function remoteBranchExists($remote, $branch)
    {
        return in_array($remote . '/' . $branch, $this->getRemoteBranches());
    }

    public function getLocalBranches()
    {
        $process = $this->getBaseCommand('branch --no-color')
            ->execute();
        $branches = explode(PHP_EOL, $process->getOutput());
        foreach ($branches as $key => $value) {
            // First delete * char
            $branch = preg_replace('/^\*\s*/', '', $value);
            $branches[$key] = trim($branch);
        }
        return array_filter($branches);
    }

    public function localBranchExists($branch)
    {
        return in_array($branch, $this->getLocalBranches());
    }
}
