<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit;

use Mockery;
use Sepehr\PHPUnitSelenium\Util\Filesystem;
use Sepehr\PHPUnitSelenium\Exception\InvalidArgument;

class PageInteractionTest extends UnitSeleniumTestCase
{

    /** @test */
    public function returnsPageTitle()
    {
        $this->injectMockedWebDriver(
            $this->webDriverMock
                 ->shouldReceive('getTitle')
                 ->once()
                 ->andReturn($expected = 'Some sample page title...')
                 ->getMock()
        );

        $this->assertSame($expected, $this->pageTitle());
    }

    /** @test */
    public function returnsPageSource()
    {
        $this->injectMockedWebDriver(
            $this->webDriverMock
                 ->shouldReceive('getPageSource')
                 ->once()
                 ->andReturn($expected = '<html><body>Lorem ipsum...</body></html>')
                 ->getMock()
        );

        $this->assertSame($expected, $this->pageSource());
    }

    /** @test */
    public function savesPageSourceToFile()
    {
        $this->injectMockedWebDriver(
            $this->webDriverMock
                 ->shouldReceive('getPageSource')
                 ->once()
                 ->andReturn($source = '<html><body>Lorem ipsum...</body></html>')
                 ->getMock()
        );

        $this->injectMockedFilesystem(
            Mockery::mock(Filesystem::class)
                   ->shouldReceive('put')
                   ->once()
                   ->with($filepath = '/tmp/source.html', $source)
                   ->andReturn(true)
                   ->getMock()
        );

        $this->savePageSource($filepath);
    }

    /** @test */
    public function throwsAnExceptionWhenSavingPageSourceIntoAnInvalidFile()
    {
        $this->injectMockedWebDriver(
            $this->webDriverMock
                 ->shouldReceive('getPageSource')
                 ->once()
                 ->andReturn($source = '<html><body>Lorem ipsum...</body></html>')
                 ->getMock()
        );

        $this->injectMockedFilesystem(
            Mockery::mock(Filesystem::class)
                   ->shouldReceive('put')
                   ->once()
                   ->with($filepath = '/some/invalid/path/source.html', $source)
                   ->andThrow(InvalidArgument::class)
                   ->getMock()
        );

        $this->expectException(InvalidArgument::class);

        $this->savePageSource($filepath);
    }

    /**
     * Injects a mocked Filesystem into SeleniumTestCase.
     *
     * @param Filesystem $mockedFilesystem
     */
    private function injectMockedFilesystem(Filesystem $mockedFilesystem)
    {
        $this->setFilesystem($mockedFilesystem);
    }
}
