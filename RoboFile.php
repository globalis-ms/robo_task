<?php
use Symfony\Component\Finder\Finder;
use Robo\Result;
use Robo\Collection\CollectionBuilder;

class RoboFile extends \Robo\Tasks
{
    /**
     * Mess detector.
     *
     * Run the PHP Mess Detector on a file or directory.
     *
     * @param string $file A file or directory to analyze.
     * @param string $type A report format.
     */
    public function mess($file = 'src/', $type = 'text')
    {
        $this->taskExec("./vendor/bin/phpmd")
            ->args([$file, $type, 'design,unusedcode'])
            ->run();
    }

    /**
     * Code sniffer.
     *
     * Run the PHP Codesniffer on a file or directory.
     *
     * @option $autofix Whether to run the automatic fixer or not.
     * @option $strict Show warnings as well as errors.
     *    Default is to show only errors.
     */
    public function sniff(
        $options = [
            'autofix' => false,
            'strict' => false,
        ]
    ) {
        $strict = $options['strict'] ? '' : '-n';
        $result = $this->taskExec("./vendor/bin/phpcs {$strict}")->run();
        if (!$result->wasSuccessful()) {
            if (!$options['autofix']) {
                $options['autofix'] = $this->confirm('Would you like to run phpcbf to fix the reported errors?');
            }
            if ($options['autofix']) {
                $result = $this->taskExec("./vendor/bin/phpcbf")->run();
            }
        }
        return $result;
    }

    /**
     * Generate documentation files.
     */
    public function docs()
    {
        $collection = $this->collectionBuilder();
        $collection->progressMessage('Generate documentation from source code.');
        $files = Finder::create()->files()->name('*.php')->in('src/Task');
        $docs = [];
        foreach ($files as $file) {
            if ($file->getFileName() == 'loadTasks.php') {
                continue;
            }
            if ($file->getFileName() == 'loadShortcuts.php') {
                continue;
            }
            $ns = $file->getRelativePath();
            if (!$ns || $ns === 'Core') {
                continue;
            }
            $ns = str_replace('/', '\\', $ns);
            $class = basename(substr($file, 0, -4));
            class_exists($class = "Globalis\\Robo\\Task\\$ns\\$class");
            $ns =  explode('\\', $ns);
            $ns = reset($ns);
            $docs[$ns][] = $class;
        }
        foreach ($docs as $ns => $tasks) {
            $tmp = explode('\\', $ns);
            $taskGenerator = $collection->taskGenDoc("docs/$ns.md");
            $taskGenerator->filterClasses(function (\ReflectionClass $r) {
                return !($r->isAbstract() || $r->isTrait()) && $r->implementsInterface('Robo\Contract\TaskInterface');
            })->prepend("# $ns Tasks");
            sort($tasks);
            foreach ($tasks as $class) {
                $taskGenerator->docClass($class);
            }
            $taskGenerator->filterMethods(
                function (\ReflectionMethod $m) {
                    if ($m->isConstructor() || $m->isDestructor() || $m->isStatic()) {
                        return false;
                    }
                    $undocumentedMethods =
                    [
                        '',
                        'run',
                        '__call',
                        'inflect',
                        'injectDependencies',
                        'getCommand',
                        'getPrinted',
                        'getConfig',
                        'setConfig',
                        'logger',
                        'setLogger',
                        'setProgressIndicator',
                        'progressIndicatorSteps',
                        'setBuilder',
                        'getBuilder',
                        'setInput',
                        'setOutput',
                        'collectionBuilder',
                        'setVerbosityThreshold',
                        'verbosityThreshold',
                        'setOutputAdapter',
                        'outputAdapter',
                        'hasOutputAdapter',
                        'verbosityMeetsThreshold',
                        'writeMessage',
                    ];
                    return !in_array($m->name, $undocumentedMethods) && $m->isPublic(); // methods are not documented
                }
            )->processClassSignature(
                function ($c) use ($ns) {
                    $name = str_replace('Globalis\\Robo\\Task\\' . $ns .'\\', '', $c->name);
                    $name = join(explode('\\', $name));
                    return "## " . preg_replace('~Task$~', '', $name) . "\n";
                }
            )->processClassDocBlock(
                function (\ReflectionClass $c, $doc) {
                    $doc = preg_replace('~@method .*?(.*?)\)~', '* `$1)` ', $doc);
                    $doc = str_replace('\\'.$c->name, '', $doc);
                    return $doc;
                }
            )->processMethodSignature(
                function (\ReflectionMethod $m, $text) {
                    return str_replace('#### *public* ', '* `', $text) . '`';
                }
            )->processMethodDocBlock(
                function (\ReflectionMethod $m, $text) {
                    return $text ? ' ' . trim(strtok($text, "\n"), "\n") : '';
                }
            );
        }
        $collection->progressMessage('Documentation generation complete.');
        return $collection->run();
    }

