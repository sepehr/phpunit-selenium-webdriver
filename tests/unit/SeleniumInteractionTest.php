<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Exception\WebDriverCurlException;
use Sepehr\PHPUnitSelenium\Exception\SeleniumNotRunning;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SeleniumInteractionTest extends UnitSeleniumTestCase
{

    /** @test */
    public function throwsAnExceptionIfSeleniumIsNotRunning()
    {
        $this
            ->mock('alias:' . RemoteWebDriver::class)
            ->shouldReceive('create')
            ->andThrow(WebDriverCurlException::class);

        $this->expectException(SeleniumNotRunning::class);

        $this->createSession();
    }
}
