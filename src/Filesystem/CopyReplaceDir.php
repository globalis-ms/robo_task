<?php
namespace Globalis\Robo\Task\Filesystem;
use Robo\Common\ResourceExistenceChecker;
use Robo\Result;
use Robo\Exception\TaskException;
use Robo\Task\BaseTask;
use Symfony\Component\Filesystem\Filesystem as sfFilesystem;

/**
 * Copies one dir into another and replace variables
 *
 * ``` php
 * <?php
 * $this->taskCopyReplaceDir(['dist/config' => 'config']
 *  ->from(array('##dbname##', '##dbhost##'))
 *  ->to(array('robo', 'localhost'))
 *  ->startDelimiter('##')
 *  ->endDelimiter('##')
 *  ->dirPermissions(0755)
 *  ->filePermissions(0644)
 *  ->exclude([file, file])
 *  ->run();
 * ?>
 * ```
 */
class CopyReplaceDir extends BaseTask
{
    use ResourceExistenceChecker;

    protected $exclude = [];

    protected $dirs = [];

    protected $fs;

    protected $from;

    protected $to;

    protected $regex;

    protected $startDelimiter = '#';

    protected $endDelimiter = '#';

    protected $dirPermissions = 0755;

    protected $filePermissions = 0644;

    public function __construct($dirs)
    {
        is_array($dirs)
            ? $this->dirs = $dirs
            : $this->dirs[] = $dirs;
        $this->fs = new sfFilesystem();
    }


    public function from($from)
    {
        $this->from = $from;
        return $this;
    }

    public function to($to)
    {
        $this->to = $to;
        return $this;
    }

    public function regex($regex)
    {
        $this->regex = $regex;
        return $this;
    }

    public function setEndDelimiter($delimiter)
    {
        $this->endDelimiter = $delimiter;
        return $this;
    }

    public function setStartDelimiter($delimiter)
    {
        $this->startDelimiter = $delimiter;
        return $this;
    }

    /**
     * Sets the default folder permissions for the destination if it doesn't exist
     *
     * @param int $value
     * @return $this
     */
    public function dirPermissions($value)
    {
        $this->dirPermissions = $value;
        return $this;
    }

    /**
     * Sets the default file permissions for the destination if it doesn't exist
     *
     * @param int $value
     * @return $this
     */
    public function filePermissions($value)
    {
        $this->filePermissions = $value;
        return $this;
    }

    /**
     * List files to exclude.
     *
     * @param array $exclude
     * @return $this
     */
    public function exclude($exclude = [])
    {
        $this->exclude = $exclude;
        return $this;
    }


    public function run()
    {
        if (!$this->checkResources($this->dirs, 'dir')) {
            return Result::error($this, 'Source directories are missing!');
        }
        foreach ($this->dirs as $src => $dst) {
            $this->copyDir($src, $dst);
        }
        return Result::success($this);
    }

    /**
     * Copies a directory to another location.
     *
     * @param string $src Source directory
     * @param string $dst Destination directory
     * @throws \Robo\Exception\TaskException
     * @return void
     */
    protected function copyDir($src, $dst)
    {
        $dir = @opendir($src);
        if (false === $dir) {
            throw new TaskException($this, "Cannot open source directory '" . $src . "'");
        }
        if (!is_dir($dst)) {
            mkdir($dst, $this->dirPermissions, true);
            chmod($dst, $this->dirPermissions);
        }
        while (false !== ($file = readdir($dir))) {
            if (in_array($file, $this->exclude)) {
                 continue;
            }
            if (($file !== '.') && ($file !== '..')) {
                $srcFile = $src . '/' . $file;
                $destFile = $dst . '/' . $file;
                if (is_dir($srcFile)) {
                    $this->copyDir($srcFile, $destFile);
                } else {
                    $this->copyFile($srcFile, $destFile);
                }
            }
        }
        closedir($dir);
    }

    protected function copyFile($src, $dst)
    {
        $text = file_get_contents($src);
        if ($this->regex) {
            $text = preg_replace($this->regex, $this->to, $text, -1, $count);
        } else {
            $from = $this->from;
            if (is_array($from)) {
                foreach ($from as $key => $value) {
                    $from[$key] = $this->startDelimiter . $value . $this->endDelimiter;
                }
            } else {
               $from = $this->startDelimiter . $this->from . $this->endDelimiter;
            }
            $text = str_replace($from, $this->to, $text, $count);
        }
        $res = file_put_contents($dst, $text);
        if ($res === false) {
            throw new TaskException($this, "Cannot copy source file '" . $src . "' to '"  . $dst . "'");
        }
        chmod($dst, $this->filePermissions);
        $this->printTaskInfo('Copied from {source} to {destination}. {count} items replaced.', ['source' => $src, 'destination' => $dst, 'count' => $count]);

    }
}