    /**
     * Build the Robo phar executable.
     */
    public function pharBuild($composerPath = null)
    {
        // Create a collection builder to hold the temporary
        // directory until the pack phar task runs.
        $collection = $this->collectionBuilder();
        $workDir = $collection->tmpDir();
        $roboBuildDir = "$workDir/robo";
        // Before we run `composer install`, we will remove the dev
        // dependencies that we only use in the unit tests.  Any dev dependency
        // that is in the 'suggested' section is used by a core task;
        // we will include all of those in the phar.
        $devProjectsToRemove = $this->devDependenciesToRemoveFromPhar();
        $depProjectsToAdd = $this->dependenciesToAddFromPhar();
        // We need to create our work dir and run `composer install`
        // before we prepare the pack phar task, so create a separate
        // collection builder to do this step in.
        $prepTasks = $this->collectionBuilder();
        $preparationResult = $prepTasks
            ->taskFilesystemStack()
                ->mkdir($workDir)
            ->taskRsync()
                ->fromPath(__DIR__ . '/')
                ->toPath($roboBuildDir)
                ->recursive()
                ->exclude(
                    [
                        'vendor/',
                        '.idea/',
                        'docs/',
                        'composer.phar',
                        'composer.lock',
                        'robo.phar',
                    ]
                )
            ->taskComposerRemove($composerPath)
                ->dir($roboBuildDir)
                ->dev()
                ->noUpdate()
                ->printOutput(false)
                ->args($devProjectsToRemove)
            ->taskExec($composerPath . ' require ')
                ->dir($roboBuildDir)
                ->option('--no-update')
                ->printOutput(false)
                ->args($depProjectsToAdd)
            ->taskComposerInstall($composerPath)
                ->dir($roboBuildDir)
                ->printOutput(false)
                ->run();
        // Exit if the preparation step failed
        if (!$preparationResult->wasSuccessful()) {
            return $preparationResult;
        }
        // Decide which files we're going to pack
        $files = Finder::create()->ignoreVCS(true)
            ->files()
            ->name('*.php')
            ->name('*.json')
            ->name('LICENSE')
            ->name('*.exe') // for 1symfony/console/Resources/bin/hiddeninput.exe
            ->name('GeneratedWrapper.tmpl')
            ->path('src')
            ->path('vendor')
            ->notPath('/vendor\/.*\/[Tt]est[s?]\//')
            ->in($roboBuildDir);
        // Build the phar
        return $collection
            ->taskPackPhar('robo.phar')
                ->addFiles($files)
                ->executable('vendor/bin/robo')
            ->taskFilesystemStack()
                ->chmod('robo.phar', 0777)
            ->run();
    }
    /**
     * The phar:build command removes the project requirements from the
     * 'require-dev' section that are not in the 'suggest' section.
     *
     * @return array
     */
    protected function devDependenciesToRemoveFromPhar()
    {
        $composerInfo = (array) json_decode(file_get_contents(__DIR__ . '/composer.json'));
        $devDependencies = array_keys((array)$composerInfo['require-dev']);
        $suggestedProjects = [];
        if (isset($composerInfo['suggest'])) {
            $suggestedProjects = array_keys((array)$composerInfo['suggest']);
        }
        return array_diff($devDependencies, $suggestedProjects);
    }

    /**
     * The phar:build command removes the project requirements from the
     * 'require-dev' section that are not in the 'suggest' section.
     *
     * @return array
     */
    protected function dependenciesToAddFromPhar()
    {
        $composerInfo = (array) json_decode(file_get_contents(__DIR__ . '/vendor/consolidation/robo/composer.json'));
        $devDependencies = array_keys((array)$composerInfo['require-dev']);
        $suggestedProjects = [];
        if (isset($composerInfo['suggest'])) {
            $suggestedProjects = array_keys((array)$composerInfo['suggest']);
        }
        return array_intersect($devDependencies, $suggestedProjects);
    }
}
