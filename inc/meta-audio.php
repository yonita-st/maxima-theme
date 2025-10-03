<?php
/**
 * Мета-бокс за аудио в страниците
 */

// Добавяне на мета-бокс
function maxima_add_audio_metabox() {
    add_meta_box(
        'maxima_audio_metabox',
        'Аудио за страницата',
        'maxima_audio_metabox_callback',
        'page',
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'maxima_add_audio_metabox' );

// Callback съдържанието на мета-бокса
function maxima_audio_metabox_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'maxima_audio_nonce' );
    $audio_url = get_post_meta( $post->ID, '_maxima_audio_url', true );
    ?>
    <p>
        <label for="maxima_audio_url">Аудио URL:</label>
        <input type="text" name="maxima_audio_url" id="maxima_audio_url" value="<?php echo esc_attr( $audio_url ); ?>" style="width:100%;" />
    </p>
    <?php
}

// Записване на стойността при съхранение на страницата
function maxima_save_audio_metabox( $post_id ) {
    if ( ! isset( $_POST['maxima_audio_nonce'] ) || ! wp_verify_nonce( $_POST['maxima_audio_nonce'], basename( __FILE__ ) ) ) {
        return;
    }

    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
        return;
    }

    if ( isset( $_POST['maxima_audio_url'] ) ) {
        update_post_meta( $post_id, '_maxima_audio_url', sanitize_text_field( $_POST['maxima_audio_url'] ) );
    }
}
add_action( 'save_post', 'maxima_save_audio_metabox' );
