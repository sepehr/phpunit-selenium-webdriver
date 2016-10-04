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
     * @dataProvider findMechanismProvider
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
     * Data provider for find mechanisms.
     *
     * @return array
     */
    public static function findMechanismProvider()
    {
        return [
            // Pattern:
            // $finder, $finderArgs[], $assert, $truth, $action, $actionArgs[]

            // Tests findByName():
            [
                'findByName',
                ['findMeByName'],
                'assertSame',
                'findMeByName',
                'getAttribute',
                ['name']
            ],

            // Tests findByClass():
            [
                'findByClass',
                ['findMeByClass'],
                'assertSame',
                'findMeByClass',
                'getAttribute',
                ['class']
            ],

            // Tests findById():
            [
                'findById',
                ['findMeById'],
                'assertSame',
                'findMeById',
                'getAttribute',
                ['id']
            ],

            // Tests findBySelector():
            [
                'findBySelector',
                ['#main > section p.lead'],
                'assertSame',
                'Webdriver-backed Selenium testcase for PHPUnit with fluent testing API.',
                'getText',
                []
            ],

            // Tests findByAttribute():
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

            // Tests findByValue():
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

            // Tests findByPartialValue():
            [
                'findByPartialValue',
                ['ByValue'],
                'assertContains',
                'ByValue',
                'getAttribute',
                ['value']
            ],

            // Tests findByText():
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

            // Tests findByPartialText():
            [
                'findByPartialText',
                ['ByText'],
                'assertContains',
                'ByText',
                'getText',
                []
            ],

            // Tests findByLinkText():
            [
                'findByLinkText',
                ['View on github'],
                'assertSame',
                'View on github',
                'getText',
                []
            ],

            // Tests findByLinkPartialText():
            [
                'findByLinkPartialText',
                ['leniumHQ'],
                'assertContains',
                'leniumHQ',
                'getText',
                []
            ],

            // Tests findByLinkHref():
            [
                'findByLinkHref',
                ['http://www.seleniumhq.org/'],
                'assertSame',
                'http://www.seleniumhq.org/',
                'getAttribute',
                ['href']
            ],

            // Tests findByLinkPartialHref():
            [
                'findByLinkPartialHref',
                ['mhq.org'],
                'assertContains',
                'mhq.org',
                'getAttribute',
                ['href']
            ],

            // Tests findByXpath():
            [
                'findByXpath',
                ['//*[@id="main"]/section[1]/p'],
                'assertSame',
                'Webdriver-backed Selenium testcase for PHPUnit with fluent testing API.',
                'getText',
                []
            ],

            // Tests findByXpath():
            [
                'findByTag',
                ['a'],
                'assertSame',
                'a',
                'getTagName',
                []
            ],

            // Tests findByXpath():
            [
                'findByTabIndex',
                [7],
                'assertEquals',
                7,
                'getAttribute',
                ['tabindex']
            ],

            // Tests find():
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

            // Tests findByTextOrValue():
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

            // Tests findByNameOrId():
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
}
