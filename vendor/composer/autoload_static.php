<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita4a3642491632a3a5cf6cb750b2467b7
{
    public static $prefixesPsr0 = array (
        'E' => 
        array (
            'EasyPost' => 
            array (
                0 => __DIR__ . '/..' . '/easypost/easypost-php/lib',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInita4a3642491632a3a5cf6cb750b2467b7::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
