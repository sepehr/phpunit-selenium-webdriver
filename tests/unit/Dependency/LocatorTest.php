<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Dependency;

use Sepehr\PHPUnitSelenium\Util\Locator;
use Sepehr\PHPUnitSelenium\Tests\Unit\UnitSeleniumTestCase;

class LocatorTest extends UnitSeleniumTestCase
{

    /** @test */
    public function createsAnInstance()
    {
        $this->assertInstanceOf(Locator::class, $this->locator());
    }

    /** @test */
    public function doesNotCreateANewInstanceIfAlreadyExists()
    {
        $this->setLocator($locator = Locator::create());

        $this->assertSame($locator, $this->locator());
    }
}
