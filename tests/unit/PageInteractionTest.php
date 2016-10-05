<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Mockery;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Sepehr\PHPUnitSelenium\Util\Filesystem;
use Sepehr\PHPUnitSelenium\Exception\InvalidArgument;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PageInteractionTest extends UnitSeleniumTestCase
{

    /** @test */
    public function visitsAPageUrl()
    {
        $this->inject(RemoteWebDriver::class)
            ->shouldReceive('get')
            ->once()
            ->with($url = 'https://github.com/sepehr')
            ->andReturn(Mockery::self())
            ->shouldReceive('getCurrentURL')
            ->once()
            ->andReturn($url);

        $this->visit($url);

        $this->assertSame($url, $this->url());
    }

    /** @test */
    public function returnsPageTitle()
    {
        $this->inject(RemoteWebDriver::class)
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn($expected = 'Some sample page title...');

        $this->assertSame($expected, $this->pageTitle());
    }

    /** @test */
    public function returnsPageSource()
    {
        $this->inject(RemoteWebDriver::class)
            ->shouldReceive('getPageSource')
            ->once()
            ->andReturn($expected = '<html><body>Lorem ipsum...</body></html>');

        $this->assertSame($expected, $this->pageSource());
    }

    /** @test */
    public function savesPageSourceToFile()
    {
        $this->inject(RemoteWebDriver::class)
            ->shouldReceive('getPageSource')
            ->once()
            ->andReturn($source = '<html><body>Lorem ipsum...</body></html>');

        $this->inject(Filesystem::class)
            ->shouldReceive('put')
            ->once()
            ->with($filepath = '/tmp/source.html', $source)
            ->andReturn(true);

        $this->savePageSource($filepath);
    }

    /** @test */
    public function throwsAnExceptionWhenSavingPageSourceIntoAnInvalidFile()
    {
        $this->inject(RemoteWebDriver::class)
            ->shouldReceive('getPageSource')
            ->once()
            ->andReturn($source = '<html><body>Lorem ipsum...</body></html>');

        $this->inject(Filesystem::class)
            ->shouldReceive('put')
            ->once()
            ->with($filepath = '/some/invalid/path/source.html', $source)
            ->andThrow(InvalidArgument::class);

        $this->expectException(InvalidArgument::class);

        $this->savePageSource($filepath);
    }
}
