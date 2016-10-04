<?php

namespace Sepehr\PHPUnitSelenium\Tests\Functional;

use Facebook\WebDriver\Remote\RemoteWebElement;
use Sepehr\PHPUnitSelenium\Exception\NoSuchElement;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ElementQueryTest extends FunctionalSeleniumTestCase
{

    /**
     * @test
     *
     * Tests all find*() API methods.
     *
     * The test for all of these methods follow the same logic, so we implemented it
     * a lil bit more general, so we can utilize a dataProvider to test all these
     * methods. If we were going to write each test method separately, we'll end
     * up having around 30 methods with mostly same implementation. Not a great
     * idea, right? Lazies of the world unite!
     *
     * @param string $finder Name of finder method, e.g. findByName.
     * @param array $finderArgs Arguments array to pass to finder method.
     * @param string $assert PHPUnit assertion method name, e.g. assertSame.
     * @param string $truth Source of truth to check for.
     * @param string $action Method name to call on the found elements.
     * @param array $actionArgs Arguments array to pass to element action.
     *
     * Each method has its own dataProvider, change this provider to test a specific finder:
     * @dataProvider mechanismsProvider
     */
    public function findsElementsByDifferentMachanisms($finder, $finderArgs, $assert, $truth, $action, $actionArgs)
    {
        $elements = $this->visitTestFile()->$finder(...$finderArgs);

        is_array($elements) or $elements = [$elements];

        foreach ($elements as $element) {
            $this->$assert($truth, $element->$action(...$actionArgs));
        }
    }

    /** @test */
    public function findsElementByPartialTextOrValue()
    {
        $elements = $this->visitTestFile()->findByPartialTextOrValue($criteria = 'find');

        foreach ($elements as $element) {
            $test = $element->getAttribute('value') . $element->getText();

            $this->assertContains($criteria, $test);
        }
    }

    /** @test */
    public function findByReturnsAnEmptyArrayForBadLocator()
    {
        $by = $this->createWebDriverByInstance('name', 'badLocator,VeryBadLocator!');

        $this->assertEmpty(
            $this->visitTestFile()->findBy($by)
        );
    }

    /** @test */
    public function findByReturnsAnElementIfOnlyOneElementIsFound()
    {
        $by = $this->createWebDriverByInstance('id', 'main');
        $el = $this->visitTestFile()->findBy($by);

        $this->assertInstanceOf(RemoteWebElement::class, $el);
    }

    /** @test */
    public function findByReturnsAnArrayOfElementsIfMultipleElementsAreFound()
    {
        $by  = $this->createWebDriverByInstance('cssSelector', '.findMeByClass');
        $els = $this->visitTestFile()->findBy($by);

        $this->assertContainsOnlyInstancesOf(RemoteWebElement::class, $els);
    }

    /** @test */
    public function findOneByReturnsOnlyOneElementEvenThoughThereAreMultipleMatches()
    {
        $by = $this->createWebDriverByInstance('cssSelector', '.findMeByClass');
        $el = $this->visitTestFile()->findOneBy($by);

        $this->assertInstanceOf(RemoteWebElement::class, $el);
    }

    /** @test */
    public function findOneByThrowsAnExceptionIfNoElementIsFound()
    {
        $this->expectException(NoSuchElement::class);

        $this->visitTestFile()->findOneBy(
            $this->createWebDriverByInstance('cssSelector', 'someBadSelector')
        );
    }

    /**
     * Data provider of all find mechanisms.
     *
     * @return array
     */
    public static function mechanismsProvider()
    {
        return array_merge(
            self::findByIdQueryProvider(),
            self::findByClassQueryProvider(),
            self::findByNameQueryProvider(),
            self::findByPartialTextQueryProvider(),
            self::findByTextQueryProvider(),
            self::findByValueQueryProvider(),
            self::findByAttributeQueryProvider(),
            self::findByPartialValueQueryProvider(),
            self::findBySelectorQueryProvider(),
            self::findByXpathQueryProvider(),
            self::findByLinkPartialHrefQueryProvider(),
            self::findByLinkHrefQueryProvider(),
            self::findByLinkPartialTextQueryProvider(),
            self::findByLinkTextQueryProvider(),
            self::findByNameOrIdQueryProvider(),
            self::findByTextOrValueQueryProvider(),
            self::findByTabIndexQueryProvider(),
            self::findByTagQueryProvider(),
            self::findQueryProvider()
        );
    }

    /**
     * Data provider for findById().
     *
     * @return array
     */
    public static function findByIdQueryProvider()
    {
        return [
            [
                'findById',
                ['findMeById'],
                'assertSame',
                'findMeById',
                'getAttribute',
                ['id']
            ],
        ];
    }

    /**
     * Data provider for findByClass().
     *
     * @return array
     */
    public static function findByClassQueryProvider()
    {
        return [
            [
                'findByClass',
                ['findMeByClass'],
                'assertSame',
                'findMeByClass',
                'getAttribute',
                ['class']
            ],
        ];
    }

    /**
     * Data provider for findByName().
     *
     * @return array
     */
    public static function findByNameQueryProvider()
    {
        return [
            [
                'findByName',
                ['findMeByName'],
                'assertSame',
                'findMeByName',
                'getAttribute',
                ['name']
            ],
        ];
    }

    /**
     * Data provider for findByPartialText().
     *
     * @return array
     */
    public static function findByPartialTextQueryProvider()
    {
        return [
            [
                'findByPartialText',
                ['ByText'],
                'assertContains',
                'ByText',
                'getText',
                []
            ],
        ];
    }

    /**
     * Data provider for findByText().
     *
     * @return array
     */
    public static function findByTextQueryProvider()
    {
        return [
            [
                'findByText',
                ['findTextareaByText', 'textarea'],
                'assertSame',
                'findTextareaByText',
                'getText',
                []
            ],
            [
                'findByText',
                ['findTextareaByText', '*'],
                'assertSame',
                'findTextareaByText',
                'getText',
                []
            ],
            [
                'findByText',
                ['findButtonByText', 'button'],
                'assertSame',
                'findButtonByText',
                'getText',
                []
            ],
            [
                'findByText',
                ['findButtonByText', '*'],
                'assertSame',
                'findButtonByText',
                'getText',
                []
            ],
            [
                'findByText',
                ['This span can be found by its text, too.', '*'],
                'assertSame',
                'This span can be found by its text, too.',
                'getText',
                []
            ],
        ];
    }

    /**
     * Data provider for findByValue().
     *
     * @return array
     */
    public static function findByValueQueryProvider()
    {
        return [
            [
                'findByValue',
                ['findInputByValue', 'input'],
                'assertSame',
                'findInputByValue',
                'getAttribute',
                ['value']
            ],
            [
                'findByValue',
                ['findOptionByValue-1', 'option'],
                'assertSame',
                'findOptionByValue-1',
                'getAttribute',
                ['value']
            ],
            [
                'findByValue',
                ['findOptionByValue-2', '*'],
                'assertSame',
                'findOptionByValue-2',
                'getAttribute',
                ['value']
            ],
        ];
    }

    /**
     * Data provider for findByAttribute().
     *
     * @return array
     */
    public static function findByAttributeQueryProvider()
    {
        return [
            [
                'findByAttribute',
                ['data-dummy', 'findMeByAttribute', 'p'],
                'assertSame',
                'findMeByAttribute',
                'getAttribute',
                ['data-dummy']
            ],
            [
                'findByAttribute',
                ['href', 'https://github.com/sepehr/phpunit-selenium-webdriver', 'a'],
                'assertSame',
                'https://github.com/sepehr/phpunit-selenium-webdriver',
                'getAttribute',
                ['href']
            ],
            [
                'findByAttribute',
                ['placeholder', 'findMeByMyPlaceholder', '*'],
                'assertSame',
                'findMeByMyPlaceholder',
                'getAttribute',
                ['placeholder']
            ],
        ];
    }

    /**
     * Data provider for findByPartialValue().
     *
     * @return array
     */
    public static function findByPartialValueQueryProvider()
    {
        return [
            [
                'findByPartialValue',
                ['ByValue'],
                'assertContains',
                'ByValue',
                'getAttribute',
                ['value']
            ],
        ];
    }

    /**
     * Data provider for findBySelector().
     *
     * @return array
     */
    public static function findBySelectorQueryProvider()
    {
        return [
            [
                'findBySelector',
                ['#main > section p.lead'],
                'assertSame',
                'Webdriver-backed Selenium testcase for PHPUnit with fluent testing API.',
                'getText',
                []
            ],
        ];
    }

    /**
     * Data provider for findByXpath().
     *
     * @return array
     */
    public static function findByXpathQueryProvider()
    {
        return [
            [
                'findByXpath',
                ['//*[@id="main"]/section[1]/p'],
                'assertSame',
                'Webdriver-backed Selenium testcase for PHPUnit with fluent testing API.',
                'getText',
                []
            ],
        ];
    }

    /**
     * Data provider for findByLinkPartialHref().
     *
     * @return array
     */
    public static function findByLinkPartialHrefQueryProvider()
    {
        return [
            [
                'findByLinkPartialHref',
                ['mhq.org'],
                'assertContains',
                'mhq.org',
                'getAttribute',
                ['href']
            ],
        ];
    }

    /**
     * Data provider for findByLinkHref().
     *
     * @return array
     */
    public static function findByLinkHrefQueryProvider()
    {
        return [
            [
                'findByLinkHref',
                ['http://www.seleniumhq.org/'],
                'assertSame',
                'http://www.seleniumhq.org/',
                'getAttribute',
                ['href']
            ],
        ];
    }

    /**
     * Data provider for findByLinkPartialText().
     *
     * @return array
     */
    public static function findByLinkPartialTextQueryProvider()
    {
        return [
            [
                'findByLinkPartialText',
                ['leniumHQ'],
                'assertContains',
                'leniumHQ',
                'getText',
                []
            ],
        ];
    }

    /**
     * Data provider for findByLinkText().
     *
     * @return array
     */
    public static function findByLinkTextQueryProvider()
    {
        return [
            [
                'findByLinkText',
                ['View on github'],
                'assertSame',
                'View on github',
                'getText',
                []
            ],
        ];
    }

    /**
     * Data provider for findByNameOrId().
     *
     * @return array
     */
    public static function findByNameOrIdQueryProvider()
    {
        return [
            [
                'findByNameOrId',
                ['findMeById'],
                'assertSame',
                'findMeById',
                'getAttribute',
                ['id']
            ],
            [
                'findByNameOrId',
                ['findMeByName'],
                'assertSame',
                'findMeByName',
                'getAttribute',
                ['name']
            ],
        ];
    }

    /**
     * Data provider for findByTextOrValue().
     *
     * @return array
     */
    public static function findByTextOrValueQueryProvider()
    {
        return [
            [
                'findByTextOrValue',
                ['findInputByValue'],
                'assertSame',
                'findInputByValue',
                'getAttribute',
                ['value']
            ],
            [
                'findByTextOrValue',
                ['findButtonByText'],
                'assertSame',
                'findButtonByText',
                'getText',
                []
            ],
        ];
    }

    /**
     * Data provider for findByTabIndex().
     *
     * @return array
     */
    public static function findByTabIndexQueryProvider()
    {
        return [
            [
                'findByTabIndex',
                [7],
                'assertEquals',
                7,
                'getAttribute',
                ['tabindex']
            ],
        ];
    }

    /**
     * Data provider for findByTag().
     *
     * @return array
     */
    public static function findByTagQueryProvider()
    {
        return [
            [
                'findByTag',
                ['a'],
                'assertSame',
                'a',
                'getTagName',
                []
            ],
        ];
    }

    /**
     * Data provider for find().
     *
     * @return array
     */
    public static function findQueryProvider()
    {
        return [
            [
                'find',
                ['findMeByName'],
                'assertSame',
                '',
                'getText',
                []
            ],
            [
                'find',
                ['findInputByValue'],
                'assertSame',
                '',
                'getText',
                []
            ],
            [
                'find',
                ['findButtonByText'],
                'assertSame',
                'findButtonByText',
                'getText',
                []
            ],
            [
                'find',
                ['findMeById'],
                'assertSame',
                'This element can be found by its ID: findMeById',
                'getText',
                []
            ],
            [
                'find',
                ['.findMeByClass:first-child'],
                'assertSame',
                'This element can be found by this class: findMeByClass',
                'getText',
                []
            ],
            [
                'find',
                ['//*[@id="main"]/section[1]/p'],
                'assertSame',
                'Webdriver-backed Selenium testcase for PHPUnit with fluent testing API.',
                'getText',
                []
            ],
        ];
    }
}
