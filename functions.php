<?php
/* ============================================================
   Maxima Theme – functions.php
   ------------------------------------------------------------
   3.0.1 – Глобални стилове/скриптове (style.css, main.js)
   3.0.2 – Настройка на тема (thumbnails, менюта, HTML5)
   3.0.3 – Публични активи само за Public Shell (public.css/js)
   3.0.4 – Хелпъри за публични маршрути (Whitelist)
   3.0.5 – Достъп и пренасочвания (гейт за гости, редиректи)
       3.0.5.1 – Гейт за гости (към /login/)
       3.0.5.2 – Логнат на корена → към /yymmdd-2 (или /homemaximalife)
       3.0.5.3 – Login redirect → към /yymmdd-2 (или /homemaximalife)
       3.0.5.4 – Logout redirect → винаги към /login/
       3.0.5.5 – Помощно: maxima_logout_url()
   3.0.6 – Featured Audio метабокс за страници (избор на аудио)
   3.0.7 – YouTube oEmbed – force enablejsapi + playsinline + origin
           (поддържа youtube.com, youtu.be, youtube-nocookie.com)
   3.0.8 – Клиентска TZ: запис в cookie + опция за redirect към „днес“
   3.0.9 – Custom Fonts: Raleway (global) + TYScript (.handwriting)
   4.0 Мои елемнти (блокове) в Gutenberg
    4.1 progress-circle
    4.2 корекция на Gutenberg - блоковете и колоните да се виждат (сива линия)
    4.3 Блок с интерактивен чеклист за дневни указания в Gutenberg
   ============================================================ */

