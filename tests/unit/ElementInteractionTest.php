<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Mockery;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Sepehr\PHPUnitSelenium\Util\Locator;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ElementInteractionTest extends UnitSeleniumTestCase
{

    /** @test */
    public function typesIntoAnElementIdentifiedByALocator()
    {
        $locator = 'someLocator';
        $typed   = 'Life is a magnificent illusion';

        $elementMock = Mockery::mock('overload:' . RemoteWebElement::class)
            ->shouldReceive('sendKeys')
            ->once()
            ->with($typed)
            ->andReturn(Mockery::self())
            ->mock();

        $this->injectMockedWebDriver(
            $this->webDriverMock
                ->shouldReceive('findElements')
                ->with(WebDriverBy::class)
                ->andReturn([$elementMock])
                ->shouldReceive('getCurrentURL')
                ->zeroOrMoreTimes()
                ->getMock()
        );

        Mockery::mock('alias:' . WebDriverBy::class)
            ->shouldReceive('xpath')
            ->once()
            ->with($locator)
            ->andReturn(Mockery::self())
            ->mock();

        Mockery::mock('alias:' . Locator::class)
            ->shouldReceive('isXpath')
            ->once()
            ->with($locator)
            ->andReturn(true);

        $this->type($typed, $locator);
    }
}
