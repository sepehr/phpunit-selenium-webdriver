<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Dependency;

use Mockery;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Sepehr\PHPUnitSelenium\Exception\InvalidArgument;
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
class DesiredCapabilitiesTest extends UnitSeleniumTestCase
{

    /** @test */
    public function createsADesiredCapabilitiesInstanceForAValidBrowserThroughShortcutMethods()
    {
        $this->mock('alias:' . DesiredCapabilities::class)
            // e.g. DesiredCapabilities::firefox()
            ->shouldReceive($browser = 'firefox')
            ->once()
            ->withNoArgs()
            ->andReturn(Mockery::self())
            ->mock();

        $this->setBrowser($browser);

        $this->assertInstanceOf(DesiredCapabilities::class, $this->desiredCapabilities());
    }

    /** @test */
    public function createsADesiredCapabilitiesInstanceForAValidBrowserThroughInstantiating()
    {
        $this->setBrowser(WebDriverBrowserType::KONQUEROR);

        // e.g. new DesiredCapabilities([...])
        $this->mock('overload:' . DesiredCapabilities::class);

        $this->assertInstanceOf(DesiredCapabilities::class, $this->desiredCapabilities());
    }

    /** @test */
    public function throwsAnExceptionIfCreatingAnInstanceWithAnInvalidBrowser()
    {
        $this->browser = 'invalidBrowser';

        $this->expectException(InvalidArgument::class);

        $this->desiredCapabilities();
    }

    /** @test */
    public function throwsAnExceptionIfCreatingAnInstanceWithAnInvalidPlatform()
    {
        $this->platform = 'invalidPlatform';
        $this->setBrowser(WebDriverBrowserType::KONQUEROR);

        $this->expectException(InvalidArgument::class);

        $this->desiredCapabilities();
    }

    /** @test */
    public function doesNotCreateANewInstanceIfAlreadyExists()
    {
        $this->inject(
            $this->mock(DesiredCapabilities::class)
                ->shouldNotReceive($this->browser)
        );

        $this->assertInstanceOf(DesiredCapabilities::class, $this->desiredCapabilities());
    }
}
