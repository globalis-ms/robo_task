<?php

namespace Globalis\Robo\Tests\Core;

use Globalis\Robo\Core\SemanticVersion;

class SemanticVersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Globalis\Robo\Core\SemanticVersion::__construct
     * @covers \Globalis\Robo\Core\SemanticVersion::parse
     * @covers \Globalis\Robo\Core\SemanticVersion::__toString
     * @dataProvider versionProvider
     */
    public function testConstruct($version, $major, $minor, $patch, $special, $meta)
    {
        $semanticVersion = new SemanticVersion($version);
        $reflection = new \ReflectionClass($semanticVersion);
        $reflection_property = $reflection->getProperty('version');
        $reflection_property->setAccessible(true);
        $versionProperty = $reflection_property->getValue($semanticVersion);
        $this->assertSame($major, $versionProperty['major']);
        $this->assertSame($minor, $versionProperty['minor']);
        $this->assertSame($patch, $versionProperty['patch']);
        $this->assertSame($special, $versionProperty['special']);
        $this->assertSame($meta, $versionProperty['metadata']);
        $this->assertSame($version, $semanticVersion->__toString());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Bad semver.
     * @covers \Globalis\Robo\Core\SemanticVersion::__construct
     * @covers \Globalis\Robo\Core\SemanticVersion::parse
     */
    public function testParseThrowException()
    {
        new SemanticVersion('badversion');
    }

    public function testIncrement()
    {
        $semanticVersion = new SemanticVersion('0.0.0-test+meta');
        $semanticVersion->increment('major');
        $this->assertSame('1.0.0-test+meta', $semanticVersion->__toString());

        $semanticVersion->increment('minor');
        $this->assertSame('1.1.0-test+meta', $semanticVersion->__toString());

        $semanticVersion->increment('major');
        $this->assertSame('2.0.0-test+meta', $semanticVersion->__toString());

        $semanticVersion->increment('patch');
        $this->assertSame('2.0.1-test+meta', $semanticVersion->__toString());

        $semanticVersion->increment('minor');
        $this->assertSame('2.1.0-test+meta', $semanticVersion->__toString());

        $semanticVersion->increment('patch');
        $this->assertSame('2.1.1-test+meta', $semanticVersion->__toString());

        $semanticVersion->increment('major');
        $this->assertSame('3.0.0-test+meta', $semanticVersion->__toString());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Bad argument, only one of the following is allowed: major, minor, patch
     */
    public function testIncrementThrowException()
    {
        $semanticVersion = new SemanticVersion('1.0.0');
        $semanticVersion->increment('toto');
    }

    public function testPrelease()
    {
        $semanticVersion = new SemanticVersion('1.0.0');
        $semanticVersion->prerelease();
        $this->assertSame('1.0.0-RC.1', $semanticVersion->__toString());

        $semanticVersion->prerelease();
        $this->assertSame('1.0.0-RC.2', $semanticVersion->__toString());

        $semanticVersion->prerelease('CR');
        $this->assertSame('1.0.0-CR.1', $semanticVersion->__toString());
    }

    public function testMetadata()
    {
        $semanticVersion = new SemanticVersion('1.0.0');
        $semanticVersion->metadata('meta');
        $this->assertSame('1.0.0+meta', $semanticVersion->__toString());

        $semanticVersion->metadata(['meta', 3]);
        $this->assertSame('1.0.0+meta.3', $semanticVersion->__toString());
    }

    public function versionProvider()
    {
        return [
            ['1.1.1', '1', '1', '1', '', ''],
            ['1.1.1-test', '1', '1', '1', 'test', ''],
            ['1.1.1-test+meta', '1', '1', '1', 'test', 'meta'],
        ];
    }
}
