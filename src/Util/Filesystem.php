<?php

namespace Sepehr\PHPUnitSelenium\Util;

class Filesystem
{

    /**
     * Writes to filesystem.
     *
     * @param string $filepath
     * @param string $data
     *
     * @return bool
     */
    public function put($filepath, $data)
    {
        $this->mkdir(dirname($filepath));

        return file_put_contents($filepath, $data) === false ? false : true;
    }

    /**
     * Creates a new directory.
     *
     * @param string $dir
     * @param int $permission
     *
     * @return void
     */
    public function mkdir($dir, $permission = 0755)
    {
        is_dir($dir) or mkdir($dir, $permission, true);
    }
}
