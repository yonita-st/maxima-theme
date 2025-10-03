<?php
/**
 * Основни включвания на темата Maxima
 */

// Списък с всички модули, които искаме да зареждаме
$maxima_includes = array(
    'inc/access.php',
    'inc/enqueue.php',
    'inc/editor.php',
    'inc/meta-audio.php',
    'inc/blocks.php',
);

// Зареждаме всеки файл от списъка
foreach ( $maxima_includes as $file ) {
    $filepath = get_theme_file_path( $file );
    if ( file_exists( $filepath ) ) {
        require_once $filepath;
    }
}
