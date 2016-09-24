<?php

namespace Sepehr\PHPUnitSelenium\Tests\Functional;

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
    public function findsElementsByName()
    {
        $this->visitTestFile()
             ->find('findMeByName')
             ->type('');
    }

}
