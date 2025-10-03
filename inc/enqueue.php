<?php
/**
 * Зареждане на CSS и JS файловете за темата
 */

function maxima_enqueue_scripts() {
    // Основен стил
    wp_enqueue_style('maxima-style', get_stylesheet_uri());

    // Gutenberg outlines (за редактора)
    wp_enqueue_style('maxima-gutenberg-outlines', get_template_directory_uri() . '/gutenberg-outlines.css', array(), null);

    // Основен JS (ако имаш такъв)
    // wp_enqueue_script('maxima-scripts', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'maxima_enqueue_scripts');
