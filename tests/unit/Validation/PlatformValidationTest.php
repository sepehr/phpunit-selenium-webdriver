<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Validation;

use Sepehr\PHPUnitSelenium\Exception\InvalidArgument;
use Sepehr\PHPUnitSelenium\Tests\Unit\UnitSeleniumTestCase;

class PlatformValidationTest extends UnitSeleniumTestCase
{

    /**
     * @test
     *
     * @param string $platform
     *
     * @dataProvider platformNameProvider
     */
    public function acceptsValidPlatforms($platform)
    {
        $this->assertTrue($this->validatePlatform($platform));
    }

    /** @test */
    public function throwsAnExceptionWhenSettingAnInvalidPlatform()
    {
        $this->expectException(InvalidArgument::class);

        $this->setPlatform('invalidPlatform');
    }

    /**
     * Data provider for valid platform names.
     *
     * @return array
     */
    public static function platformNameProvider()
    {
        return [
            ['ANY'],
            ['ANDROID'],
            ['LINUX'],
            ['MAC'],
            ['UNIX'],
            ['VISTA'],
            ['WINDOWS'],
            ['XP'],
        ];
    }
}
