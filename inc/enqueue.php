<?php
/**
 * Зареждане на CSS и JS за темата Maxima
 */

if ( ! function_exists( 'maxima_enqueue_scripts' ) ) {

    /**
     * Връща версия на файл според последна промяна (за да няма кеш проблеми).
     */
    function maxima_file_version( $relative_path ) {
        $path = get_theme_file_path( $relative_path );
        return file_exists( $path ) ? filemtime( $path ) : null;
    }

    function maxima_enqueue_scripts() {
        $theme_uri  = get_template_directory_uri();

        // 1) Базовият style.css (изисква се от WP)
        wp_enqueue_style(
            'maxima-style',
            get_stylesheet_uri(),
            array(),
            maxima_file_version( 'style.css' )
        );

        // 2) Общи стилове и скриптове за целия сайт
        wp_enqueue_style(
            'maxima-common-css',
            $theme_uri . '/assets/css/common.css',
            array('maxima-style'),
            maxima_file_version( 'assets/css/common.css' )
        );

        wp_enqueue_script(
            'maxima-common-js',
            $theme_uri . '/assets/js/common.js',
            array(), // ако ти трябва jQuery, добави 'jquery'
            maxima_file_version( 'assets/js/common.js' ),
            true // вкарваме в footer
        );

        // 3) Публичен shell: зареждаме само на страници с шаблон shell-public.php
        if ( is_page_template( 'page-templates/shell-public.php' ) ) {
            wp_enqueue_style(
                'maxima-public-css',
                $theme_uri . '/assets/css/public.css',
                array('maxima-common-css'),
                maxima_file_version( 'assets/css/public.css' )
            );

            wp_enqueue_script(
                'maxima-public-js',
                $theme_uri . '/assets/js/public.js',
                array('maxima-common-js'),
                maxima_file_version( 'assets/js/public.js' ),
                true
            );
        }

        // 4) Платен shell: зареждаме само на страници с шаблон shell-paid.php
        if ( is_page_template( 'page-templates/shell-paid.php' ) ) {
            wp_enqueue_style(
                'maxima-paid-css',
                $theme_uri . '/assets/css/paid.css',
                array('maxima-common-css'),
                maxima_file_version( 'assets/css/paid.css' )
            );

            wp_enqueue_script(
                'maxima-paid-js',
                $theme_uri . '/assets/js/paid.js',
                array('maxima-common-js'),
                maxima_file_version( 'assets/js/paid.js' ),
                true
            );
        }

        // (по избор) Gutenberg outlines и на фронта – оставям както беше, за да не сменяме поведение
        wp_enqueue_style(
            'maxima-gutenberg-outlines',
            $theme_uri . '/gutenberg-outlines.css',
            array(),
            maxima_file_version( 'gutenberg-outlines.css' )
        );
    }

    add_action( 'wp_enqueue_scripts', 'maxima_enqueue_scripts' );
}
