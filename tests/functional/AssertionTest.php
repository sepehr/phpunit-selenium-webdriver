<?php

namespace Sepehr\PHPUnitSelenium\Tests\Functional;

class AssertionTest extends FunctionalSeleniumTestCase
{

    /** @test */
    public function assertsPageUrl()
    {
        $url = $this->getTestFileUrl('test.html');

        $this
            ->visit($url)
            ->seePageIs($url)
            ->dontSeePageIs('https://github.com/sepehr');
    }

    /** @test */
    public function assertsPageSource()
    {
        $this
            ->visitTestFile()
            ->see('Webdriver-backed Selenium testcase')
            ->dontSee('an area of the mind which could be called unsane, beyond sanity and yet not insane.');
    }

    /** @test */
    public function assertsPageTitle()
    {
        $this
            ->visitTestFile()
            ->seeTitle($this->testFileTitle)
            ->seeTitleContains(substr($this->testFileTitle, 0, 5))
            ->dontSeeTitle('There is fear to face...')
            ->dontSeeTitleContains('...but happiness');
    }
}