/* 3.0.1 – Глобални стилове и скриптове */
function maxima_enqueue_assets() {
    // style.css (версия по filemtime против кеш)
    $style = get_stylesheet_directory() . '/style.css';
    if ( file_exists($style) ) {
        wp_enqueue_style(
            'maxima-style',
            get_stylesheet_uri(),
            [],
            filemtime($style)
        );
        // (Премахнат е inline мобилният CSS – вече е в отделния снипет)
    }

    // Главен JS (по избор). Ако липсва – пропускаме.
    $main_js = get_template_directory() . '/assets/js/main.js';
    if ( file_exists($main_js) ) {
        wp_enqueue_script(
            'maxima-script',
            get_template_directory_uri() . '/assets/js/main.js',
            [],
            filemtime($main_js),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'maxima_enqueue_assets');

/* 3.0.8 – Клиентска TZ + опция за „днес“ чрез ?mx_go_today=1 */
add_action('wp_head', function () {
    // --- Критично за мобилен изглед
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
    ?>
    <script>
    (function(){
      try {
        // 1) Засичаме IANA timezone и записваме в cookie за 30 дни
        var tz = (Intl && Intl.DateTimeFormat) ? Intl.DateTimeFormat().resolvedOptions().timeZone : '';
        if (tz) {
          var exp = new Date(); exp.setTime(exp.getTime() + 30*24*60*60*1000);
          document.cookie = 'mx_tz=' + encodeURIComponent(tz) + '; path=/; expires=' + exp.toUTCString();
        }

        // 2) Ако сме извикани с ?mx_go_today=1 → изчисли локалния yymmdd-2 и пренасочи
        var params = new URLSearchParams(window.location.search);
        if (params.has('mx_go_today')) {
          var d = new Date(); // локалното време на потребителя
          var yy = String(d.getFullYear()).slice(-2);
          var mm = String(d.getMonth()+1).padStart(2,'0');
          var dd = String(d.getDate()).padStart(2,'0');
          var slug = yy + mm + dd + '-2';
          var target = window.location.origin + '/' + slug + '/';
          // Практичен fallback: ако страницата не съществува, отиди към /homemaximalife
          // (ще се изпълни от PHP, но за по-сигурно добавяме graceful degrade)
          window.location.replace(target);
        }
      } catch(e) {}
    })();
    </script>
    <?php
});

/* 3.0.2 – Настройка на тема (thumbnails, менюта, HTML5) */
function maxima_theme_setup() {
    // Фонове по страница (Featured Image)
    add_theme_support('post-thumbnails');

    // HTML5 маркъп за форми/галерии/скриптове/стилове
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);

    // Менюта
    register_nav_menus([
        'primary'            => __('Primary Menu', 'maxima'),
        'world-primary'      => __('Основно меню (свят)', 'maxima'),
        'world-secondary'    => __('Подменю (второ ниво, свят)', 'maxima'),
        'worlds'             => __('Меню за 3-те свята', 'maxima'),
        'maxima_life_menu'   => __('Maxima Life Menu', 'maxima'),
        'maxima_divine_menu' => __('Maxima Divine Menu', 'maxima'),
        'maxima_boss_menu'   => __('Maxima Boss Menu', 'maxima'),
        'legal'              => __('Политики', 'maxima'),
        'docs'               => __('Документи', 'maxima'),
        'faq'                => __('ЧЗВ', 'maxima'),
    ]);
}
add_action('after_setup_theme', 'maxima_theme_setup');

/* 3.0.3 – Публични активи само за шаблона "Maxima: Public Shell" */
function maxima_enqueue_public_assets() {
    if ( is_page_template('templates/public/shell-public.php') ) {
        // CSS
        $css = get_template_directory() . '/assets/css/public/public.css';
        if ( file_exists($css) ) {
            wp_enqueue_style(
                'maxima-public',
                get_template_directory_uri() . '/assets/css/public/public.css',
                [],
                filemtime($css)
            );
        }
        // JS (бутоните/логиката за музика)
        $js = get_template_directory() . '/assets/js/public/public.js';
        if ( file_exists($js) ) {
            wp_enqueue_script(
                'maxima-public',
                get_template_directory_uri() . '/assets/js/public/public.js',
                [],
                filemtime($js),
                true
            );
        }
    }
}
add_action('wp_enqueue_scripts', 'maxima_enqueue_public_assets', 20);

/* 3.0.4 – Хелпъри за публични маршрути (Whitelist) */
function maxima_public_slugs() {
    return apply_filters('maxima_public_slugs', [
        // Собствени публични страници
        'login',
        'register',
        'lost-password',
        'about',
        'terms',
        'pricing',
        // PMPro (ако показваме нива/чекаут публично)
        'membership-levels',
        'membership-checkout',
        'membership-confirmation',
    ]);
}

/* >>>>>>>>>>>> TZ ПОТРЕБИТЕЛ – помощници <<<<<<<<<<< */
function maxima_user_timezone(): DateTimeZone {
    $tz_cookie = isset($_COOKIE['mx_tz']) ? trim($_COOKIE['mx_tz']) : '';
    try {
        if ($tz_cookie !== '') {
            return new DateTimeZone($tz_cookie);
        }
    } catch (Throwable $e) {}
    return wp_timezone(); // fallback към Settings → General
}
function maxima_today_slug(): string {
    // yymmdd-2 според ЛОКАЛНОТО време на потребителя (браузърен IANA tz, cookie mx_tz)
    try {
        $tz  = maxima_user_timezone();
        $now = new DateTimeImmutable('now', $tz);
        return strtolower( $now->format('ymd') . '-2' );
    } catch (Throwable $e) {
        // последна защита – WP зона
        return strtolower( wp_date('ymd') . '-2' );
    }
}
/* >>>>>>>>>>>> /TZ ПОТРЕБИТЕЛ <<<<<<<<<<< */

function maxima_build_url_from_slug( string $slug ): string {
    return home_url( user_trailingslashit( $slug ) );
}
function maxima_current_url(): string {
    $scheme = is_ssl() ? 'https://' : 'http://' ;
    return $scheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}
function maxima_path_only( string $url ): string {
    $path = parse_url( $url, PHP_URL_PATH );
    $path = $path ? $path : '/';
    return untrailingslashit( $path );
}

/* 3.0.5 – Достъп и пренасочвания (гейт за гости, редиректи) */

/** 3.0.5.1 – Гейт за ГОСТИ: не-публичен маршрут → /login/ */
function maxima_is_public_route(): bool {
    // Системни контексти
    if ( is_admin() ) return true;
    if ( wp_doing_ajax() ) return true;
    if ( defined('REST_REQUEST') && REST_REQUEST ) return true;
    if ( defined('DOING_CRON') && DOING_CRON ) return true;
    if ( defined('WP_CLI') && WP_CLI ) return true;

    // Публичен темплейт
    if ( is_page_template('page-templates/shell-public.php') ) {
        return true;
    }

    // Платен темплейт → не е публичен
    if ( is_page_template('page-templates/shell-paid.php') ) {
        return false;
    }

    // Всичко останало по подразбиране не е публично
    return false;
}

function maxima_login_url(): string {
    // ВИНАГИ към custom login, без fallback към wp-login.php
    return home_url( user_trailingslashit('login') ); // https://.../login/
}
function maxima_home_fallback_url(): string {
    return home_url( user_trailingslashit('homemaximalife') ); // https://.../homemaximalife/
}

function maxima_guard_guests_redirect() {
    if ( is_user_logged_in() ) return;
    if ( maxima_is_public_route() ) return;

    $login_url    = maxima_login_url();
    $current_url  = maxima_current_url();

    // Guard: ако вече сме на login пътя → не правим нищо
    if ( maxima_path_only($current_url) === maxima_path_only($login_url) ) {
        return;
    }

    // Запази redirect_to към първоначалния URL
    $target = add_query_arg( 'redirect_to', rawurlencode( $current_url ), $login_url );

    wp_safe_redirect( $target, 302, 'Maxima Guests Gate' );
    exit;
}
add_action( 'template_redirect', 'maxima_guard_guests_redirect', 1 );

/** 3.0.5.2 – Логнат на корена → към /yymmdd-2 (или /homemaximalife) */
function maxima_redirect_logged_on_home() {
    if ( ! is_user_logged_in() ) return;

    // Работим САМО на корена/началната
    $is_root = ( is_front_page() || is_home() );
    if ( ! $is_root ) return;

    // Изчисляваме целта за днес
    $today_slug = maxima_today_slug();
    $today_page = get_page_by_path( $today_slug );

    $dest_url = $today_page
        ? maxima_build_url_from_slug( $today_slug )
        : maxima_home_fallback_url(); // /homemaximalife

    // Guard: ако по някаква причина вече сме на целта
    if ( maxima_path_only( maxima_current_url() ) === maxima_path_only( $dest_url ) ) {
        return;
    }

    wp_safe_redirect( $dest_url, 302, 'Maxima Logged Home' );
    exit;
}
add_action( 'template_redirect', 'maxima_redirect_logged_on_home', 2 );

/** 3.0.5.3 – Login redirect → /yymmdd-2 (или /homemaximalife) */
function maxima_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
    if ( ! is_a( $user, 'WP_User' ) ) {
        return $redirect_to;
    }

    // Изчисляваме slug-а за днешния ден
    $today_slug = maxima_today_slug();
    $today_page = get_page_by_path( $today_slug );

    $dest_url = $today_page
        ? maxima_build_url_from_slug( $today_slug )
        : maxima_home_fallback_url(); // /homemaximalife

    return $dest_url;
}
add_filter( 'login_redirect', 'maxima_login_redirect', 10, 3 );

