<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Sepehr\PHPUnitSelenium\Exception\NoSuchElement;

class FormInteractionTest extends UnitSeleniumTestCase
{

    /** @test */
    public function fillsAFieldFoundByNameLocator()
    {
        $this
            ->injectSpy(RemoteWebDriver::class)
            ->shouldReceive('findElements')
            ->andReturn([$element = $this->spy(RemoteWebElement::class)]);

        $this->fillField('nameLocator', $text = 'someText...');

        $element
            ->shouldHaveReceived('sendKeys')
            ->with($text)
            ->once();
    }

    /** @test */
    public function throwsAnExceptionWhenFillingAFieldWithInvalidNameLocator()
    {
        $this
            ->injectSpy(RemoteWebDriver::class)
            ->shouldReceive('findElements')
            ->andReturn([]);

        $this->expectException(NoSuchElement::class);

        $this->fillField('invalidNameLocator', 'someText...');
    }
}
