<?php
/**
 * Настройки за Gutenberg и стилове за редактора
 * За да виждам линиите на блоковете, защото иначе не мога да се ориентирам има ли блог и къде е.
 */

// Зареждане на custom стилове за редактора
function maxima_add_editor_styles() {
    add_editor_style( 'gutenberg-outlines.css' );
}
add_action( 'admin_init', 'maxima_add_editor_styles' );

// Деактивиране на някои опции (пример, ако имаш в оригинала)
function maxima_editor_settings( $settings ) {
    // Тук можеш да добавиш или махаш настройки
    return $settings;
}
add_filter( 'block_editor_settings_all', 'maxima_editor_settings' );
