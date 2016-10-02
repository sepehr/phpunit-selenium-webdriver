<?php

namespace Sepehr\PHPUnitSelenium\Tests\Unit\Util;

use Sepehr\PHPUnitSelenium\Util\Filesystem;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Instance of Filesystem.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Path to workspace where shit happens.
     *
     * @var string
     */
    protected $shithole;

    /**
     * Test setup.
     */
    public function setUp()
    {
        $this->filesystem = new Filesystem;

        $this->shithole = sys_get_temp_dir() . '/' . microtime(true) . '/' . mt_rand();
        mkdir($this->shithole, 0777, true);

        $this->shithole = realpath($this->shithole);
    }

    /** @test */
    public function putsDataIntoFile()
    {
        $content  = 'baz';
        $filepath = $this->shithole . DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'bar.txt';

        $this->filesystem->put($filepath, $content);

        $this->assertFileExists($filepath);
        $this->assertSame($content, file_get_contents($filepath));
        $this->assertFilePermissions(644, $filepath);
    }

    /**
     * Asserts file permissions.
     *
     * @param int $expectedPerms Expected permission. e.g. 644, 755
     * @param string $filepath
     *
     * @return bool
     */
    private function assertFilePermissions($expectedPerms, $filepath)
    {
        $actualPerms = (int) substr(sprintf('%o', fileperms($filepath)), -3);

        $this->assertEquals(
            $expectedPerms,
            $actualPerms,
            sprintf('File permissions for %s must be %s but is %s.', $filepath, $expectedPerms, $actualPerms)
        );
    }
}
