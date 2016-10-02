<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Sepehr\PHPUnitSelenium\Exceptions\InvalidArgument;

class BrowserTest extends UnitSeleniumTestCase
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
