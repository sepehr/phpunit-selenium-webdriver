<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Mockery;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Sepehr\PHPUnitSelenium\WebDriver\WebDriverBy;
use Sepehr\PHPUnitSelenium\Exception\NoSuchElement;
use Facebook\WebDriver\Exception\NoSuchElementException;

class ElementQueryTest extends UnitSeleniumTestCase
{

    /** @test */
    public function findsElementsByAnInstanceOfWebDriverBy()
    {
        $webDriver = $this->injectSpy(RemoteWebDriver::class);

        $this->findBy($webDriverBy = WebDriverBy::create());

        $webDriver
            ->shouldHaveReceived('findElements')
            ->with($webDriverBy)
            ->once();
    }

    /** @test */
    public function returnsAnEmptyArrayWhenNoElementIsFound()
    {
        $this
            ->inject(RemoteWebDriver::class)
            ->shouldReceive('findElements')
            ->andReturn([]);

        $this->assertSame([], $this->findBy(WebDriverBy::create()));
    }

    /** @test */
    public function unwrapsContainingArrayWhenFindsOnlyOneElement()
    {
        $this
            ->inject(RemoteWebDriver::class)
            ->shouldReceive('findElements')
            ->andReturn(['foo']);

        $this->assertSame('foo', $this->findBy(WebDriverBy::create()));
    }

    /** @test */
    public function findsJustOneElement()
    {
        $this
            ->inject(RemoteWebDriver::class)
            ->shouldReceive('findElement')
            ->andReturn($expected = 'foo');

        $this->assertSame($expected, $this->findOneBy(WebDriverBy::create()));
    }

    /** @test */
    public function throwsAnExceptionWhenTryingToFindJustOneElementAndItsNotAvailable()
    {
        $this
            ->inject(RemoteWebDriver::class)
            ->shouldReceive('findElement')
            ->andThrow(NoSuchElementException::class);

        $this->expectException(NoSuchElement::class);

        $this->findOneBy(WebDriverBy::create());
    }

    /** @test */
    public function findsElementsByXpathLocator()
    {
        $this
            ->inject(RemoteWebDriver::class)
            ->shouldReceive('findElements')
            ->andReturn($expected = ['el1', 'el2']);

        $this->assertSame($expected, $this->find('//*[@id="main"]/section[1]/p'));
    }

    /** @test */
    public function throwsAnExceptionIfNoElementIsFoundByProvidedLocator()
    {
        $this
            ->inject(RemoteWebDriver::class)
            ->shouldReceive('findElements')
            ->times(6);

        $this->expectException(NoSuchElement::class);

        $this->find($locator = 'someLocator');
    }

    /**
     * @test
     *
     * Tests default mechanisms of findBy*() methods.
     *
     * @param string $api Name of SeleniumTestCase API method.
     * @param array $args API method array of args.
     * @param string $mechanism WebDriverBy mechanism method to be expected to be called.
     * @param string|null $alt Parameter to be passed to specified WebDriverBy mechanism method, falls back to $args.
     *
     * @dataProvider elementQueryMethodProvider
     */
    public function findsElementsByDifferentMachanisms($api, array $args, $mechanism, $alt = null)
    {
        $this
            ->inject(RemoteWebDriver::class)
            ->shouldReceive('findElements')
            ->once();

        $this
            ->inject(WebDriverBy::class)
            ->shouldReceive($mechanism)
            ->with($alt ? $alt : $args[0])
            ->once()
            ->andReturn(Mockery::self());

        $this->$api(...$args);
    }

    /**
     * @test
     *
     * Tests fallback mechanism of findByBarOrBaz() methods.
     *
     * @param string $api Name of SeleniumTestCase API method.
     * @param array $args API method array of args.
     * @param array $mechanism WebDriverBy mechanism method to be expected to be called.
     * @param array $alt Parameter to be passed to specified WebDriverBy mechanism method, falls back to $args.
     *
     * @dataProvider elementQueryMethodWithFallbackProvider
     */
    public function findsElementsByDifferentFallbackMachanisms($api, array $args, array $mechanism, array $alt)
    {
        $this
            ->inject(RemoteWebDriver::class)
            ->shouldReceive('findElements')
            ->twice()
            ->andReturn([], $expected = 'foundElement');

        $this
            ->inject(WebDriverBy::class)
            ->shouldReceive($mechanism[0])
            ->with($alt[0])
            ->once()
            ->andReturn(Mockery::self())
            ->shouldReceive($mechanism[1])
            ->with($alt[1])
            ->once()
            ->andReturn(Mockery::self());

        $this->assertSame($expected, $this->$api(...$args));
    }