/** 3.0.5.4 – Logout redirect → винаги към /login/ */
function maxima_force_logout_redirect( $redirect_to, $requested_redirect_to, $user ) {
    return maxima_login_url();
}
add_filter( 'logout_redirect', 'maxima_force_logout_redirect', 10, 3 );

/** 3.0.5.5 – Помощно: URL за logout с правилен redirect */
function maxima_logout_url(): string {
    return wp_logout_url( maxima_login_url() );
}

/* 3.0.6 – Featured Audio метабокс за страници (избор на аудио) */
add_action('add_meta_boxes', 'maxima_add_audio_metabox');
function maxima_add_audio_metabox() {
    add_meta_box(
        'maxima_audio_box',
        __('Музика към страницата', 'maxima'),
        'maxima_audio_metabox_cb',
        ['page'], // добави 'post' или CPT ако искаш
        'side',
        'default'
    );
}

function maxima_audio_metabox_cb($post){
    wp_nonce_field('maxima_audio_save', 'maxima_audio_nonce');
    $audio = get_post_meta($post->ID, 'mx_audio', true);
    $has   = !empty($audio);
    ?>
    <div id="mx-audio-meta" class="mx-meta">
      <input type="hidden" id="mx_audio" name="mx_audio" value="<?php echo esc_attr($audio); ?>">
      <p>
        <button type="button" class="button button-primary" id="mx_audio_select">
          <?php echo $has ? esc_html__('Смени аудио', 'maxima') : esc_html__('Избери аудио', 'maxima'); ?>
        </button>
        <?php if ($has): ?>
          <button type="button" class="button" id="mx_audio_remove" style="margin-left:6px;"><?php echo esc_html__('Премахни', 'maxima'); ?></button>
        <?php endif; ?>
      </p>
      <p id="mx_audio_preview" style="margin:8px 0;">
        <?php if ($has): ?>
          <a href="<?php echo esc_url($audio); ?>" target="_blank" rel="noopener">
            <?php echo esc_html( basename( parse_url($audio, PHP_URL_PATH) ) ); ?>
          </a>
          <audio controls style="width:100%; margin-top:6px;">
            <source src="<?php echo esc_url($audio); ?>" type="audio/mpeg">
          </audio>
        <?php else: ?>
          <em style="color:#666;"><?php echo esc_html__('Няма избрано аудио', 'maxima'); ?></em>
        <?php endif; ?>
      </p>
    </div>
    <script>
    jQuery(function($){
      let frame;
      $('#mx_audio_select').on('click', function(e){
        e.preventDefault();
        if (frame) { frame.open(); return; }
        frame = wp.media({
          title: 'Избери аудио файл',
          library: { type: 'audio' },
          button: { text: 'Ползвай този файл' },
          multiple: false
        });
        frame.on('select', function(){
          const file = frame.state().get('selection').first().toJSON();
          $('#mx_audio').val(file.url);
          $('#mx_audio_preview').html(
            '<a href="'+file.url+'" target="_blank" rel="noopener">'+(file.filename || 'audio')+'</a>' +
            '<audio controls style="width:100%; margin-top:6px;"><source src="'+file.url+'" type="'+(file.mime||'audio/mpeg')+'"></audio>'
          );
          if (!$('#mx_audio_remove').length) {
            $('<button type="button" class="button" id="mx_audio_remove" style="margin-left:6px;">Премахни</button>')
              .insertAfter('#mx_audio_select').on('click', removeAudio);
          }
          $('#mx_audio_select').text('Смени аудио');
        });
        frame.open();
      });
      function removeAudio(e){
        e && e.preventDefault();
        $('#mx_audio').val('');
        $('#mx_audio_preview').html('<em style="color:#666;">Няма избрано аудио</em>');
        $('#mx_audio_select').text('Избери аудио');
        $('#mx_audio_remove').remove();
      }
      $('#mx_audio_remove').on('click', removeAudio);
    });
    </script>
    <?php
}

