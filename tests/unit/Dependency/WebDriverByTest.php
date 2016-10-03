<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Dependency;

use Mockery;
use Facebook\WebDriver\WebDriverBy;
use Sepehr\PHPUnitSelenium\Exception\InvalidArgument;
use Sepehr\PHPUnitSelenium\Tests\Unit\UnitSeleniumTestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class WebDriverByTest extends UnitSeleniumTestCase
{

    /** @test */
    public function createsAnInstanceOfWebDriverBy()
    {
        Mockery::mock('alias:' . WebDriverBy::class)
            ->shouldReceive($mechanism = 'id')
            ->once()
            ->with($value = 'someElementId')
            ->andReturn(Mockery::self())
            ->mock();

        $this->assertInstanceOf(
            WebDriverBy::class,
            $this->createWebDriverByInstance($mechanism, $value)
        );
    }

    /** @test */
    public function throwsAnExceptionWhenCreatingAnInstanceOfWebDriverByWithInvalidMechanism()
    {
        Mockery::mock('alias:' . WebDriverBy::class)
            ->shouldReceive($mechanism = 'invalidMechanism')
            ->once()
            ->with($value = 'someValue')
            ->andThrow(\Exception::class);

        $this->expectException(InvalidArgument::class);

        $this->createWebDriverByInstance($mechanism, $value);
    }
}
