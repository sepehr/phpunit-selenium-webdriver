<?php

namespace Sepehr\PHPUnitSelenium\Utils;

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
     *
     * @return void
     */
    public function mkdir($dir)
    {
        is_dir($dir) or mkdir($dir, 0777, true);
    }
}
