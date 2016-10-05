<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Dependency;

use Mockery;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Sepehr\PHPUnitSelenium\Exception\InvalidArgument;
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
