<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit56118b9947e8d9e613c1a7965ba2d067
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Twilio\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Twilio\\' => 
        array (
            0 => __DIR__ . '/..' . '/twilio/sdk/src/Twilio',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit56118b9947e8d9e613c1a7965ba2d067::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit56118b9947e8d9e613c1a7965ba2d067::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
