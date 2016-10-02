<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Facebook\WebDriver\Exception\WebDriverCurlException;
use Sepehr\PHPUnitSelenium\Exceptions\SeleniumNotRunning;

class SeleniumInteractionTest extends UnitSeleniumTestCase
{

    /** @test */
    public function throwsAnExceptionIfSeleniumIsNotRunning()
    {
        $this->webDriverMock
             ->shouldReceive('create')
             ->once()
             ->andThrow(WebDriverCurlException::class);

        $this->expectException(SeleniumNotRunning::class);

        $this->createSession();
    }
}
