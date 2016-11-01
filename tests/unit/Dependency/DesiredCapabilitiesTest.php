<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Dependency;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Sepehr\PHPUnitSelenium\Exception\InvalidArgument;
use Sepehr\PHPUnitSelenium\Tests\Unit\UnitSeleniumTestCase;

class DesiredCapabilitiesTest extends UnitSeleniumTestCase
{

    /** @test */
    public function createsAnInstanceForAValidBrowserThroughShortcutMethods()
    {
        $this->setBrowser($browser = 'firefox');

        $desiredCapabilities = $this->desiredCapabilities();

        $this->assertInstanceOf(DesiredCapabilities::class, $desiredCapabilities);
        $this->assertSame($browser, $desiredCapabilities->getBrowserName());
    }

    /** @test */
    public function createsAnInstanceForAValidBrowserThroughInstantiating()
    {
        $this->setBrowser($browser = WebDriverBrowserType::KONQUEROR);

        $desiredCapabilities = $this->desiredCapabilities();

        $this->assertInstanceOf(DesiredCapabilities::class, $desiredCapabilities);
        $this->assertSame($browser, $desiredCapabilities->getBrowserName());
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
        $this->setDesiredCapabilities(
            $desiredCapabilities = $this->desiredCapabilitiesInstance()
        );

        $this->assertSame($desiredCapabilities, $this->desiredCapabilities());
    }
}
