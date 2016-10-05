<?php

namespace Sepehr\PHPUnitSelenium\Util;

class Filesystem
{

    /**
     * Creates an instance of the class.
     *
     * @return Filesystem
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Writes to filesystem.
     *
     * @param string $filepath
     * @param string $data
     *
     * @return bool
     */
    public static function put($filepath, $data)
    {
        self::mkdir(dirname($filepath));

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
    public static function mkdir($dir, $permission = 0755)
    {
        is_dir($dir) or mkdir($dir, $permission, true);
    }
}
