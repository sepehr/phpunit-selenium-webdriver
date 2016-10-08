<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Mockery;
use Facebook\WebDriver\WebDriverKeys;
use Sepehr\PHPUnitSelenium\Util\Locator;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Sepehr\PHPUnitSelenium\WebDriver\WebDriverBy;
use Sepehr\PHPUnitSelenium\Exception\InvalidArgument;

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
     * @param array $actionArgs Arguments array to pass to the action method.
     *
     * @dataProvider elementActionProvider
     */
    public function dispatchesActionsOnElementsFoundByALocator($api, $apiArgs, $action, $actionArgs = [])
    {
        $elementMock = $this->mock(RemoteWebElement::class)
            ->shouldReceive($action)
            ->once()
            ->with(...$actionArgs)
            ->andReturn(Mockery::self())
            ->mock();

        $this->inject(RemoteWebDriver::class)
            ->shouldReceive('findElements')
            ->with(WebDriverBy::class)
            ->andReturn([$elementMock])
            ->shouldReceive('getCurrentURL')
            ->zeroOrMoreTimes();

        $this->inject(WebDriverBy::class)
            ->shouldReceive('xpath')
            ->once()
            ->with(end($apiArgs))
            ->andReturn(Mockery::self());

        $this->inject(Locator::class)
            ->shouldReceive('isLocator', 'isXpath')
            ->andReturn(true, false);

        $this->$api(...$apiArgs);
    }

    /** @test */
    public function throwsAnExceptionWhenHittingAnInvalidKey()
    {
        $this->expectException(InvalidArgument::class);

        $this->hit('invalidKey', 'someLocator');
    }

    public function dispatchesActionsOnElementObjects()
    {
        $elementMock = $this->mock(RemoteWebElement::class)
            ->shouldReceive('sendKeys')
            ->once()
            ->with($text = 'sending some keys...')
            ->andReturn(Mockery::self())
            ->mock();

        $this->inject(Locator::class)
            ->shouldReceive('isLocator')
            ->andReturn(false);

        $this->type($text, $elementMock);
    }

    /**
     * Data provider for element actions.
     *
     * @return array
     */
    public static function elementActionProvider()
    {
        return [
            // Pattern: $api, $apiArgs, $action, $actionArgs
            ['type', ['magnificent illusion', 'someLocator'], 'sendKeys', ['magnificent illusion']],
            ['fill', ['illbedancingdeafdumbandblind', 'someLocator'], 'sendKeys', ['illbedancingdeafdumbandblind']],
            ['hit', ['enter', 'someLocator'], 'sendKeys', ["\xEE\x80\x87"]],
            ['hit', ["\xEE\x80\x8D", 'someLocator'], 'sendKeys', ["\xEE\x80\x8D"]],
            ['hit', [WebDriverKeys::SEMICOLON, 'someLocator'], 'sendKeys', ["\xEE\x80\x98"]],
            ['press', ['enter', 'someLocator'], 'sendKeys', ["\xEE\x80\x87"]],
            ['enter', ['someLocator'], 'sendKeys', ["\xEE\x80\x87"]],
            ['click', ['someLocator'], 'click'],
            ['follow', ['someLocator'], 'click'],
            ['clear', ['someLocator'], 'clear'],
        ];
    }
}