    /** @test */
    public function findsFormElements()
    {
        $element = $this
            ->mock(RemoteWebElement::class)
            ->shouldReceive('getTagName')
            ->andReturn('form');

        $this
            ->inject(RemoteWebDriver::class)
            ->shouldReceive('findElements')
            ->andReturn([$element->getMock()]);

        $this->assertInstanceOf(RemoteWebElement::class, $this->findForm('formLocator'));
    }

    /** @test */
    public function throwsAnExceptionWhenLocatorMatchesANonFormElement()
    {
        $element = $this
            ->mock(RemoteWebElement::class)
            ->shouldReceive('getTagName')
            ->andReturn('div');

        $this
            ->inject(RemoteWebDriver::class)
            ->shouldReceive('findElements')
            ->andReturn([$element->getMock()]);

        $this->expectException(NoSuchElement::class);

        $this->findForm('nonFormLocator');
    }

    /**
     * Element query data provider.
     *
     * We use this provider to test multiple find*() methods with one test.
     *
     * @return array
     */
    public static function elementQueryMethodProvider()
    {
        return [
            // Pattern:
            // [$api, $args[], $mechanism[, $alt]]
            ['findByName', ['elementName'], 'name'],
            ['findBySelector', ['ul > li .selector'], 'cssSelector'],
            ['findByClass', ['someClass'], 'cssSelector', '.someClass'],
            ['findById', ['someId'], 'id'],
            ['findByAttribute', ['data-dummy', 'someAttr', '*'], 'cssSelector', "*[data-dummy='someAttr']"],
            ['findByPartialAttribute', ['data-dummy', 'someAttr', '*'], 'cssSelector', "*[data-dummy*='someAttr']"],
            ['findByValue', ['someValue', '*'], 'cssSelector', "*[value='someValue']"],
            ['findByPartialValue', ['someValue', '*'], 'cssSelector', "*[value*='someValue']"],
            ['findByText', ['someText', '*'], 'xpath', "//*[text()='someText']"],
            ['findByBody', ['someText', '*'], 'xpath', "//*[text()='someText']"],
            ['findByPartialText', ['someText', '*'], 'xpath', "//*[contains(text(), 'someText')]"],
            ['findByLinkText', ['someText'], 'linkText'],
            ['findByLinkPartialText', ['someText'], 'partialLinkText'],
            ['findByLinkHref', ['https://g.cn/'], 'cssSelector', "a[href='https://g.cn/']"],
            ['findByLinkPartialHref', ['/sepehr'], 'cssSelector', "a[href*='/sepehr']"],
            ['findByXpath', ['/html/body/div/div[2]/div[1]/h1'], 'xpath'],
            ['findByTag', ['someTag'], 'tagName'],
            ['findByTabIndex', [7, '*'], 'cssSelector', "*[tabindex='7']"],
        ];
    }

    /**
     * Element query data provider.
     *
     * @return array
     */
    public static function elementQueryMethodWithFallbackProvider()
    {
        return [
            // Pattern:
            // [$api, $args[], $mechanism[], $alt[]]
            [
                'findByTextOrValue',
                ['someValue', '*'],
                ['xpath', 'cssSelector'],
                ["//*[text()='someValue']", "*[value='someValue']"]
            ],
            [
                'findByPartialTextOrValue',
                ['someValue', '*'],
                ['xpath', 'cssSelector'],
                ["//*[contains(text(), 'someValue')]", "*[value*='someValue']"]
            ],
            [
                'findByNameOrId',
                ['someId', '*'],
                ['name', 'id'],
                ['someId', 'someId']
            ],
        ];
    }
}
