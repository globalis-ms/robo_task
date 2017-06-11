<?php
namespace Globalis\Robo\Tests;

use Symfony\Component\Process\Process;

class Util
{

    public static function runProcess($cmd, $cwd = null)
    {
        $process = new Process($cmd);
        $process->setWorkingDirectory($cwd);
        $process->run();
        return $process;
    }

    public static function rmDir($path)
    {
        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), ['.', '..']);
            foreach ($files as $file) {
                static::rmDir(realpath($path) . '/' . $file);
            }
            return rmdir($path);
        } elseif (is_file($path) === true) {
            return unlink($path);
        }
        return false;
    }

    public static function getProtectedProperty($object, $property)
    {
        $reflection = new \ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        return $reflection_property->getValue($object);
    }

    public static function invokeMethod($object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
