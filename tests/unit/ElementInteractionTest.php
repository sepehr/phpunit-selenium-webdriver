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
        $elementMock = Mockery::mock('overload:' . RemoteWebElement::class)
            ->shouldReceive($action)
            ->once()
            ->with($apiArgs[0])
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
            ->with($apiArgs[1])
            ->andReturn(Mockery::self())
            ->mock();

        Mockery::mock('alias:' . Locator::class)
            ->shouldReceive('isXpath')
            ->once()
            ->with($apiArgs[1])
            ->andReturn(true);

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
