<?php

namespace Sepehr\PHPUnitSelenium\Tests\Functional;

class SeleniumTestCaseTest extends BaseSeleniumTestCase
{
    /** @test */
    public function visitsLocalUrls()
    {
        $url = $this->getTestFileUrl('test.html');

        $this->visit($url);

        $this->assertSame($url, $this->driverUrl());
    }

    /** @test */
    public function visitsRemoteUrls()
    {
        $url = 'https://github.com/sepehr';

        $this->visit($url);

        $this->assertSame($url, $this->driverUrl());
    }

    /** @test */
    public function throwsExceptionOnNotFoundUrls()
    {
        $this->visit('https://github.com/pagedoesnotexist');
    }

}
