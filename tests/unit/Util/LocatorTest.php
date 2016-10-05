<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Util;

use Sepehr\PHPUnitSelenium\Util\Locator;

class LocatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Locator instance.
     *
     * @var Locator
     */
    protected $locator;

    public function setUp()
    {
        $this->locator = Locator::create();

        parent::setUp();
    }

    /**
     * @test
     *
     * @param string $valid Valid CSS selector.
     * @param string $invalid Invalid CSS selector.
     *
     * @dataProvider selectorXpathProvider
     */
    public function validatesIfLocatorIsACssSelectorOrNot($valid, $invalid)
    {
        $this->assertTrue(
            $this->locator->isSelector($valid),
            "Failed asserting that $valid is a valid CSS selector."
        );

        $this->assertFalse(
            $this->locator->isSelector($invalid),
            "Failed asserting that $invalid is an INVALID CSS selector."
        );
    }

    /**
     * @test
     *
     * @param string $invalid Invalid XPath.
     * @param string $valid Valid XPath.
     *
     * @dataProvider selectorXpathProvider
     */
    public function validatesIfLocatorIsXpathOrNot($invalid, $valid)
    {
        $this->assertTrue(
            $this->locator->isXpath($valid),
            "Failed asserting that $valid is a valid XPath."
        );

        $this->assertFalse(
            $this->locator->isXpath($invalid),
            "Failed asserting that $invalid is an INVALID XPath."
        );
    }

    /** @test */
    public function checksIfItsAValidLocator()
    {
        $this->assertTrue($this->locator->isLocator('Any string can be a locator...'));

        $this->assertFalse($this->locator->isLocator([]));
    }

    /**
     * Selector/Xpath provider.
     *
     * @return array
     */
    public static function selectorXpathProvider()
    {
        return [
            // Pattern: [selector, xpath]
            [
                '#main > section:nth-child(4) > form > button',
                '//*[@id="main"]/section[4]/form/button'
            ],
            [
                '#someId',
                "//*[contains(text(), 'someText')]"
            ],
            [
                '#main > section:nth-child(1) > div > h1',
                '//*[@id="main"]/section[1]/div/h1'
            ],
            [
                '#main > section:nth-child(3) > ul > li:nth-child(4) > input',
                '//*[@id="main"]/section[3]/ul/li[4]/input'
            ],
            [
                '.someClass',
                '//*[@id="main"]/section[3]/ul/li[11]/code[1]'
            ]
        ];
    }
}
