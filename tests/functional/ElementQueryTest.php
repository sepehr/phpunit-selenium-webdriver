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

    /** @test */
    public function findsElementByName()
    {
        $element = $this->visitTestFile()->findByName($name = 'findMeByName');

        $this->assertSame($name, $element->getAttribute('name'));
    }

    /** @test */
    public function findsElementsByClass()
    {
        $elements = $this->visitTestFile()->findByClass($class = 'findMeByClass');

        foreach ($elements as $element) {
            $this->assertSame($class, $element->getAttribute('class'));
        }
    }

    /** @test */
    public function findsElementById()
    {
        $element = $this->visitTestFile()->findById($id = 'findMeById');

        $this->assertSame($id, $element->getAttribute('id'));
    }

    /** @test */
    public function findsElementByCssSelector()
    {
        $element = $this->visitTestFile()->findBySelector('#main > section p.lead');

        $this->assertSame(
            'Webdriver-backed Selenium testcase for PHPUnit with fluent testing API.',
            $element->getText()
        );
    }

    /**
     * @test
     *
     * @param string $attribute
     * @param string $value
     * @param string $tagName
     *
     * @dataProvider attributeValueTagProvider
     */
    public function findsElementByAttribute($attribute, $value, $tagName)
    {
        $element = $this->visitTestFile()->findByAttribute($attribute, $value, $tagName);

        $this->assertSame($value, $element->getAttribute($attribute));
    }

    /**
     * @test
     *
     * @param string $value
     * @param string $tagName
     *
     * @dataProvider valueTagProvider
     */
    public function findsElementByValue($value, $tagName)
    {
        $element = $this->visitTestFile()->findByValue($value, $tagName);

        $this->assertSame($value, $element->getAttribute('value'));
    }

    /** @test */
    public function findsElementsByPartialValue()
    {
        $elements = $this->visitTestFile()->findByPartialValue($partialValue = 'ByValue');

        foreach ($elements as $element) {
            $this->assertContains($partialValue, $element->getAttribute('value'));
        }
    }

    /**
     * @test
     *
     * @param string $text
     * @param string $tagName
     *
     * @dataProvider textTagProvider
     */
    public function findsElementByText($text, $tagName)
    {
        $element = $this->visitTestFile()->findByText($text, $tagName);

        $this->assertSame($text, $element->getText());
    }

    /** @test */
    public function findsElementsByPartialText()
    {
        $elements = $this->visitTestFile()->findByPartialText($partialText = 'ByText');

        foreach ($elements as $element) {
            $this->assertContains($partialText, $element->getText());
        }
    }

    /** @test */
    public function findsElementByTextOrValue()
    {
        $this->visitTestFile();

        $criteria = 'findInputByValue';

        $this->assertSame(
            $criteria,
            $this->findByTextOrValue($criteria)->getAttribute('value')
        );

        $criteria = 'findButtonByText';

        $this->assertSame(
            $criteria,
            $this->findByTextOrValue($criteria)->getText()
        );
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
    public function findsElementByNameOrId()
    {
        $this->visitTestFile();

        $criteria = 'findMeById';

        $this->assertSame($criteria, $this->findByNameOrId($criteria)->getAttribute('id'));

        $criteria = 'findMeByName';

        $this->assertSame($criteria, $this->findByNameOrId($criteria)->getAttribute('name'));
    }

    /** @test */
    public function findsLinkByItsText()
    {
        $element = $this->visitTestFile()->findByLinkText($text = 'View on github');

        $this->assertSame($text, $element->getText());
    }

    /** @test */
    public function findsLinkByItsPartialText()
    {
        $element = $this->visitTestFile()->findByLinkPartialText($text = 'leniumHQ');

        $this->assertContains($text, $element->getText());
    }

    /** @test */
    public function findsLinkByItsHref()
    {
        $element = $this->visitTestFile()->findByLinkHref($href = 'http://www.seleniumhq.org/');

        $this->assertEquals($href, $element->getAttribute('href'));
    }

    /** @test */
    public function findsLinkByItsPartialHref()
    {
        $element = $this->visitTestFile()->findByLinkPartialHref($href = 'mhq.org');

        $this->assertContains($href, $element->getAttribute('href'));
    }

    /** @test */
    public function findsElementByXpath()
    {
        $element = $this->visitTestFile()->findByXpath('//*[@id="main"]/section[1]/p');

        $this->assertSame(
            'Webdriver-backed Selenium testcase for PHPUnit with fluent testing API.',
            $element->getText()
        );
    }

    /** @test */
    public function findsElementsByTagName()
    {
        $elements = $this->visitTestFile()->findByTag($tagName = 'a');

        foreach ($elements as $element) {
            $this->assertSame($tagName, $element->getTagName());
        }
    }

    /** @test */
    public function findsElementByTabIndex()
    {
        $element = $this->visitTestFile()->findByTabIndex($tabindex = 7);

        $this->assertEquals($tabindex, $element->getAttribute('tabindex'));
    }

    /**
     * @test
     *
     * @param string $locator
     * @param string $text
     *
     * @dataProvider locatorTextProvider
     */
    public function findsElementsByLocator($locator, $text)
    {
        $element = $this->visitTestFile()->find($locator);

        $this->assertSame($text, $element->getText());
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
            $this->createWebDriverByInstance('cssSelector', 'lead')
        );
    }

    /**
     * Data provider for attribute/value/tag pairs.
     *
     * @return array
     */
    public static function attributeValueTagProvider()
    {
        return [
            ['data-dummy', 'findMeByAttribute', 'p'],
            ['href', 'https://github.com/sepehr/phpunit-selenium-webdriver', 'a'],
            ['placeholder', 'findMeByMyPlaceholder', '*'],
        ];
    }

    /**
     * Data provider for value/tag pairs.
     *
     * @return array
     */
    public static function valueTagProvider()
    {
        return [
            ['findInputByValue', 'input'],
            ['findOptionByValue-1', 'option'],
            ['findOptionByValue-2', '*'],
        ];
    }

    /**
     * Data provider for text/tag pairs.
     *
     * @return array
     */
    public static function textTagProvider()
    {
        return [
            ['findTextareaByText', 'textarea'],
            ['findTextareaByText', '*'],
            ['findButtonByText', 'button'],
            ['findButtonByText', '*'],
            ['This span can be found by its text, too.', '*'],
        ];
    }

    /**
     * Data provider for locator/text pairs.
     *
     * @return array
     */
    public static function locatorTextProvider()
    {
        return [
            ['findMeByName', ''],
            ['findInputByValue', ''],
            ['findButtonByText', 'findButtonByText'],
            ['findMeById', 'This element can be found by its ID: findMeById'],
            ['.findMeByClass:first-child', 'This element can be found by this class: findMeByClass'],
            ['//*[@id="main"]/section[1]/p', 'Webdriver-backed Selenium testcase for PHPUnit with fluent testing API.'],
        ];
    }
}
