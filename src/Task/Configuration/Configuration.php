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
 *        'choices' => ['choice'],
 *     ]
 *  ])
 *  ->initSettings([
 *       'config_key' => 'value'
 *   ]),
 *  ->initLocal([
 *      'config_key' => [
 *          'question' => 'question ?',
 *          'empty' => true,
 *      ],
 *      'config_key_2' => [
 *          'question' => 'question ?',
 *          'formatter' => function ($value) {
 *              $formatValue = trim($value);
 *              return $formatValue;
 *          },
 *      ]
 *  ]),
 *  ->localFilePath($localFilePath)
 *  ->configFilePath($configFilePath)
 *  ->force()
 *  ->emptyPattern('empty')
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

    protected $emptyPattern = 'empty';


    public function __construct()
    {
        $this->localConfigFilePath = $this->getUserHome() .'/.robo_config';
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
     * @param  bool $bool
     * @return $this
     */
    public function force($bool = true)
    {
        $this->force = $bool;
        return $this;
    }

    /**
     * Empty pattern
     *
     * @param string $emptyPattern
     * @return $this
     */
    public function emptyPattern($emptyPattern)
    {
        $this->emptyPattern = $emptyPattern;
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
            if (isset($option['empty']) && $option['empty'] === true) {
                if (isset($option['default']) && $option['default'] === '') {
                    $option['default'] = $this->emptyPattern;
                }
                $option['question'] .= ', type "' . $this->emptyPattern . '" to set an empty value';
                $option['empty'] = function ($string) {
                    if ($string === null) {
                        return $this->emptyPattern;
                    }
                    return $string;
                };
            } else {
                $option['empty'] = null;
            }
            if (isset($option['choices'])) {
                if (isset($option['default'])) {
                    $value = $this->io()->choice($option['question'], $option['choices'], $option['default']);
                } else {
                    $value = $this->io()->choice($option['question'], $option['choices']);
                }
            } else {
                if (isset($option['default'])) {
                    $value = $this->io()->ask($option['question'], $option['default'], $option['empty']);
                } else {
                    $value = $this->io()->ask($option['question'], null, $option['empty']);
                }
            }

            if ($option['empty'] && $value === $this->emptyPattern) {
                $value = '';
            }

            if (isset($option['formatter'])) {
                $value = call_user_func($option['formatter'], $value);
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
        if (
            !is_writable($filePath)
            &&
            (!file_exists($filePath) && is_writable(dirname($filePath)) === false)
        ) {
            throw new TaskException($this, "Cannot write in file '" . $filePath  ."'");
        }
        $res = file_put_contents($filePath, '<?php return ' . var_export($config, true) . ';');
    }
}
