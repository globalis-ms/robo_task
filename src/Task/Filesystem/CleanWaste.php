<?php
namespace Globalis\Robo\Task\Filesystem;

use Robo\Common\ResourceExistenceChecker;
use Robo\Result;
use Robo\Task\Filesystem\BaseDir;

/**
 * Deletes all files from specified dir, ignoring git files.
 *
 * ``` php
 * <?php
 * $this->taskCleanDir(['tmp','logs'])
 * ->wastePatterns([
 *     "/\.DS_Store/",
 *     "/Thumbs\.db/",
 *     "/.*~/",
 *     "/\._.*\/",
 *  ])->run();
 * ?>
 * ```
 */
class CleanWaste extends BaseDir
{
    use ResourceExistenceChecker;

    protected $wastePatterns = [
        "/\.DS_Store/",
        "/Thumbs\.db/",
        "/.*~/",
        "/\._.*/",
    ];

    /**
     * Set waste patterns to delete
     *
     * @param  array  $wastePatterns
     * @return $this
     */
    public function wastePatterns(array $wastePatterns)
    {
        $this->wastePatterns = $wastePatterns;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if (!$this->checkResources($this->dirs, 'dir')) {
            return Result::error($this, 'Source directories are missing!');
        }
        foreach ($this->dirs as $dir) {
            $this->cleanWaste($dir);
            $this->printTaskInfo("Cleaned {dir}", ['dir' => $dir]);
        }
        return Result::success($this);
    }

    protected function cleanWaste($path)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $path) {
            if (!$path->isDir()) {
                $file = (string)$path;
                if (basename($file) === '.gitignore' || basename($file) === '.gitkeep') {
                    continue;
                }
                if ($this->isWasteFile(basename($file))) {
                    $this->printTaskInfo("{file} removed", ['file' => (string)$path]);
                    $this->fs->remove($file);
                }
            }
        }
    }

    protected function isWasteFile($filename)
    {
        foreach ($this->wastePatterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                return true;
            }
        }
        return false;
    }
}
