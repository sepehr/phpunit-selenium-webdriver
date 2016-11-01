<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Sepehr\PHPUnitSelenium\Exception\InvalidArgument;

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
    public function dispatchesVariousActionsOnAnElementFoundByALocator($api, $apiArgs, $action, $actionArgs = [])
    {
        $this
            ->injectSpy(RemoteWebDriver::class)
            ->shouldReceive('findElements')
            ->andReturn([$element = $this->spy(RemoteWebElement::class)]);

        $this->$api(...$apiArgs);

        $element
            ->shouldHaveReceived($action)
            ->with(...$actionArgs)
            ->once();
    }

    /** @test */
    public function dispatchesActionsOnAnElementObject()
    {
        $this->injectSpy(RemoteWebDriver::class);
        $element = $this->spy(RemoteWebElement::class);

        $this->type($text = 'sending some keys...', $element);

        $element
            ->shouldHaveReceived('sendKeys')
            ->once()
            ->with($text);
    }

    /** @test */
    public function dispatchesActionsOnACollectionOfElementsFoundByALocator()
    {
        $element = $this->spy(RemoteWebElement::class);

        $this
            ->injectSpy(RemoteWebDriver::class)
            ->shouldReceive('findElements')
            ->andReturn([$element, $element, $element]);

        $this->type($text = 'someText', $locator = 'locatorToMatchMultipleElements');

        $element
            ->shouldHaveReceived('sendKeys')
            ->times(3)
            ->with($text);
    }

    /** @test */
    public function throwsAnExceptionWhenHittingAnInvalidKey()
    {
        $this->expectException(InvalidArgument::class);

        $this->hit('invalidKey', 'someLocator');
    }

    /** @test */
    public function throwsAnExceptionWhenDispatchingActionsOnNonElements()
    {
        $this->expectException(InvalidArgument::class);

        $this->type('This dance is like a weapon... a self defence.', ['nonElement']);
    }

    /** @test */
    public function throwsAnExceptionWhenDispatchingAnInvalidActionOnAnElement()
    {
        $element = $this
            ->mock(RemoteWebElement::class)
            ->shouldReceive('invalidAction')
            ->andThrow(\Exception::class)
            ->getMock();

        $this->expectException(InvalidArgument::class);

        $this->type('dummyText', $element);
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
            ['fill', ['illBeDancingDeafDumbAndBlind', 'someLocator'], 'sendKeys', ['illBeDancingDeafDumbAndBlind']],
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
