<?php

namespace Sepehr\PHPUnitSelenium\Tests\Functional;

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
    public function readsPageSource()
    {
        $this->assertNotEmpty($this->visitTestFile()->pageSource());
    }

    /** @test */
    public function readsPageTitle()
    {
        $this->assertSame(
            $this->testFileTitle,
            $this->visitTestFile()->pageTitle()
        );
    }
}