add_action('save_post_page', 'maxima_save_audio_meta');
function maxima_save_audio_meta($post_id){
    if ( !isset($_POST['maxima_audio_nonce']) || !wp_verify_nonce($_POST['maxima_audio_nonce'], 'maxima_audio_save') ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( !current_user_can('edit_post', $post_id) ) return;

    if ( isset($_POST['mx_audio']) ) {
        $v = trim( sanitize_text_field( $_POST['mx_audio'] ) );
        if ($v === '') delete_post_meta($post_id, 'mx_audio');
        else update_post_meta($post_id, 'mx_audio', esc_url_raw($v));
    }
}

add_action('admin_enqueue_scripts', 'maxima_enqueue_media_for_audio_metabox');
function maxima_enqueue_media_for_audio_metabox($hook){
    if ($hook === 'post.php' || $hook === 'post-new.php') {
        wp_enqueue_media(); // зарежда Media Library модала
    }
}

/* 3.0.7 – YouTube oEmbed: force enablejsapi + playsinline + origin (вкл. youtube-nocookie) */
add_filter('embed_oembed_html', function($html, $url, $attr, $post_id){
    // Приемаме YouTube, YouTube short и YouTube-nocookie
    $is_youtube = (
        (strpos($html, 'youtube.com') !== false) ||
        (strpos($html, 'youtu.be') !== false) ||
        (strpos($html, 'youtube-nocookie.com') !== false)
    );
    if (!$is_youtube) {
        return $html;
    }

    // Хващаме src от първия <iframe ... src="...">
    if (!preg_match('~<iframe[^>]+src=["\']([^"\']+)~i', $html, $m)) {
        return $html;
    }
    $src = html_entity_decode($m[1], ENT_QUOTES); // ако дойде с &amp;

    // Разбиваме URL и query параметрите
    $parts = wp_parse_url($src);
    if (empty($parts['host'])) {
        return $html; // неочаквана структура – не пипаме
    }

    $q = [];
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $q);
    }

    // Задължителните параметри за JS API и коректно вграждане
    $q['enablejsapi'] = '1';
    $q['playsinline'] = '1';
    // уважаваме вече подадена стойност за rel, но по подразбиране 0
    if (!isset($q['rel'])) {
        $q['rel'] = '0';
    }
    // origin трябва да е пълният origin на сайта (YouTube приема и home URL)
    if (empty($q['origin'])) {
        $q['origin'] = home_url('/');
    }

    // Сглобяваме обратно src
    $new_src =
        (isset($parts['scheme']) ? $parts['scheme'].'://' : '') .
        ($parts['host'] ?? '') .
        (isset($parts['port']) ? ':'.$parts['port'] : '') .
        ($parts['path'] ?? '') .
        '?'. http_build_query($q, '', '&') .
        (isset($parts['fragment']) ? '#'.$parts['fragment'] : '');

    // Подменяме само src атрибута, запазвайки останалия iframe
    $safe_new_src = esc_url($new_src);
    $html = str_replace($m[1], $safe_new_src, $html);

    return $html;
}, 10, 4);

