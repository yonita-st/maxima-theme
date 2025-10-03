<?php
/**
 * Контрол на достъп и редиректи за Maxima
 */

// Ако потребителят не е логнат и се опитва да отвори платена страница → редирект към /login
function maxima_redirect_non_logged_users() {
    if ( is_page_template( 'page-templates/shell-paid.php' ) && ! is_user_logged_in() ) {
        wp_redirect( site_url( '/login' ) );
        exit;
    }
}
add_action( 'template_redirect', 'maxima_redirect_non_logged_users' );
