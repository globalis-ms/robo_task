<?php
namespace Globalis\Robo\Tests\Task\Composer;

use Globalis\Robo\Tests\Util;

class InstallTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider options
     */
    public function testComposerOptions($function, $result, $args = [])
    {
        $command = new \Globalis\Robo\Task\Composer\Install();
        $command->{$function}(...$args);
        if (empty($args)) {
            $this->assertSame([$result => null], Util::getProtectedProperty($command, 'options'));
        } else {
            $this->assertSame([$result => join($args)], Util::getProtectedProperty($command, 'options'));
        }
    }

    public function options()
    {
        return [
            ['preferSource', '--prefer-source'],
            ['preferDist', '--prefer-dist'],
            ['dryRun', '--dry-run'],
            ['dev', '--dev'],
            ['noDev', '--no-dev'],
            ['optimizeAutoloader', '--optimize-autoloader'],
            ['noAutoloader', '--no-autoloader'],
            ['noScript', '--no-script'],
            ['noSuggest', '--no-suggest'],
            ['noProgress', '--no-progress'],
        ];
    }
}
