<?php

namespace Globalis\Robo\Task\Configuration;

use Robo\Exception\TaskException;
use Robo\Result;
use Robo\Robo;
use Robo\Task\BaseTask;

/**
 * Configuration variables
 *
 * ``` php
 * <?php
 * $this->taskConfiguration()
 *  ->initConfig(
 *     config_key' => [
 *        'question' => 'question ?',
 *        'default' => 'ddd',
 *        'choises' => ['choice']
 *     ]
 *  ])
 *  ->initSettings([
 *       'config_key' => 'value'
 *   ]),
 *  ->initLocal([
 *      'config_key' => [
 *          'question' => 'question ?',
 *      ]
 *  ]),
 *  ->setLocalFilePath($localFilePath) // Default User Home
 *  ->setConfigFilePath($configFilePath) // Project Dir / .my_config
 *  ->run();
 * ?>
 * ```
 */
class Configuration extends BaseTask
{
    use \Robo\Common\IO;

    protected $localConfigFilePath;

    protected $configFilePath;

    protected $settings = [];

    protected $config = [];

    protected $localConfig = [];

    protected $configDefinition = [];

    protected $localConfigDefinition = [];

    protected $force = false;


    public function __construct() {
        $this->localConfigFilePath = $this->getUserHome() .'/.robot_config';
        $this->configFilePath = getcwd() . '/my.config';
    }

    protected function getUserHome()
    {
        $home = getenv('HOME');
        if (!empty($home)) {
            // home should never end with a trailing slash.
            $home = rtrim($home, '/');
        } elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
            // home on windows
            $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
            // If HOMEPATH is a root directory the path can end with a slash. Make sure
            // that doesn't happen.
            $home = rtrim($home, '\\/');
        }
        return empty($home) ? NULL : $home;
    }

    public function initConfig(array $config)
    {
        $this->configDefinition = $config;
    }

    public function initSettings(array $config)
    {
        $this->settings = $config;
    }

    public function initLocal(array $config)
    {
        $this->localConfigDefinition = $config;
    }

    public function setLocalFilePath($filePath)
    {
        $this->localConfigFilePath = $filePath;
    }

    public function setConfigFilePath($filePath)
    {
        $this->configFilePath = $filePath;
    }

    public function force()
    {
        $this->force = true;
    }


    public function run()
    {
        $this->loadLocal();
        $this->loadConfig();
        return Result::success($this, 'Config loaded', array_merge($this->localConfig, $this->config, $this->settings));
    }


    protected function loadConfig()
    {
        $this->config = [];
        if (file_exists($this->configFilePath)) {
            $this->config  = include $this->configFilePath;
        }

        if ($this->force || !$this->checkConfig($this->configDefinition, $this->config)) {
            $this->config  = $this->askForConfig($this->configDefinition, $this->config);
            $this->saveConfig($this->config, $this->configFilePath);
        }
    }

    protected function loadLocal()
    {
        $this->localConfig = [];
        if (file_exists($this->localConfigFilePath)) {
            $this->localConfig  = include $this->localConfigFilePath;
        }
        if ($this->force || !$this->checkConfig($this->localConfigDefinition, $this->localConfig)) {
            $this->localConfig  = array_merge($this->localConfig, $this->askForConfig($this->localConfigDefinition, $this->localConfig));
            $this->saveConfig($this->localConfig, $this->localConfigFilePath);
        }
    }

    /**
     * Configuration check
     */
    private function checkConfig(array $definition, array $config)
    {
        // Load config sample
        $return = false;
        foreach ($definition as $key => $value) {
            if (!isset($config[$key])) {
                return false;
            }
        }
        return true;
    }

    private function askForConfig(array $definition, array $config)
    {
        $inProgress = $this->hideTaskProgress();
        $this->setOutput(Robo::service('output'));
        foreach ($definition as $key => $option) {
            if (isset($config[$key])) {
                $option['default'] = $config[$key];
            }
            if (isset($option['choices'])) {
                $value = null;
                while (!in_array($value, $option['choices'])) {
                    if (isset($option['default'])) {
                        $value = $this->askDefault($option['question'] . ' (' . implode(',',$option['choices']) . ')', $option['default']);
                    } else {
                        $value = $this->ask($option['question'] . ' (' . implode(',',$option['choices']) . ')');
                    }
                }
            } else {
                if (isset($option['default'])) {
                    $value = $this->askDefault($option['question'], $option['default']);
                } else {
                    $value = $this->ask($option['question']);
                }
            }
            $config[$key] = $value;
        }
        if ($inProgress) {
            $this->showTaskProgress($inProgress);
        }
        return $config;
    }

    private function saveConfig(array $config, $filePath)
    {
        $res = file_put_contents($filePath, '<?php return ' . var_export($config, true) . ';');
        if ($res === false) {
            throw new TaskException($this, "Cannot write in file '" . $filePath  ."'");
        }
    }
}
