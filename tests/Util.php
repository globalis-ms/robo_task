<?php
namespace Globalis\Robo\Tests;

use Symfony\Component\Process\Process;

class Util
{

    public static function runProcess($cmd, $cwd = null)
    {
        $process = static::runProcessWithoutException($cmd, $cwd);
        if (!$process->isSuccessful()) {
            $error = sprintf(
                'The command "%s" failed.'."\n\nExit Code: %s(%s)\n\nWorking directory: %s",
                $process->getCommandLine(),
                $process->getExitCode(),
                $process->getExitCodeText(),
                $process->getWorkingDirectory()
            );
            if (!$process->isOutputDisabled()) {
                if ($process->getOutput()) {
                    $error .= sprintf("\n\nOutput:\n================\n%s", $process->getOutput());
                }

                if ($process->getErrorOutput()) {
                    $error .= sprintf("\n\nError Output:\n================\n%s", $process->getErrorOutput());
                }
            }
            throw new \Exception($error);
        }
        return $process;
    }

    public static function runProcessWithoutException($cmd, $cwd = null)
    {
        $process = new Process($cmd);
        $cwd = $cwd ?: getcwd();
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

    public static function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
