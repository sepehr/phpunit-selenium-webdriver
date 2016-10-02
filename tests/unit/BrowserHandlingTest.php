<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Sepehr\PHPUnitSelenium\Exceptions\NoSuchBrowser;

class BrowserHandlingTest extends UnitSeleniumTestCase
{

    /** @test */
    public function throwsAnExceptionWhenSettingAnInvalidBrowser()
    {
        $this->expectException(NoSuchBrowser::class);

        $this->setBrowser('invalidBrowser');
    }

    /** @test */
    public function returnsWebDriverCurrentUrl()
    {
        $this->injectMockedWebDriver(
            $this->webDriverMock
                 ->shouldReceive('getCurrentURL')
                 ->once()
                 ->andReturn($expected = 'https://github.com/')
                 ->getMock()
        );

        $this->assertSame($expected, $this->webDriverUrl());
    }
}
