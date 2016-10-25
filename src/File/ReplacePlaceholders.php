<?php
namespace Globalis\Robo\Task\File;

use Robo\Result;
use Robo\Task\BaseTask;

/**
 * Performs search and replace inside a files.
 *
 * ``` php
 * <?php
 * $this->taskReplacePlacehoders('VERSION')
 *  ->from('0.2.0')
 *  ->to('0.3.0')
 *  ->startDelimiter('##')
 *  ->endDelimiter('##')
 *  ->run();
 * ?>
 * ```
 */
class ReplacePlaceholders extends BaseTask
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * @var string[]
     */
    protected $from;

    /**
     * @var string[]
     */
    protected $to;

    /**
     * @var string
     */
    protected $regex;

    /**
     * @var string
     */
    protected $startDelimiter = '##';

    /**
     * @var string
     */
    protected $endDelimiter = '##';

    /**
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Set string(s) to be replaced
     * @param string|array $from
     * @return $this
     */
    public function from($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * Set value(s) to be set as a replacement
     *
     * @param string $to
     * @return $this
     */
    public function to($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * Set end delimiter
     *
     * @param string $delimiter
     * @return $this
     */
    public function endDelimiter($delimiter)
    {
        $this->endDelimiter = $delimiter;
        return $this;
    }

    /**
     * Set start delimiter
     *
     * @param string $delimiter
     * @return $this
     */
    public function startDelimiter($delimiter)
    {
        $this->startDelimiter = $delimiter;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if (!file_exists($this->filename)) {
            $this->printTaskError('File {filename} does not exist', ['filename' => $this->filename]);
            return false;
        }
        $text = file_get_contents($this->filename);
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
        if ($count > 0) {
            $res = file_put_contents($this->filename, $text);
            if ($res === false) {
                return Result::error($this, "Error writing to file {filename}.", ['filename' => $this->filename]);
            }
            $this->printTaskSuccess("{filename} updated. {count} items replaced", ['filename' => $this->filename, 'count' => $count]);
        } else {
            $this->printTaskInfo("{filename} unchanged. {count} items replaced", ['filename' => $this->filename, 'count' => $count]);
        }
        return Result::success($this, '', ['replaced' => $count]);
    }
}
