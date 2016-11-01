<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Dependency;

use Mockery;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Sepehr\PHPUnitSelenium\Tests\Unit\UnitSeleniumTestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class RemoteWebDriverTest extends UnitSeleniumTestCase
{

    /** @test */
    public function doesNotCreateAnInstanceOnSetUp()
    {
        $this->assertFalse($this->webDriverLoaded());
    }

    /**
     * @test
     */
    public function createsAnInstanceWithProperArgsUponCreatingNewSessions()
    {
        $this
            ->spy('alias:' . RemoteWebDriver::class)
            ->shouldReceive('create')
            ->with(
                $this->host,
                DesiredCapabilities::class,
                $this->connectionTimeout,
                $this->requestTimeout,
                $this->httpProxy,
                $this->httpProxyPort
            )
            ->once()
            ->andReturn(Mockery::self());

        $this->createSession();

        $this->assertTrue($this->webDriverLoaded());
    }

    /** @test */
    public function doesNotCreateANewInstanceIfAlreadyExists()
    {
        $webDriver = $this->injectSpy(RemoteWebDriver::class);

        $this->createSession();

        $webDriver->shouldNotHaveReceived('create');
    }

    /** @test */
    public function canBeForcedToCreateANewInstanceEvenThoughOneAlreadyExists()
    {
        $this
            ->injectSpy('alias:' . RemoteWebDriver::class)
            ->shouldReceive('create')
            ->andReturn(Mockery::self());

        $this->forceCreateSession();

        $this->assertTrue($this->webDriverLoaded());
    }

    /** @test */
    public function unloadsAndQuitsWebDriverWhenDestroyingSession()
    {
        $webDriver = $this->injectSpy(RemoteWebDriver::class);

        $this->destroySession();

        $webDriver->shouldHaveReceived('quit');
        $this->assertFalse($this->webDriverLoaded());
    }

    /** @test */
    public function returnsItsInstanceOfWebDriver()
    {
        $webDriver = $this->inject(RemoteWebDriver::class);

        $this->assertSame($webDriver, $this->webDriver());
    }
}
