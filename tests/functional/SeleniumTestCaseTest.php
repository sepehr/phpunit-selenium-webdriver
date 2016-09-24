<?php

namespace Sepehr\PHPUnitSelenium\Tests\Functional;

class SeleniumTestCaseTest extends BaseSeleniumTestCase
{
    /** @test */
    public function itVisitsFileUrls()
    {
        $url = $this->getTestFileUrl('test.html');

        $this->visit($url);

        $this->assertSame($url, $this->driverUrl());
    }
}
