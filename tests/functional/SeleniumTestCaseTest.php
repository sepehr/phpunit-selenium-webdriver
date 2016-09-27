<?php

namespace Sepehr\PHPUnitSelenium\Tests\Functional;

use Sepehr\PHPUnitSelenium\Exceptions\NoSuchElement;

class SeleniumTestCaseTest extends BaseSeleniumTestCase
{

    // ----------------------------------------------------------------------------
    // Page URL
    // ----------------------------------------------------------------------------

    /** @test */
    public function visitsRemoteUrls()
    {
        $url = 'https://github.com/sepehr';

        $this->visit($url);

        $this->assertSame($url, $this->driverUrl());
    }

    /** @test */
    public function visitsLocalUrls()
    {
        $url = $this->getTestFileUrl('test.html');

        $this->visit($url);

        $this->assertSame($url, $this->driverUrl());
    }

    /** @test */
    public function performsAssertionsAgainstPageUrl()
    {
        $url = $this->getTestFileUrl('test.html');

        $this->visit($url)
             ->seePageIs($url)
             ->dontSeePageIs('https://github.com/sepehr');
    }

    // ----------------------------------------------------------------------------
    // Page Source
    // ----------------------------------------------------------------------------

    /** @test */
    public function seesPageSource()
    {
        $this->assertNotEmpty($this->visitTestFile()->getPageSource());
    }

    /** @test */
    public function performsAssertionsAgainstPageSource()
    {
        $this->visitTestFile()
             ->see('Webdriver-backed Selenium testcase')
             ->dontSee('an area of the mind which could be called unsane, beyond sanity and yet not insane.');
    }

    // ----------------------------------------------------------------------------
    // Page Title
    // ----------------------------------------------------------------------------

    /** @test */
    public function seesPageTitle()
    {
        $this->assertSame(
            $this->testFileTitle,
            $this->visitTestFile()->getPageTitle()
        );
    }

    /** @test */
    public function performsAssertionsAgainstPageTitle()
    {
        $this->visitTestFile()
             ->seeTitle($this->testFileTitle)
             ->seeTitleContains(substr($this->testFileTitle, 0, 5))
             ->dontSeeTitle('There is fear to face...')
             ->dontSeeTitleContains('...but happiness');
    }

    // ----------------------------------------------------------------------------
    // Finds Elements
    // ----------------------------------------------------------------------------

    /** @test */
    public function findsElementByName()
    {
        $element = $this->visitTestFile()
                        ->findByName($name = 'findMeByName');

        $this->assertSame($name, $element->getAttribute('name'));
    }

    /** @test */
    public function findsElementsByClass()
    {
        $elements = $this->visitTestFile()
                         ->findByClass($class = 'findMeByClass');

        foreach ($elements as $element) {
            $this->assertSame($class, $element->getAttribute('class'));
        }
    }

    /** @test */
    public function findsElementById()
    {
        $element = $this->visitTestFile()
                        ->findById($id = 'findMeById');

        $this->assertSame($id, $element->getAttribute('id'));
    }

    /** @test */
    public function findsElementByCssSelector()
    {
        $element = $this->visitTestFile()
                        ->findBySelector('#main > section p.lead');

        $this->assertSame(
            'Webdriver-backed Selenium testcase for PHPUnit with fluent testing API.',
            $element->getText()
        );
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
        $element = $this->visitTestFile()
                        ->findByValue($value, $tagName);

        $this->assertSame($value, $element->getAttribute('value'));
    }

    /** @test */
    public function findsElementsByPartialValue()
    {
        $elements = $this->visitTestFile()
                         ->findByPartialValue($partialValue = 'ByValue');

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
        $element = $this->visitTestFile()
                        ->findByText($text, $tagName);

        $this->assertSame($text, $element->getText());
    }

    /** @test */
    public function findsElementsByPartialText()
    {
        $elements = $this->visitTestFile()
                         ->findByPartialText($partialText = 'ByText');

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
        $elements = $this->visitTestFile()
                         ->findByPartialTextOrValue($criteria = 'find');

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
        $element = $this->visitTestFile()
                        ->findByLinkText($text = 'View on github');

        $this->assertSame($text, $element->getText());
    }

    /** @test */
    public function findsLinkByItsPartialText()
    {
        $element = $this->visitTestFile()
                        ->findByLinkPartialText($text = 'leniumHQ');

        $this->assertContains($text, $element->getText());
    }

    /** @test */
    public function findsElementByXpath()
    {
        $element = $this->visitTestFile()
                        ->findByXpath('//*[@id="main"]/section[1]/p');

        $this->assertSame(
            'Webdriver-backed Selenium testcase for PHPUnit with fluent testing API.',
            $element->getText()
        );
    }

    /** @test */
    public function findsElementsByTagName()
    {
        $elements = $this->visitTestFile()
                         ->findByTag($tagName = 'a');

        foreach ($elements as $element) {
            $this->assertSame($tagName, $element->getTagName());
        }
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
        $element = $this->visitTestFile()
                        ->find($locator);

        $this->assertSame($text, $element->getText());
    }

    /** @test */
    public function findByReturnsAnEmptyArrayForBadLocator()
    {
        $by = $this->getWebDriverByInstance('name', 'badLocator,VeryBadLocator!');

        $this->assertEmpty(
            $this->visitTestFile()->findBy($by)
        );
    }

    /** @test */
    public function findByReturnsAnElementIfOnlyOneElementIsFound()
    {
        $by = $this->getWebDriverByInstance('id', 'main');
        $el = $this->visitTestFile()->findBy($by);

        $this->assertInstanceOf($this->getValidElementClassName(), $el);
    }

    /** @test */
    public function findByReturnsAnArrayOfElementsIfMultipleElementsAreFound()
    {
        $by  = $this->getWebDriverByInstance('cssSelector', '.findMeByClass');
        $els = $this->visitTestFile()->findBy($by);

        $this->assertContainsOnlyInstancesOf($this->getValidElementClassName(), $els);
    }

    /** @test */
    public function findOneByReturnsOnlyOneElementEvenThoughThereAreMultipleMatchedOnes()
    {
        $by = $this->getWebDriverByInstance('cssSelector', '.findMeByClass');
        $el = $this->visitTestFile()->findOneBy($by);

        $this->assertInstanceOf($this->getValidElementClassName(), $el);
    }

    /** @test */
    public function findOneByThrowsAnExceptionIfNoElementIsFound()
    {
        $this->expectException(NoSuchElement::class);

        $this->visitTestFile()->findOneBy(
            $this->getWebDriverByInstance('cssSelector', 'lead')
        );
    }

    // ----------------------------------------------------------------------------
    // Interactions
    // ----------------------------------------------------------------------------

    // ----------------------------------------------------------------------------
    // Data Providers
    // ----------------------------------------------------------------------------

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
            ['.findMeByClass:first-child', 'This element can be found by this class: findMeByClass'],
            ['findMeByName', ''],
            ['findInputByValue', ''],
            ['findMeById', 'This element can be found by its ID: findMeById'],
            ['findButtonByText', 'findButtonByText'],
            /* This fails the fuckin test, fix find():
            ['//*[@id="main"]/section[1]/p', 'Webdriver-backed Selenium testcase for PHPUnit with fluent testing API.'],
            */
        ];
    }
}
