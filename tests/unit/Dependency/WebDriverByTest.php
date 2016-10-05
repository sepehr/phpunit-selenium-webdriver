<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Dependency;

use Mockery;
use Facebook\WebDriver\WebDriverBy;
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
            $this->webDriverBy->$mechanism($value)
        );
    }
}
