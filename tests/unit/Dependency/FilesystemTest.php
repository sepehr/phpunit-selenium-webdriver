<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Dependency;

use Mockery;
use Sepehr\PHPUnitSelenium\Utils\Filesystem;
use Sepehr\PHPUnitSelenium\Tests\Unit\UnitSeleniumTestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class FilesystemTest extends UnitSeleniumTestCase
{

    /** @test */
    public function createsAFilesystemInstance()
    {
        Mockery::mock('overload:' . Filesystem::class);

        $this->assertInstanceOf(
            Filesystem::class,
            $this->createFilesystemInstance()
        );
    }
}