/* 3.0.9 – Custom Fonts: Raleway (global) + TYScript (.handwriting) */
add_action('wp_enqueue_scripts', function () {
    // 1) Raleway от Google (глобален)
    wp_enqueue_style(
        'maxima-fonts-google',
        'https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700&display=swap',
        [],
        null
    );

    // 2) Локален TYScript – стабилен URL дори при child theme
    $ty_url = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/fonts/TYScript-Rg.woff2';

    // 3) Инлайн CSS: @font-face + глобален body + непробиваем .handwriting
    $css = "
@font-face{
  font-family:'TYScript';
  src:url('{$ty_url}') format('woff2');
  font-weight:400;
  font-style:normal;
  font-display:swap;
}
/* Глобален шрифт за сайта */
body{ font-family:'Raleway',sans-serif; }
/* Акцентен клас – и върху децата, за да не може да бъде пренаписан от h1/h2 и т.н. */
.handwriting,
.handwriting *{
  font-family:'TYScript',cursive !important;
  font-weight:400 !important;
}
";
    // Вързваме към основния стил; при нужда – регистрираме временен handle
    if ( wp_style_is('maxima-style','enqueued') ) {
        wp_add_inline_style('maxima-style', $css);
    } else {
        wp_register_style('maxima-inline', false);
        wp_enqueue_style('maxima-inline');
        wp_add_inline_style('maxima-inline', $css);
    }
}, 20);

/* (по желание) Preload на локалния шрифт за по-бързо рисуване */
add_action('wp_head', function () {
    $ty_url = trailingslashit( get_stylesheet_directory_uri() ) . 'assets/fonts/TYScript-Rg.woff2';
    echo '<link rel="preload" href="'.esc_url($ty_url).'" as="font" type="font/woff2" crossorigin>';
}, 1);
// *4.1 === Progress Circle (Gutenberg block) ===
add_action('enqueue_block_editor_assets', function () {
    $dir = get_stylesheet_directory();
    $uri = get_stylesheet_directory_uri();
    wp_enqueue_script(
        'maxima-progress-circle',
        $uri . '/blocks/progress-circle/index.js',
        ['wp-blocks','wp-element','wp-components','wp-block-editor','wp-i18n'],
        filemtime($dir . '/blocks/progress-circle/index.js'),
        true
    );
    wp_enqueue_style(
        'maxima-progress-circle-editor',
        $uri . '/blocks/progress-circle/editor.css',
        [],
        filemtime($dir . '/blocks/progress-circle/editor.css')
    );
});
add_action('wp_enqueue_scripts', function () {
    $dir = get_stylesheet_directory();
    $uri = get_stylesheet_directory_uri();
    wp_enqueue_style(
        'maxima-progress-circle',
        $uri . '/blocks/progress-circle/style.css',
        [],
        filemtime($dir . '/blocks/progress-circle/style.css')
    );
});

