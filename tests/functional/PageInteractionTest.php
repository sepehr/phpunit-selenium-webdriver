<?php

namespace Sepehr\PHPUnitSelenium\Tests\Functional;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PageInteractionTest extends FunctionalSeleniumTestCase
{

    /** @test */
    public function visitsRemoteUrls()
    {
        $url = 'https://github.com/sepehr';

        $this->visit($url);

        $this->assertSame($url, $this->webDriverUrl());
    }

    /** @test */
    public function visitsLocalUrls()
    {
        $url = $this->getTestFileUrl('test.html');

        $this->visit($url);

        $this->assertSame($url, $this->webDriverUrl());
    }

    /** @test */
    public function performsAssertionsAgainstPageUrl()
    {
        $url = $this->getTestFileUrl('test.html');

        $this->visit($url)
            ->seePageIs($url)
            ->dontSeePageIs('https://github.com/sepehr');
    }

    /** @test */
    public function seesPageSource()
    {
        $this->assertNotEmpty($this->visitTestFile()->pageSource());
    }

    /** @test */
    public function performsAssertionsAgainstPageSource()
    {
        $this->visitTestFile()
            ->see('Webdriver-backed Selenium testcase')
            ->dontSee('an area of the mind which could be called unsane, beyond sanity and yet not insane.');
    }

    /** @test */
    public function seesPageTitle()
    {
        $this->assertSame(
            $this->testFileTitle,
            $this->visitTestFile()->pageTitle()
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
}
