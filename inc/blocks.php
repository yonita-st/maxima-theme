<?php
/**
 * Регистрация на custom Gutenberg блокове
 */

// Пример: регистриране на custom блок (ако имаш такъв)
function maxima_register_blocks() {
    // Ако имаш собствен JS/CSS за блоковете, може да се enqueue-не тук

    // Пример за блок от /blocks/ директория (ако по-късно добавим):
    // register_block_type( get_template_directory() . '/blocks/example-block' );
}
add_action( 'init', 'maxima_register_blocks' );
