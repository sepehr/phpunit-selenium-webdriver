<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Mockery;
use Sepehr\PHPUnitSelenium\Util\Locator;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Sepehr\PHPUnitSelenium\WebDriver\WebDriverBy;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ElementInteractionTest extends UnitSeleniumTestCase
{

    /**
     * @test
     *
     * Tests element interaction methods, e.g. type(), click(), hit(), etc.
     *
     * @param string $api API method name to call.
     * @param array $apiArgs Arguments array to pass to the API method.
     * @param string $action Method name to actually call against the element.
     *
     * @dataProvider elementActionProvider
     */
    public function dispatchesActionsOnElementsFoundByALocator($api, $apiArgs, $action)
    {
        $elMock = $this->mock(RemoteWebElement::class)
            ->shouldReceive($action)
            ->once()
            ->with($apiArgs[0])
            ->andReturn(Mockery::self())
            ->mock();

        $this->inject(RemoteWebDriver::class)
            ->shouldReceive('findElements')
            ->with(WebDriverBy::class)
            ->andReturn([$elMock])
            ->shouldReceive('getCurrentURL')
            ->zeroOrMoreTimes();

        $this->inject(WebDriverBy::class)
            ->shouldReceive('xpath')
            ->once()
            ->with($apiArgs[1])
            ->andReturn(Mockery::self());

        $this->inject(Locator::class)
            ->shouldReceive('isLocator', 'isXpath')
            ->andReturn(true, false);

        $this->$api(...$apiArgs);
    }

    /**
     * Data provider for element actions.
     *
     * @return array
     */
    public static function elementActionProvider()
    {
        return [
            // Pattern: $api, $apiArgs, $action
            ['type', ['Life is a magnificent illusion', 'someLocator'], 'sendKeys']
        ];
    }
}
