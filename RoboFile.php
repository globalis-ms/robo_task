<?php
use Symfony\Component\Finder\Finder;
use Robo\Result;
use Robo\Collection\CollectionBuilder;
class RoboFile extends \Robo\Tasks
{
    /**
     * Generate documentation files.
     */
    public function docs()
    {
        $collection = $this->collectionBuilder();
        $collection->progressMessage('Generate documentation from source code.');
        $files = Finder::create()->files()->name('*.php')->in('src');
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
                        'collectionBuilder',
                    ];
                    return !in_array($m->name, $undocumentedMethods) && $m->isPublic(); // methods are not documented
                }
            )->processClassSignature(
                function ($c) use ($ns){
                    $name = str_replace('Globalis\\Robo\\Task\\' . $ns .'\\', '' ,$c->name);
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
}