//* 4.2 корекция на Gutenberg - блоковете и колоните да се виждат (сива линия)
add_action('after_setup_theme', function () {
    // Активира поддръжка на редакторски стилове
    add_theme_support('editor-styles');

    // Зарежда твоя файл вътре в Gutenberg редактора
    add_editor_style('gutenberg-outlines.css');
});
/* ============================================================
 * БЛОК 4.3 – Daily Interactive Checklist (PHP част)
 * ------------------------------------------------------------
 * Подблок 4.3.1 – Регистрация и enqueue
 * Подблок 4.3.2 – AJAX: зареждане на отметки (load)
 * Подблок 4.3.3 – AJAX: запис на отметки (save)
 * Подблок 4.3.4 – Помощни функции
 * ============================================================ */

if ( ! defined('ABSPATH') ) { exit; }

/* ---------- Подблок 4.3.1 – Регистрация и enqueue ---------- */
add_action('init', function () {

    $handle = 'mx-dic';
    $src    = get_stylesheet_directory_uri() . '/blocks/daily-interactive-checklist/index.js';

    wp_register_script(
        $handle,
        $src,
        [ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-block-editor', 'wp-components', 'wp-i18n', 'jquery' ],
        filemtime( get_stylesheet_directory() . '/blocks/daily-interactive-checklist/index.js' ),
        true
    );

    wp_localize_script($handle, 'MX_DIC', [
        'ajaxurl'   => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('mx_dic_nonce'),
        'userId'    => get_current_user_id(),
        'isLogged'  => is_user_logged_in(),
    ]);

    register_block_type( 'mx/daily-interactive-checklist', [
        'editor_script' => $handle,
        'script'        => $handle,
    ]);
});


/* ---------- Подблок 4.3.2 – AJAX: зареждане на отметки (load) ---------- */
add_action('wp_ajax_mx_dic_load', function () {

    if ( ! is_user_logged_in() ) {
        wp_send_json_error([ 'msg' => 'Not logged in' ], 401);
    }
    check_ajax_referer('mx_dic_nonce', 'nonce');

    $uid = isset($_POST['uid']) ? sanitize_text_field( wp_unslash($_POST['uid']) ) : '';
    if ( $uid === '' ) {
        wp_send_json_error([ 'msg' => 'Missing uid' ], 400);
    }

    $key   = 'mx_dic_' . $uid;
    $value = get_user_meta( get_current_user_id(), $key, true );

    if ( ! is_array($value) ) {
        $value = [ 'left' => [], 'right' => [] ];
    }
    wp_send_json_success( $value );
});

/* ---------- Подблок 4.3.3 – AJAX: запис на отметки (save) ---------- */
add_action('wp_ajax_mx_dic_save', function () {

    if ( ! is_user_logged_in() ) {
        wp_send_json_error([ 'msg' => 'Not logged in' ], 401);
    }
    check_ajax_referer('mx_dic_nonce', 'nonce');

    $uid  = isset($_POST['uid'])  ? sanitize_text_field( wp_unslash($_POST['uid']) )  : '';
    $data = isset($_POST['data']) ? wp_unslash($_POST['data']) : null;

    if ( $uid === '' || ! is_array($data) ) {
        wp_send_json_error([ 'msg' => 'Bad payload' ], 400);
    }

    $clean = mx_dic_sanitize_payload($data);

    $key = 'mx_dic_' . $uid;
    update_user_meta( get_current_user_id(), $key, $clean );

    wp_send_json_success([ 'saved' => true ]);
});

/* ---------- Подблок 4.3.4 – Помощни функции ---------- */
function mx_dic_sanitize_payload( $data ) {
    $out = [ 'left' => [], 'right' => [] ];
    foreach ( ['left','right'] as $side ) {
        if ( isset($data[$side]) && is_array($data[$side]) ) {
            $out[$side] = array_values( array_filter( array_map( static function($v){
                $v = is_string($v) ? $v : '';
                return preg_match('/^[a-z0-9\-]{2,}$/i', $v) ? sanitize_text_field($v) : '';
            }, $data[$side] ) ) );
        }
    }
    return $out;
}
