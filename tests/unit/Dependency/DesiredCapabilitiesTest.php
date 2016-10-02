<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Dependency;

use Mockery;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Sepehr\PHPUnitSelenium\Tests\Unit\UnitSeleniumTestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class DesiredCapabilitiesTest extends UnitSeleniumTestCase
{

    /** @test */
    public function createsADesiredCapabilitiesInstanceForAValidBrowserThroughShortcutMethods()
    {
        Mockery::mock('alias:' . DesiredCapabilities::class)
               // e.g. DesiredCapabilities::firefox()
               ->shouldReceive($browser = 'firefox')
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
    public function createsADesiredCapabilitiesInstanceForAValidBrowserThroughInstantiating()
    {
        $this->setBrowser($browser = WebDriverBrowserType::KONQUEROR);

        // e.g. new DesiredCapabilities([...])
        Mockery::mock('overload:' . DesiredCapabilities::class);

        $this->assertInstanceOf(
            DesiredCapabilities::class,
            $this->createDesiredCapabilitiesInstance()
        );
    }
}
