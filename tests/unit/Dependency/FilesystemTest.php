<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Dependency;

use Sepehr\PHPUnitSelenium\Util\Filesystem;
use Sepehr\PHPUnitSelenium\Tests\Unit\UnitSeleniumTestCase;

class FilesystemTest extends UnitSeleniumTestCase
{

    /** @test */
    public function createsAnInstance()
    {
        $this->assertInstanceOf(Filesystem::class, $this->filesystem());
    }

    /** @test */
    public function doesNotCreateANewInstanceIfAlreadyExists()
    {
        $this->setFilesystem($fs = Filesystem::create());

        $this->assertSame($fs, $this->filesystem());
    }
}
