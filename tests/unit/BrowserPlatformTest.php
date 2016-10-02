<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Sepehr\PHPUnitSelenium\Exceptions\InvalidArgument;

class BrowserPlatformTest extends UnitSeleniumTestCase
{

    /**
     * @test
     *
     * @param string $browser
     *
     * @dataProvider browserNameProvider
     */
    public function acceptsValidBrowsers($browser)
    {
        $this->assertTrue($this->validateBrowser($browser));
    }

    /** @test */
    public function throwsAnExceptionWhenSettingAnInvalidBrowser()
    {
        $this->expectException(InvalidArgument::class);

        $this->setBrowser('invalidBrowser');
    }

    /**
     * @test
     *
     * @param string $platform
     *
     * @dataProvider platformNameProvider
     */
    public function acceptsValidPlatforms($platform)
    {
        $this->assertTrue($this->validatePlatform($platform));
    }

    /** @test */
    public function throwsAnExceptionWhenSettingAnInvalidPlatform()
    {
        $this->expectException(InvalidArgument::class);

        $this->setPlatform('invalidPlatform');
    }

    /**
     * Data provider for valid platform names.
     *
     * @return array
     */
    public static function platformNameProvider()
    {
        return [
            ['ANY'],
            ['ANDROID'],
            ['LINUX'],
            ['MAC'],
            ['UNIX'],
            ['VISTA'],
            ['WINDOWS'],
            ['XP'],
        ];
    }

    /**
     * Data provider for valid browser names.
     *
     * @return array
     */
    public static function browserNameProvider()
    {
        return [
            ['firefox'],
            ['firefox2'],
            ['firefox3'],
            ['firefoxproxy'],
            ['firefoxchrome'],
            ['googlechrome'],
            ['safari'],
            ['opera'],
            ['iexplore'],
            ['iexploreproxy'],
            ['safariproxy'],
            ['chrome'],
            ['konqueror'],
            ['mock'],
            ['iehta'],
            ['android'],
            ['htmlunit'],
            ['internet explorer'],
            ['iphone'],
            ['iPad'],
            ['phantomjs'],
        ];
    }
}
