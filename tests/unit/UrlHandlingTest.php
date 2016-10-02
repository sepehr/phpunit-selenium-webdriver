<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

class UrlHandlingTest extends UnitSeleniumTestCase
{

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
