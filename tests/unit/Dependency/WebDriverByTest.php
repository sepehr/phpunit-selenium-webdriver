<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Dependency;

use Sepehr\PHPUnitSelenium\WebDriver\WebDriverBy;
use Sepehr\PHPUnitSelenium\Tests\Unit\UnitSeleniumTestCase;

class WebDriverByTest extends UnitSeleniumTestCase
{

    /** @test */
    public function createsAnInstanceWithoutAnySpecificMechanism()
    {
        $this->assertInstanceOf(WebDriverBy::class, $this->webDriverBy());
    }

    /** @test */
    public function createsAnInstanceWithAMechanismAndAValue()
    {
        $webDriverBy = $this->webDriverBy($mechanism = 'id', $value = 'someId');

        $this->assertInstanceOf(WebDriverBy::class, $webDriverBy);
        $this->assertSame($value, $webDriverBy->getValue());
        $this->assertSame($mechanism, $webDriverBy->getMechanism());
    }

    /** @test */
    public function doesNotCreateANewInstanceIfAlreadyExists()
    {
        $this->setWebDriverBy($webDriverBy = $this->webDriverBy());

        $this->assertSame($webDriverBy, $this->webDriverBy());
    }
}
