<?php


namespace Composer\Autoload;

class ComposerStaticInite6a6296dc97510d75fa8ef66b026484e
{
    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'WPML\\CF7\\Constants' => __DIR__ . '/../..' . '/classes/constants.php',
        'WPML\\CF7\\Language_Metabox' => __DIR__ . '/../..' . '/classes/language-metabox.php',
        'WPML\\CF7\\Locale' => __DIR__ . '/../..' . '/classes/Locale.php',
        'WPML\\CF7\\Shortcodes' => __DIR__ . '/../..' . '/classes/shortcodes.php',
        'WPML\\CF7\\Templates' => __DIR__ . '/../..' . '/classes/Templates.php',
        'WPML\\CF7\\TranslationEditor\\JobFilter' => __DIR__ . '/../..' . '/classes/TranslationEditor/JobFilter.php',
        'WPML\\CF7\\TranslationEditor\\TagTexts' => __DIR__ . '/../..' . '/classes/TranslationEditor/TagTexts.php',
        'WPML\\CF7\\TranslationReview' => __DIR__ . '/../..' . '/classes/TranslationReview.php',
        'WPML\\CF7\\Translations' => __DIR__ . '/../..' . '/classes/translations.php',
        'WPML_Core_Version_Check' => __DIR__ . '/..' . '/wpml-shared/wpml-lib-dependencies/src/dependencies/class-wpml-core-version-check.php',
        'WPML_Dependencies' => __DIR__ . '/..' . '/wpml-shared/wpml-lib-dependencies/src/dependencies/class-wpml-dependencies.php',
        'WPML_PHP_Version_Check' => __DIR__ . '/..' . '/wpml-shared/wpml-lib-dependencies/src/dependencies/class-wpml-php-version-check.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInite6a6296dc97510d75fa8ef66b026484e::$classMap;

        }, null, ClassLoader::class);
    }
}
