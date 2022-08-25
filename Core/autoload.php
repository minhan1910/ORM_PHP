<?php


/**
 * @var $className like Core\User when using Use Core\User;
 */
// function myAutoLoader($className)
// {
//     $path = ROOT . '/';
//     $extension = '.php';
//     $file = $path . $className . $extension;
//     $file = str_replace('\\', '/', $file);

//     if (file_exists($file)) {
//         require_once $file;
//         echo $file;
//     } else {
//         throw new Error('file' . $file . ' not found');
//     }
// }


// Refactor using Singleton design pattern
class ClassAutoLoader
{
    private static $instance = null;

    private function __construct()
    {
        spl_autoload_register(array($this, 'loader'));
    }

    private function loader($className)
    {
        $path = ROOT . '/';
        $extension = '.php';
        $file = $path . $className . $extension;
        $file = str_replace('\\', '/', $file);

        if (file_exists($file)) {
            require_once $file;
        } else
            throw new Error('File ' . $file . ' not found');
    }

    public static function load()
    {
        if (self::$instance === null)
            return self::$instance = new ClassAutoLoader;
        return self::$instance;
    }
}

ClassAutoLoader::load();