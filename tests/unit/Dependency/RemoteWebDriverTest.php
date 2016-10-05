<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Dependency;

use Mockery;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Sepehr\PHPUnitSelenium\Tests\Unit\UnitSeleniumTestCase;

/**
 * Here we're testing the creation of a hard dependency. Even though we
 * could easily inject a mocked copy of the dependency class into the SUT,
 * we went the hard way and used aliased/overloaded mocks in few test methods,
 * to actually test the creation of dependency class, when no instance is injected.
 *
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
    public function createsAnInstanceUponCreatingNewSessions()
    {
        $this->inject(DesiredCapabilities::class);

        $this->mock('alias:' . RemoteWebDriver::class)
            ->shouldReceive('create')
            ->once()
            ->with(
                $this->host,
                DesiredCapabilities::class,
                $this->connectionTimeout,
                $this->requestTimeout,
                $this->httpProxy,
                $this->httpProxyPort
            )
            ->andReturn(Mockery::self())
            // It's only added when getting injected to SUT, so:
            ->shouldReceive('quit');

        $this->createSession();

        $this->assertTrue($this->webDriverLoaded());
    }

    /** @test */
    public function doesNotCreateANewInstanceIfAlreadyExists()
    {
        $this->inject(RemoteWebDriver::class)
            ->shouldNotReceive('create');

        $this->createSession();

        $this->assertTrue($this->webDriverLoaded());
    }

    /** @test */
    public function canBeForcedToCreateANewInstanceEvenThoughOneAlreadyExists()
    {
        $this->inject('alias:' . RemoteWebDriver::class)
            ->shouldReceive('create')
            ->once()
            ->withAnyArgs()
            ->andReturn(Mockery::self());

        $this->forceCreateSession();

        $this->assertTrue($this->webDriverLoaded());
    }

    /** @test */
    public function unloadsWebDriverWhenDestroyingSession()
    {
        $this->inject(RemoteWebDriver::class);

        $this->assertTrue($this->webDriverLoaded());

        $this->destroySession();

        $this->assertFalse($this->webDriverLoaded());
    }

    /** @test */
    public function returnsItsInstanceOfWebDriver()
    {
        $this->inject(RemoteWebDriver::class);

        $this->assertInstanceOf(RemoteWebDriver::class, $this->webDriver());
    }
}
