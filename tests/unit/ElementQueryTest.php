<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Mockery;
use Facebook\WebDriver\WebDriverBy;
use Sepehr\PHPUnitSelenium\Exception\NoSuchElement;
use Facebook\WebDriver\Exception\NoSuchElementException;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ElementQueryTest extends UnitSeleniumTestCase
{

    /** @test */
    public function findsElementsByAnInstanceOfWebDriverBy()
    {
        $this->injectMockedWebDriver(
            $this->webDriverMock
                ->shouldReceive('findElements')
                ->once()
                ->with(WebDriverBy::class)
                ->andReturn($expected = ['foo', 'bar', 'baz'])
                ->getMock()
        );

        $webDriverByMock = Mockery::mock(WebDriverBy::class);

        $this->assertSame($expected, $this->findBy($webDriverByMock));
    }

    /** @test */
    public function returnsAnEmptyArrayWhenNoElementIsFound()
    {
        $this->injectMockedWebDriver(
            $this->webDriverMock
                ->shouldReceive('findElements')
                ->once()
                ->with(WebDriverBy::class)
                ->andReturn($expected = [])
                ->getMock()
        );

        $webDriverByMock = Mockery::mock(WebDriverBy::class);

        $this->assertSame($expected, $this->findBy($webDriverByMock));
    }

    /** @test */
    public function unwrapsContainingArrayWhenFindsOnlyOneElement()
    {
        $this->injectMockedWebDriver(
            $this->webDriverMock
                ->shouldReceive('findElements')
                ->once()
                ->with(WebDriverBy::class)
                ->andReturn(['foo'])
                ->getMock()
        );

        $webDriverByMock = Mockery::mock(WebDriverBy::class);

        $this->assertSame('foo', $this->findBy($webDriverByMock));
    }

    /** @test */
    public function findsJustOneElement()
    {
        $this->injectMockedWebDriver(
            $this->webDriverMock
                ->shouldReceive('findElement')
                ->once()
                ->with(WebDriverBy::class)
                ->andReturn($expected = 'foo')
                ->getMock()
        );

        $webDriverByMock = Mockery::mock(WebDriverBy::class);

        $this->assertSame($expected, $this->findOneBy($webDriverByMock));
    }

    /** @test */
    public function throwsAnExceptionWhenTryingToFindJustOneElementAndItsNotThere()
    {
        $this->injectMockedWebDriver(
            $this->webDriverMock
                ->shouldReceive('findElement')
                ->once()
                ->with(WebDriverBy::class)
                ->andThrow(NoSuchElementException::class)
                ->getMock()
        );

        $this->expectException(NoSuchElement::class);

        $this->findOneBy(Mockery::mock(WebDriverBy::class));
    }

    /**
     * @test
     *
     * @param string $api Name of SeleniumTestCase API method.
     * @param array $args API method array of args.
     * @param string $mechanism WebDriverBy mechanism method to be expected to be called.
     * @param string $alt Parameter to be passed to specified WebDriverBy mechanism method, falls back to $args.
     *
     * @dataProvider elementQueryProvider
     */
    public function hasApisToFindElementsByDifferentCriterias($api, array $args, $mechanism, $alt = null)
    {
        $this->injectMockedWebDriver(
            $this->webDriverMock
                ->shouldReceive('findElements')
                ->once()
                ->with(WebDriverBy::class)
                ->andReturn($expected = 'foundElement')
                ->getMock()
        );

        Mockery::mock('alias:' . WebDriverBy::class)
               ->shouldReceive($mechanism)
               ->once()
               ->with($alt ? $alt : $args[0])
               ->andReturn(Mockery::self())
               ->mock();

        $this->assertSame($expected, $this->$api(...$args));
    }

    /**
     * Element query data provider.
     *
     * We use this provider to test multiple find*() methods with one test.
     *
     * @return array
     */
    public static function elementQueryProvider()
    {
        return [
            // Pattern:
            // [$api, $args, $mechanism[, $alt]]
            ['findByName', ['elementName'], 'name'],
            ['findBySelector', ['ul > li .selector'], 'cssSelector'],
            ['findByClass', ['someClass'], 'cssSelector', '.someClass'],
            ['findById', ['someId'], 'id'],
            ['findByValue', ['someValue', '*'], 'cssSelector', "*[value='someValue']"],
            ['findByPartialValue', ['someValue', '*'], 'cssSelector', "*[value*='someValue']"],
            ['findByText', ['someText', '*'], 'xpath', "//*[text()='someText']"],
            ['findByBody', ['someText', '*'], 'xpath', "//*[text()='someText']"],
            ['findByPartialText', ['someText', '*'], 'xpath', "//*[contains(text(), 'someText')]"],
            // NOTE: Value fallback is not being tested:
            ['findByTextOrValue', ['someText', '*'], 'xpath', "//*[text()='someText']"],
            // NOTE: Value fallback is not being tested:
            ['findByPartialTextOrValue', ['someText', '*'], 'xpath', "//*[contains(text(), 'someText')]"],
            // NOTE: ID fallback is not being tested:
            ['findByNameOrId', ['someName'], 'name'],
            ['findByLinkText', ['someText'], 'linkText'],
            ['findByLinkPartialText', ['someText'], 'partialLinkText'],
            ['findByXpath', ['/html/body/div/div[2]/div[1]/h1'], 'xpath'],
            ['findByTag', ['someTag'], 'tagName'],
        ];
    }

}
