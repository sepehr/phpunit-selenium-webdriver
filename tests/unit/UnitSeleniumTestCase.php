<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Mockery;
use phpmock\mockery\PHPMockery;
use Sepehr\PHPUnitSelenium\SeleniumTestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

abstract class UnitSeleniumTestCase extends SeleniumTestCase
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
        $this->injectMockedSleep();

        $this->webDriverMock = Mockery::mock('alias:' . RemoteWebDriver::class, function ($mock) {
            // This way we satisfy the expectation set by PHPUnit's @after annotation by default
            // See: SeleniumTestCase::tearDownWebDriver()
            return $mock->shouldReceive('quit');
        });

        parent::setUp();
    }

    /**
     * Injects a mocked RemoteWebDriver into the SeleniumTestCase.
     *
     * @param RemoteWebDriver|null $mockedWebDriver
     *
     * @return void
     */
    protected function injectMockedWebDriver($mockedWebDriver = null)
    {
        $this->setWebDriver($mockedWebDriver ? $mockedWebDriver : $this->webDriverMock);
    }

    /**
     * Injects a mocked sleep() under SUT's namespace.
     *
     * @return void
     */
    protected function injectMockedSleep()
    {
        PHPMockery::mock('Sepehr\PHPUnitSelenium', 'sleep')
            ->zeroOrMoreTimes()
            ->andReturn(0);
    }
}
