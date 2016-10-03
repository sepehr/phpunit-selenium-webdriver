<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Util;

use Sepehr\PHPUnitSelenium\Util\Locator;

class LocatorTest extends \PHPUnit_Framework_TestCase
{

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
        $this->assertTrue(Locator::isSelector($valid));

        $this->assertFalse(Locator::isSelector($invalid));
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
        $this->assertTrue(Locator::isXpath($valid));

        $this->assertFalse(Locator::isXpath($invalid));
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
