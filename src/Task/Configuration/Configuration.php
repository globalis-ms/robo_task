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
 *     'config_key' => [
 *        'question' => 'question ?',
 *        'default' => 'ddd',
 *        'choices' => ['choice']
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
 *  ->localFilePath($localFilePath)
 *  ->configFilePath($configFilePath)
 *  ->force()
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


    public function __construct()
    {
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
        return empty($home) ? null : $home;
    }

    /**
     * Init config variables
     *
     * @param  array  $config
     * @return $this
     */
    public function initConfig(array $config)
    {
        $this->configDefinition = $config;
        return $this;
    }

    /**
     * Init settings variables
     *
     * @param  array  $config [description]
     * @return $this
     */
    public function initSettings(array $config)
    {
        $this->settings = $config;
        return $this;
    }

    /**
     * Init settings variables
     *
     * @param  array  $config
     * @return $this
     */
    public function initLocal(array $config)
    {
        $this->localConfigDefinition = $config;
        return $this;
    }

    /**
     * Set local file path, Default User Home
     *
     * @param string $filePath
     * @return $this
     */
    public function localFilePath($filePath)
    {
        $this->localConfigFilePath = $filePath;
        return $this;
    }

    /**
     * Set config file path, default Project Dir / .my_config
     *
     * @param string $filePath
     * @return $this
     */
    public function configFilePath($filePath)
    {
        $this->configFilePath = $filePath;
        return $this;
    }

    /**
     * Force question
     *
     * @return $this
     */
    public function force()
    {
        $this->force = true;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
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
                if (isset($option['default'])) {
                    $value = $this->io()->choice($option['question'], $option['choices'], $option['default']);
                } else {
                    $value = $this->io()->choice($option['question'], $option['choices']);
                }
            } else {
                if (isset($option['default'])) {
                    $value = $this->io()->ask($option['question'], $option['default']);
                } else {
                    $value = $this->io()->ask($option['question']);
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
