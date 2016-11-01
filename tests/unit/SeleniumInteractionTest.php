<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Exception\WebDriverCurlException;
use Sepehr\PHPUnitSelenium\Exception\SeleniumNotRunning;

class SeleniumInteractionTest extends UnitSeleniumTestCase
{

    /** @test */
    public function throwsAnExceptionIfSeleniumIsNotRunning()
    {
        $this
            ->inject(RemoteWebDriver::class)
            ->shouldReceive('create')
            ->andThrow(WebDriverCurlException::class);

        $this->expectException(SeleniumNotRunning::class);

        $this->forceCreateSession();
    }
}
