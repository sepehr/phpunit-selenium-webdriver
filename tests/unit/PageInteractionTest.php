<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Mockery;
use Sepehr\PHPUnitSelenium\Util\Filesystem;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Sepehr\PHPUnitSelenium\Exception\InvalidArgument;

class PageInteractionTest extends UnitSeleniumTestCase
{

    /** @test */
    public function visitsAPageUrlAndUpdatesCurrentUrl()
    {
        $this
            ->inject(RemoteWebDriver::class)
            ->shouldReceive('get')
            ->with($url = 'https://github.com/sepehr')
            ->shouldReceive('getCurrentURL')
            ->andReturn($url);

        $this->visit($url);

        $this->assertSame($url, $this->url());
    }

    /** @test */
    public function returnsPageTitle()
    {
        $this
            ->inject(RemoteWebDriver::class)
            ->shouldReceive('getTitle')
            ->andReturn($expected = 'Some sample page title...');

        $this->assertSame($expected, $this->pageTitle());
    }

    /** @test */
    public function returnsPageSource()
    {
        $this
            ->inject(RemoteWebDriver::class)
            ->shouldReceive('getPageSource')
            ->andReturn($expected = '<html><body>Lorem ipsum...</body></html>');

        $this->assertSame($expected, $this->pageSource());
    }

    /** @test */
    public function savesPageSourceToFile()
    {
        $filesystem = $this->injectSpy(Filesystem::class);

        $this
            ->inject(RemoteWebDriver::class)
            ->shouldReceive('getPageSource')
            ->andReturn($source = '<html><body>Lorem ipsum...</body></html>');

        $this->savePageSource($filepath = '/tmp/source.html');

        $filesystem
            ->shouldHaveReceived('put')
            ->with($filepath, $source);
    }

    /** @test */
    public function throwsAnExceptionWhenSavingPageSourceIntoAnInvalidFile()
    {
        $this
            ->inject(RemoteWebDriver::class)
            ->shouldReceive('getPageSource')
            ->andReturn($source = '<html><body>Lorem ipsum...</body></html>');

        $this
            ->inject(Filesystem::class)
            ->shouldReceive('put')
            ->with($filepath = '/some/invalid/path/source.html', $source)
            ->andThrow(InvalidArgument::class);

        $this->expectException(InvalidArgument::class);

        $this->savePageSource($filepath);
    }
}
