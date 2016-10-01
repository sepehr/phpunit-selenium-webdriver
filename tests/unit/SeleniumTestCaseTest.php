<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Exception\WebDriverCurlException;

use Sepehr\PHPUnitSelenium\SeleniumTestCase;
use Sepehr\PHPUnitSelenium\Exceptions\NoSuchBrowser;
use Sepehr\PHPUnitSelenium\Exceptions\InvalidArgument;
use Sepehr\PHPUnitSelenium\Exceptions\SeleniumNotRunning;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SeleniumTestCaseTest extends SeleniumTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Holds a mocked RemoteWebDriver instance.
     *
     * @var RemoteWebDriver|Mockery\MockInterface
     */
    protected $webDriverMock;

    /**
     * Test setup.
     */
    public function setUp()
    {
        $this->webDriverMock = Mockery::mock('alias:' . RemoteWebDriver::class, function ($mock) {
            // This way we satisfy the expectation set by PHPUnit's @after annotation by default
            // See: SeleniumTestCase::tearDownWebDriver()
            return $mock->shouldReceive('quit');
        });

        parent::setUp();
    }

    /** @test */
    public function doesNotCreateAWebDriverOnSetUp()
    {
        $this->assertFalse($this->webDriverLoaded());
    }

    /** @test */
    public function createsAWebDriverInstanceUponCreatingNewSessions()
    {
        $this->webDriverMock
             ->shouldReceive('create')
             ->once()
             ->with(
                 $this->host,
                 // @TODO: Do not touch DesiredCapabilities class
                 DesiredCapabilities::class,
                 $this->connectionTimeout,
                 $this->requestTimeout,
                 $this->httpProxy,
                 $this->httpProxyPort
             )
             ->andReturn(Mockery::self())
             ->mock();

        $this->createSession();

        $this->assertTrue($this->webDriverLoaded());
    }

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

    /** @test */
    public function doesNotCreateANewWebDriverIfAlreadyExists()
    {
        $this->injectWebDriverMock();

        $this->webDriverMock
             ->shouldNotReceive('create');

        $this->createSession();

        $this->assertTrue($this->webDriverLoaded());
    }

    /** @test */
    public function canForceToCreateANewWebDriverEvenThoughItAlreadyExists()
    {
        $this->injectWebDriverMock();

        $this->webDriverMock
            ->shouldReceive('create')
            ->once()
            ->withAnyArgs()
            ->andReturn(Mockery::self())
            ->mock();

        $this->forceCreateSession();

        $this->assertTrue($this->webDriverLoaded());
    }

    /** @test */
    public function destroysWebDriverWhenDestroyingSession()
    {
        $this->injectWebDriverMock();

        $this->assertTrue($this->webDriverLoaded());

        // NOTE:
        // We do not need to set an expectation to receive a "quit()" call
        // on the driver, as it's already set by default in the setUp().
        $this->destroySession();

        $this->assertFalse($this->webDriverLoaded());
    }

    /** @test */
    public function throwsAnExceptionWhenSettingAnInvalidBrowser()
    {
        $this->expectException(NoSuchBrowser::class);

        $this->setBrowser('invalidBrowser');
    }

    /** @test */
    public function createsADesiredCapabilitiesInstanceForAValidBrowser()
    {
        $mock = Mockery::mock('alias:' . DesiredCapabilities::class);

        $mock->shouldReceive($browser = 'firefox')
             ->once()
             ->withNoArgs()
             ->andReturn(Mockery::self())
             ->mock();

        $this->setBrowser($browser);

        $this->assertInstanceOf(
            DesiredCapabilities::class,
            $this->createDesiredCapabilitiesInstance()
        );
    }

    /** @test */
    public function returnsItsInstanceOfWebDriver()
    {
        $this->injectWebDriverMock();

        $this->assertInstanceOf(RemoteWebDriver::class, $this->webDriver());
    }

    /** @test */
    public function createsAnInstanceOfWebDriverBy()
    {
        $mock = Mockery::mock('alias:' . WebDriverBy::class);

        $mock->shouldReceive($mechanism = 'id')
             ->once()
             ->with($value = 'someElementId')
             ->andReturn(Mockery::self())
             ->mock();

        $this->assertInstanceOf(
            WebDriverBy::class,
            $this->createWebDriverByInstance($mechanism, $value)
        );
    }

    /** @test */
    public function throwsAnExceptionWhenCreatingAnInstanceOfWebDriverByWithInvalidMechanism()
    {
        $mock = Mockery::mock('alias:' . WebDriverBy::class);

        $mock->shouldReceive($mechanism = 'invalidMechanism')
             ->once()
             ->with($value = 'someValue')
             ->andThrow(\Exception::class);

        $this->expectException(InvalidArgument::class);

        $this->createWebDriverByInstance($mechanism, $value);
    }

    /** @test */
    public function returnsPageTitle()
    {
        $this->injectWebDriverMock();

        $expected = 'Some sample page title...';

        $this->webDriverMock
             ->shouldReceive('getTitle')
             ->once()
             ->andReturn($expected);

        $this->assertSame($expected, $this->pageTitle());
    }

    /** @test */
    public function returnsPageSource()
    {
        $this->injectWebDriverMock();

        $expected = '<html><head><title>Sample page</title></head><body>Lorem ipsum...</body></html>';

        $this->webDriverMock
            ->shouldReceive('getPageSource')
            ->once()
            ->andReturn($expected);

        $this->assertSame($expected, $this->pageSource());
    }

    /** @test */
    public function returnsWebDriverCurrentUrl()
    {
        $this->injectWebDriverMock();

        $expected = 'https://github.com/';

        $this->webDriverMock
             ->shouldReceive('getCurrentURL')
             ->once()
             ->andReturn($expected);

        $this->assertSame($expected, $this->webDriverUrl());
    }

    /**
     * Injects a mocked RemoteWebDriver into the SeleniumTestCase.
     *
     * @param null|RemoteWebDriver $mockedWebDriver
     *
     * @return void
     */
    private function injectWebDriverMock($mockedWebDriver = null)
    {
        $this->setWebDriver($mockedWebDriver ? $mockedWebDriver : $this->webDriverMock);
    }
}
