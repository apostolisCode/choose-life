<?php


namespace Composer\Autoload;

class ComposerStaticInitd6c39fb1c87178789e2f42fab5b8aded
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WPML\\Troubleshooting\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WPML\\Troubleshooting\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd6c39fb1c87178789e2f42fab5b8aded::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd6c39fb1c87178789e2f42fab5b8aded::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitd6c39fb1c87178789e2f42fab5b8aded::$classMap;

        }, null, ClassLoader::class);
    }
}
