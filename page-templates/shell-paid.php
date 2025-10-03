<?php
/**
 * Template Name: Paid Shell
 * Description: Шаблон за платени (регистрирани) страници
 */
?>

<?php
/*
Template Name: Maxima Paid Shell
Description: Обвивка за платено съдържание (PMPro) – фон от meta/featured, хедър с лого/звук/потребител, 3 „папки“, двустепенни менюта, glass съдържание и унифициран футър.
*/
if ( ! defined('ABSPATH') ) { exit; }

/* ============================================================
   ИНДЕКС НА БЛОКОВЕТЕ
   ------------------------------------------------------------
   БЛОК 1.0 – Достъп и контекст
       1.1 – Само за логнати
       1.2 – Потребител (име, аватар)
       1.3 – Музика (mx_audio → fallback /assets/audio/login.mp3)
       1.4 – Фон (mx_bg → Featured Image → none)
       1.5 – Палитра по „Свят“ (world-1/2/3 по URL)
       1.6 – Помощници „ДНЕС“ по потребителска TZ (cookie mx_tz)
   БЛОК 2.0 – HEAD (+ CSS: фон, хедър, папки, менюта, glass, аудио)
   БЛОК 3.0 – Хедър (лого • контрол на звука • потребителско меню)
   БЛОК 4.0 – Папки/светове (tabs)
   БЛОК 5.0 – Менюта (основно + второ ниво + мобилен панел)
   БЛОК 6.0 – Съдържание (glassmorphism)
   БЛОК 7.0 – Футър
   БЛОК 8.0 – JS
       8.1 – Потребителско меню и мобилен бургер
       8.2 – Звук: play/pause, volume, запомняне, авто-пауза при външен звук
       8.3 – Падащи подменюта (трите „папки“)
   ============================================================ */

/* -------------------------
   БЛОК 1.0 – Достъп и контекст
-------------------------- */

// 1.1 – Само за логнати
if ( ! is_user_logged_in() ) {
  $login_url = get_page_by_path('login') ? site_url('/login/') : wp_login_url( get_permalink() );
  wp_safe_redirect($login_url); exit;
}

// 1.2 – Потребител
$current_user = wp_get_current_user();
$first_name   = $current_user->first_name ?: $current_user->display_name;
$avatar_html  = get_avatar( $current_user->ID, 64, '', $first_name, ['class' => 'mx-avatar'] );

// 1.3 – Музика
$audio_src = trim((string) get_post_meta(get_the_ID(), 'mx_audio', true));
if ( empty($audio_src) ) {
  $audio_src = get_stylesheet_directory_uri() . '/assets/audio/login.mp3';
}

// 1.4 – Фон
$mx_bg_meta     = get_post_meta(get_the_ID(), 'mx_bg', true);
$mx_bg_featured = get_the_post_thumbnail_url(get_the_ID(), 'full');
$mx_bg_url      = $mx_bg_meta ? esc_url($mx_bg_meta) : ($mx_bg_featured ? esc_url($mx_bg_featured) : '');

// 1.5 – Палитра по свят
$req_uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$world = 'world-1';
if (preg_match('~(^|/)homemaximadivine(/|$)~', $req_uri)) {
    $world = 'world-2';
} elseif (preg_match('~(^|/)homemaximaboss(/|$)~', $req_uri)) {
    $world = 'world-3';
}
$color_bg_tabs   = '#f5f2ed';
$color_bg_active = '#ffffff';
$world_palette = [
  'world-1' => ['accent' => '#6b4eff'],
  'world-2' => ['accent' => '#00a58a'],
  'world-3' => ['accent' => '#e15c84'],
];
$accent = $world_palette[$world]['accent'] ?? '#0e3a46';

/* 1.6 – Помощници „ДНЕС“ по потребителска TZ (cookie mx_tz) */
if (!function_exists('mx_user_timezone')) {
  function mx_user_timezone(): DateTimeZone {
      $tz_cookie = isset($_COOKIE['mx_tz']) ? trim($_COOKIE['mx_tz']) : '';
      try { if ($tz_cookie !== '') return new DateTimeZone($tz_cookie); } catch (Throwable $e) {}
      return wp_timezone();
  }
}
if (!function_exists('mx_today_slug')) {
  function mx_today_slug(): string {
      try {
          $now = new DateTimeImmutable('now', mx_user_timezone());
          return strtolower($now->format('ymd') . '-2');
      } catch (Throwable $e) {
          return strtolower(wp_date('ymd') . '-2');
      }
  }
}
if (!function_exists('mx_today_url')) {
  function mx_today_url(string $fallback='/homemaximalife/'): string {
      $slug = mx_today_slug();
      $page = get_page_by_path($slug);
      return $page ? get_permalink($page->ID) : site_url($fallback);
  }
}

?><!doctype html>
<html <?php language_attributes(); ?> class="mx-paid-shell <?php echo esc_attr($world); ?>">
<head>
<!-- БЛОК 2.0 – HEAD -->
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
<style>
  :root{
    --mx-bg-tabs: <?php echo esc_html($color_bg_tabs); ?>;
    --mx-bg-active: <?php echo esc_html($color_bg_active); ?>;
    --mx-accent: <?php echo esc_html($accent); ?>;
    --mx-brand:#0e3a46;
    --mx-ink:#111;
    --mx-muted:#666;
    --mx-white:#fff;

    --mx-header-h-desktop: 80px;
    --mx-tabs-h-desktop:   56px;

    --mx-header-h: var(--mx-header-h-desktop);
    --mx-tabs-h:   var(--mx-tabs-h-desktop);
  }

  .mx-header{
    position: fixed !important;
    top: 0; left: 0; right: 0;
    z-index: 1000;
    background: var(--mx-bg-tabs);
    -webkit-backdrop-filter: saturate(120%) blur(6px);
    backdrop-filter: saturate(120%) blur(6px);
  }


  .mx-worlds{
    position: fixed !important;
    top: var(--mx-header-h);
    left: 0; right: 0;
    z-index: 999;
    background: var(--mx-bg-tabs);
    -webkit-backdrop-filter: saturate(120%) blur(6px);
    backdrop-filter: saturate(120%) blur(6px);
  }
  body{ padding-top: calc(var(--mx-header-h) + var(--mx-tabs-h)) !important; }

  html,body{margin:0;padding:0; width:100%; overflow-x:hidden;body {
  font-size: 18px;   /* нов размер по подразбиране */
  line-height: 1.6;  /* по-четлив интервал между редовете */
  font-family: 'Raleway', sans-serif; /* ако това е твоят основен шрифт */
}
}
  main{ width:100%; max-width:100%; margin:0 auto; padding:0 16px; box-sizing:border-box; }
  main > *{ max-width:100%; margin-left:auto; margin-right:auto; }
  main img, main video, main iframe, main table{ max-width:100%; height:auto; }
  a{color:var(--mx-brand); text-decoration:none}
  a:hover{text-decoration:none}
  .wp-block-embed__wrapper > a[href*="youtu"],
  .wp-block-embed__wrapper > a[href*="youtube"],
  .wp-block-embed__wrapper > a[href*="vimeo"]{ display:none !important; }
  .mx-navwrap, .mx-worlds{ overflow: visible; }

  #mx-bg{ position:fixed; inset:0; z-index:-1; background:#f5f2ed; }
  #mx-bg::before{ content:""; position:absolute; inset:0; background:var(--mx-img, none) center/cover no-repeat; }
  #mx-bg::after{ content:""; position:absolute; inset:0; background: rgba(0,0,0,0.08); }

  .mx-header{
    position:sticky; top:0; z-index:50;
    display:flex; align-items:center; gap:16px;
    height:var(--mx-header-h); padding:0 18px;
    background:var(--mx-bg-tabs);
    border-bottom:1px solid #ddd !important;
    -webkit-backdrop-filter:saturate(120%) blur(6px); backdrop-filter:saturate(120%) blur(6px);
  }
  .mx-logo{display:flex; align-items:center; gap:10px; font-weight:800; letter-spacing:.5px;}
  .mx-logo img{height:calc(var(--mx-header-h) - 24px); width:auto}

  .bgm-wrap{display:flex; gap:12px; align-items:center; user-select:none; margin-left:12px}
  .bgm-toggle{display:grid; grid-template-columns:1fr 1fr; width:100px; height:30px; padding:4px; border-radius:999px; background:rgba(255,255,255,.1); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); box-shadow:0 4px 12px rgba(0,0,0,.25);}
  .bgm-seg{border:0; cursor:pointer; margin:0; border-radius:calc(999px - 4px); background:linear-gradient(180deg,#f7f9fb,#e9edf1); box-shadow:inset 1px 1px 2px rgba(0,0,0,.08), inset -1px -1px 2px rgba(255,255,255,.9); transition:transform .15s ease, background .2s ease, opacity .2s ease; display:grid; place-items:center; font-size:16px; color:#6b7280; outline:none;}
  .bgm-seg.is-active{ background:linear-gradient(180deg,#fff,#eef2f6); color:var(--mx-accent); }
  .bgm-seg:not(.is-active){ opacity:.6; }
  .bgm-volume{display:grid; grid-template-columns:auto 1fr auto; align-items:center; gap:6px; width:120px; height:30px; padding:6px 10px; border-radius:999px; background:rgba(255,255,255,.2); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); box-shadow:0 4px 12px rgba(0,0,0,.25); font-size:12px;}
  .bgm-volume .vol-icon{ line-height:1; font-size:14px; }
  .bgm-volume input[type="range"]{ width:100%; -webkit-appearance:none; appearance:none; background:transparent; cursor:pointer; }
  .bgm-volume input[type="range"]::-webkit-slider-runnable-track{ height:4px; border-radius:2px; background:linear-gradient(90deg,#ccc,#888); }
  .bgm-volume input[type="range"]::-moz-range-track{ height:4px; border-radius:2px; background:linear-gradient(90deg,#ccc,#888); }
  .bgm-volume input[type="range"]::-webkit-slider-thumb{ -webkit-appearance:none; appearance:none; width:12px; height:12px; border-radius:50%; background:#fff; border:1px solid #bbb; box-shadow:1px 1px 3px rgba(0,0,0,.3); margin-top:-4px; }
  .bgm-volume input[type="range"]::-moz-range-thumb{ width:12px; height:12px; border-radius:50%; background:#fff; border:1px solid #bbb; box-shadow:1px 1px 3px rgba(0,0,0,.3); }

  .mx-user{margin-left:auto; display:flex; align-items:center; gap:10px; position:relative}
  .mx-user img,.mx-user .avatar,.mx-user .mx-avatar{ width:36px; height:36px; border-radius:999px; object-fit:cover; display:block; }
  .mx-user__name{font-weight:700}
  .mx-user__menu{position:absolute; right:0; top:calc(100% + 8px); background:#fff; border:1px solid rgba(0,0,0,.06); border-radius:12px; padding:6px; box-shadow:0 12px 30px rgba(0,0,0,.12); display:none; min-width:200px}
  .mx-user__menu a{display:block; padding:10px 12px; border-radius:8px}
  .mx-user__menu a:hover{background:#f7f7f8; text-decoration:none}
  .mx-user.open .mx-user__menu{display:block}

  .mx-worlds{
    position: sticky;
    top: var(--mx-header-h);
    z-index: 45;
    background:var(--mx-bg-tabs);
    padding:0 12px;
    display:flex; gap:14px; align-items:flex-end;
    border-bottom:0;
    -webkit-backdrop-filter:saturate(120%) blur(6px);
    backdrop-filter:saturate(120%) blur(6px);
  }
  .mx-world{
    display:inline-flex; align-items:center; justify-content:center;
    padding:12px 18px; border-radius:0px 22px 0 0;
    background:var(--mx-bg-tabs); font-weight:800; color:#333; opacity:.75;
      }
      @media (max-width: 768px){
  .mx-worlds .mx-world {
    font-size: 0.85rem;  /* ~14px */
  }
}
  .mx-world--active{
    background:var(--mx-bg-active); opacity: 0.9; border-bottom:0; box-shadow:none; position:relative; z-index:2;
  }

  /* контейнер за таб + бургер + подменю (и за трите свята) */
  .mx-worldwrap{ position:relative; display:inline-flex; align-items:flex-end; gap:8px; }
  .mx-folder-burger{ background:transparent; border:0; font-size:18px; line-height:1; padding:6px 8px; cursor:pointer; opacity:.9; position:absolute; right:10px; bottom:8px; z-index:10; }
  .mx-world--active{ padding-right: 42px; }
  .mx-worldwrap .mx-folder-burger{ display:none; }
  .mx-worldwrap .mx-world--active + .mx-folder-burger{ display:inline-block; }

  .mx-submenu[hidden]{ display:none !important; }
  .mx-submenu{
    position:absolute; top:100%; left:0;
    min-width:220px; margin:8px 0 0; padding:8px 0;
    background:#fff; border-radius:10px; box-shadow:0 10px 30px rgba(0,0,0,.12);
    z-index:1000; list-style:none;
  }
  .mx-submenu li{ margin:0; }
  .mx-submenu a{ display:block; padding:10px 14px; text-decoration:none; color:#222; font-weight:600; }
  .mx-submenu a:hover{ background:rgba(0,0,0,.05); }

  .mx-navwrap{background:transparent;}
  .mx-nav{display:flex; gap:18px; padding:12px 18px; align-items:center; flex-wrap:wrap}
  .mx-nav a{font-weight:700; color:#222;}
  .mx-nav a:hover{color:var(--mx-accent); text-decoration:none}
  .mx-subnav{margin-left:auto; display:flex; gap:14px; align-items:center}
  .mx-subnav a{color:#444; font-weight:600}
  .mx-burger{display:none; margin-left:auto; border:0; background:transparent; font-size:28px; line-height:1; cursor:pointer}
  .mx-subpanel{display:none; position:fixed; top:var(--mx-header-h); left:0; right:0; bottom:0; background:#fff; padding:18px; overflow:auto}
  .mx-subpanel.open{display:block}
  .mx-subpanel .mx-subnav{flex-direction:column; align-items:flex-start}
  .mx-subpanel .mx-subnav a{padding:10px 0; width:100%}

  .mx-container { width: 90%; margin-inline: auto; }
  .wp-block-embed.is-type-video .wp-block-embed__wrapper,
  .wp-block-embed-youtube .wp-block-embed__wrapper { width: 100%; aspect-ratio: 16 / 9; position: relative; }
  .wp-block-embed.is-type-video .wp-block-embed__wrapper iframe,
  .wp-block-embed-youtube .wp-block-embed__wrapper iframe { width: 100%; height: 100%; position: absolute; inset: 0; display: block; }
  .wp-block-embed.is-type-video .wp-block-embed__wrapper > a{ display:none !important; }
  .wp-block-embed:not(.is-type-video) iframe { height: auto; max-height: none; }
  .mx-container iframe, .mx-container .wp-block-embed__wrapper, .mx-container .wp-block-embed { display: block; margin: 0 auto; max-width: 100%; width: 100%; }
  @supports not (aspect-ratio: 1) {
    .wp-block-embed.is-type-video .wp-block-embed__wrapper,
    .wp-block-embed-youtube .wp-block-embed__wrapper { height: 0; padding-bottom: 56.25%; }
  }

  .mx-footer{ margin-top:40px; padding:24px 18px; background:var(--mx-bg-tabs); border-top:1px solid rgba(0,0,0,.06); color:#333 }
  .mx-footer__inner{max-width:1100px; margin:0 auto; display:grid; gap:16px}
  @media(min-width:800px){ .mx-footer__inner{grid-template-columns:2fr 1fr 1fr 1fr} }
  .mx-footer h4{margin:.2rem 0 .4rem; font-size:14px; text-transform:uppercase; letter-spacing:.08em; color:#111}
  .mx-footer ul{list-style:none; padding:0; margin:0}
  .mx-footer li{margin:.25rem 0}
  .mx-footer a{color:#333}
  .mx-footer a:hover{color:var(--mx-accent); text-decoration:none}

/* Подблок 2.X – Глобално скалиране за десктоп (Firefox/Chrome OK) */
@media (min-width:1025px){
  html.mx-paid-shell body{ font-size:30px !important; line-height:1.75 !important; }
  .handwriting, .handwriting *{ font-size:80px !important; line-height:1.8 !important; }
}

</style>
</head>
<body <?php body_class('mx-paid'); ?>>

<?php if ( !empty($mx_bg_url) ) : ?>
  <div id="mx-bg" style="--mx-img:url('<?php echo $mx_bg_url; ?>');"></div>
<?php endif; ?>

<header class="mx-header" role="banner">
<?php $logo_url = mx_today_url(); ?>
<div class="mx-logo">
  <a href="<?php echo esc_url($logo_url); ?>">
    <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/logo.png' ); ?>" alt="Maxima Logo" />
  </a>
</div>

  <div class="bgm-wrap">
    <div id="bgmToggle" class="bgm-toggle" role="group" aria-label="Звук">
      <button id="bgmOn"  class="bgm-seg"          type="button" aria-pressed="false" aria-label="Включен звук">🔊</button>
      <button id="bgmOff" class="bgm-seg is-active" type="button" aria-pressed="true"  aria-label="Изключен звук">🔇</button>
    </div>
    <div class="bgm-volume" aria-label="Сила на звука">
      <span class="vol-icon" aria-hidden="true">🔈</span>
      <input id="bgmVolume" type="range" min="0" max="100" step="1" value="7" aria-valuemin="0" aria-valuemax="100" aria-valuenow="7" />
      <span class="vol-icon" aria-hidden="true">🔊</span>
    </div>
  </div>

  <div class="mx-user" id="mx-user">
    <?php echo $avatar_html; ?>
    <div class="mx-user__name"><?php echo esc_html($first_name); ?></div>
    <div class="mx-user__menu" id="mx-user-menu" aria-label="Потребителско меню">
      <?php $profile_url = function_exists('pmpro_url') ? pmpro_url('account') : admin_url('profile.php'); ?>
      <a href="<?php echo esc_url($profile_url); ?>">Моят профил</a>
      <a href="<?php echo esc_url(wp_logout_url( home_url('/') )); ?>">Изход</a>
    </div>
  </div>
</header>

<audio id="loginMusic" preload="auto" muted loop playsinline crossorigin="anonymous">
  <source src="<?php echo esc_url($audio_src); ?>" type="audio/mpeg">
</audio>

<nav class="mx-worlds" aria-label="Светове">
  <?php
   $world1_url = mx_today_url();
   $world_links = [
     'world-1' => ['label'=>'Maxima Life',   'url'=>$world1_url],
     'world-2' => ['label'=>'Maxima Divine', 'url'=>site_url('/homemaximadivine/')],
     'world-3' => ['label'=>'Maxima Boss',   'url'=>site_url('/homemaximaboss/')],
   ];

   // LIFE
   $active = ($world === 'world-1') ? ' mx-world--active' : '';
   echo '<span class="mx-worldwrap">';
   echo '<a class="mx-world'.$active.'" href="'.esc_url($world_links['world-1']['url']).'">Maxima Life</a>';
   echo '<button class="mx-folder-burger" type="button" aria-label="Отвори Maxima Life" aria-controls="mx-life-submenu" aria-expanded="false">☰</button>';
   wp_nav_menu([
     'theme_location' => 'maxima_life_menu',
     'container'      => false,
     'fallback_cb'    => '__return_empty_string',
     'depth'          => 2,
     'items_wrap'     => '<ul id="mx-life-submenu" class="mx-submenu" hidden>%3$s</ul>',
     'menu_class'     => '',
   ]);
   echo '</span>';

   // DIVINE
   $active = ($world === 'world-2') ? ' mx-world--active' : '';
   echo '<span class="mx-worldwrap">';
   echo '<a class="mx-world'.$active.'" href="'.esc_url($world_links['world-2']['url']).'">Maxima Divine</a>';
   echo '<button class="mx-folder-burger" type="button" aria-label="Отвори Maxima Divine" aria-controls="mx-divine-submenu" aria-expanded="false">☰</button>';
   wp_nav_menu([
     'theme_location' => 'maxima_divine_menu',
     'container'      => false,
     'fallback_cb'    => '__return_empty_string',
     'depth'          => 2,
     'items_wrap'     => '<ul id="mx-divine-submenu" class="mx-submenu" hidden>%3$s</ul>',
     'menu_class'     => '',
   ]);
   echo '</span>';

   // BOSS
   $active = ($world === 'world-3') ? ' mx-world--active' : '';
   echo '<span class="mx-worldwrap">';
   echo '<a class="mx-world'.$active.'" href="'.esc_url($world_links['world-3']['url']).'">Maxima Boss</a>';
   echo '<button class="mx-folder-burger" type="button" aria-label="Отвори Maxima Boss" aria-controls="mx-boss-submenu" aria-expanded="false">☰</button>';
   wp_nav_menu([
     'theme_location' => 'maxima_boss_menu',
     'container'      => false,
     'fallback_cb'    => '__return_empty_string',
     'depth'          => 2,
     'items_wrap'     => '<ul id="mx-boss-submenu" class="mx-submenu" hidden>%3$s</ul>',
     'menu_class'     => '',
   ]);
   echo '</span>';
  ?>
</nav>

<div class="mx-navwrap">
  <div class="mx-nav">
    <div class="mx-primary">
      <?php
        wp_nav_menu([
          'theme_location' => 'world-primary',
          'container'      => false,
          'menu_class'     => 'mx-primary__menu',
          'fallback_cb'    => '__return_empty_string',
          'depth'          => 1,
          'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
        ]);
      ?>
    </div>

    <div class="mx-subnav" id="mx-subnav">
      <?php
        wp_nav_menu([
          'theme_location' => 'world-secondary',
          'container'      => false,
          'menu_class'     => 'mx-subnav__menu',
          'fallback_cb'    => '__return_empty_string',
          'depth'          => 1,
          'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
        ]);
      ?>
    </div>

    <button class="mx-burger" id="mx-burger" aria-expanded="false" aria-controls="mx-subpanel">☰</button>
  </div>

  <div class="mx-subpanel" id="mx-subpanel" aria-hidden="true">
    <h3>Раздели</h3>
    <div class="mx-subnav">
      <?php
        wp_nav_menu([
          'theme_location' => 'world-secondary',
          'container'      => false,
          'menu_class'     => 'mx-subnav__menu',
          'fallback_cb'    => '__return_empty_string',
          'depth'          => 2,
          'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
        ]);
      ?>
    </div>
  </div>
</div>

<main class="mx-container" role="main">
  <article class="mx-glass">
    <?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?>
  </article>
</main>

<footer class="mx-footer">
  <div class="mx-footer__inner">
    <div>
      <h4><?php bloginfo('name'); ?></h4>
      <p>Мото: Вдъхновено развитие и резултати без бърнаут.</p>
      <p><a href="<?php echo esc_url(site_url('/about/')); ?>">За нас</a></p>
      <p>Супорт: <a href="mailto:support@next-lvls.com">support@next-lvls.com</a></p>
    </div>
    <div>
      <h4>Политики</h4>
      <?php wp_nav_menu(['theme_location'=>'legal','container'=>false,'menu_class'=>'mx-legal','fallback_cb'=>'__return_empty_string','depth'=>1,'items_wrap'=>'<ul class="%2$s">%3$s</ul>']); ?>
    </div>
    <div>
      <h4>Документи</h4>
      <?php wp_nav_menu(['theme_location'=>'docs','container'=>false,'menu_class'=>'mx-docs','fallback_cb'=>'__return_empty_string','depth'=>1,'items_wrap'=>'<ul class="%2$s">%3$s</ul>']); ?>
    </div>
    <div>
      <h4>ЧЗВ</h4>
      <?php wp_nav_menu(['theme_location'=>'faq','container'=>false,'menu_class'=>'mx-faq','fallback_cb'=>'__return_empty_string','depth'=>1,'items_wrap'=>'<ul class="%2$s">%3$s</ul>']); ?>
    </div>
  </div>
</footer>

<script>
/* ================================
   БЛОК 8.1 – Потребителско меню + мобилен бургер
================================ */
(function(){
  const user   = document.getElementById('mx-user');
  const burger = document.getElementById('mx-burger');
  const panel  = document.getElementById('mx-subpanel');

  user?.addEventListener('click', () => user.classList.toggle('open'));

  burger?.addEventListener('click', ()=>{
    const open = panel.classList.toggle('open');
    burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    panel.setAttribute('aria-hidden', open ? 'false' : 'true');
  });
})();

/* ================================
   БЛОК 8.2 – Звук: play/pause/volume + авто-пауза при външен звук
   – без агресивно премахване на <source>, само пауза във фонов режим
================================ */
(function () {
  const bgm      = document.getElementById('loginMusic');
  const onBtn    = document.getElementById('bgmOn');
  const offBtn   = document.getElementById('bgmOff');
  const volInput = document.getElementById('bgmVolume');
  if (!bgm || !onBtn || !offBtn || !volInput) return;

  const LS_MUTE = 'paidAudioMuted';
  const LS_VOL  = 'paidAudioVolume';

  // Инициално състояние
  bgm.loop  = true;
  bgm.muted = true;
  bgm.volume = 0.07;
  try {
    const savedVol = localStorage.getItem(LS_VOL);
    if (savedVol !== null) {
      const v = Math.max(0, Math.min(100, Number(savedVol)));
      bgm.volume = v / 100;
      volInput.value = String(v);
      volInput.setAttribute('aria-valuenow', String(v));
    }
  } catch(_) {}

  function setUI(muted){
    onBtn.classList.toggle('is-active', !muted);
    offBtn.classList.toggle('is-active',  muted);
    onBtn.setAttribute('aria-pressed', String(!muted));
    offBtn.setAttribute('aria-pressed', String( muted));
    // sync slider with current volume
    const v = Math.round((isNaN(bgm.volume)?0:bgm.volume)*100);
    volInput.value = String(v);
    volInput.setAttribute('aria-valuenow', String(v));
  }
  setUI(true);

  function setMutePref(m){ try{ localStorage.setItem(LS_MUTE, String(m)); }catch(_){ } }
  function wantsUnmuted(){ try{ return localStorage.getItem(LS_MUTE)==='false'; }catch(_){ return false; } }

  async function unmuteAndPlay({persist=false}={}){
    if (externalPlayers > 0) { setUI(true); if (persist) setMutePref(true); return false; }
    try{
      bgm.muted = false;
      bgm.removeAttribute('muted');
      if (bgm.readyState < 2) bgm.load();
      if (bgm.paused) bgm.currentTime = 0;
      await bgm.play();
      setUI(false);
      if (persist) setMutePref(false);
      return true;
    }catch(_){
      bgm.muted = true; setUI(true);
      if (persist) setMutePref(true);
      return false;
    }
  }
  function pauseBgm({persist=false}={}){
    try{ bgm.pause(); }catch(_){}
    bgm.muted = true;
    setUI(true);
    if (persist) setMutePref(true);
  }

  // Експорт (ако някъде потрябва)
  window.__mxAudioControl = Object.assign(window.__mxAudioControl||{},{
    pauseBgm: ()=>pauseBgm({persist:false}),
    resumeBgmIfAllowed: ()=>{ if (externalPlayers===0 && wantsUnmuted()) unmuteAndPlay({persist:false}); }
  });

  // Контроли
  onBtn.addEventListener('click', async (e)=>{ e.stopPropagation(); await unmuteAndPlay({persist:true}); });
  offBtn.addEventListener('click', (e)=>{ e.stopPropagation(); pauseBgm({persist:true}); });
  volInput.addEventListener('input', (e)=>{
    const v = Math.max(0, Math.min(100, Number(e.target.value||0)));
    bgm.volume = v/100;
    e.target.setAttribute('aria-valuenow', String(v));
    try{ localStorage.setItem(LS_VOL, String(v)); }catch(_){}
  });

  // По-щадящо поведение при невидимост: само пауза
  document.addEventListener('visibilitychange', ()=>{
    if (document.hidden) { try{ bgm.pause(); }catch(_){ } }
  });

  // Първи жест: ако потребителят е „искам звук“, пусни
  if (wantsUnmuted()){
    const first = ()=>{ document.removeEventListener('pointerup', first, true); unmuteAndPlay({persist:false}); };
    document.addEventListener('pointerup', first, {once:true, capture:true});
  } else {
    // опит за „подгряване“, не пречи ако е блокирано
    bgm.play().catch(()=>{});
  }

  // -------- Детекция на външен звук (HTML5 / YouTube / Vimeo) --------
  let externalPlayers = 0;
  const activeHtml5 = new Set();

  function onExternalStart(){
    if (externalPlayers===0) pauseBgm({persist:false});
    externalPlayers++;
  }
  function onExternalStop(){
    externalPlayers = Math.max(0, externalPlayers-1);
    if (externalPlayers===0 && wantsUnmuted()) unmuteAndPlay({persist:false});
  }

  // A) HTML5 video/audio (без нашето аудио)
  document.addEventListener('play', (ev)=>{
    const el = ev.target;
    if (!(el instanceof HTMLMediaElement)) return;
    if (el === bgm) return;
    if (!activeHtml5.has(el)){
      activeHtml5.add(el);
      onExternalStart();
      const cleanup = ()=>{ if (activeHtml5.delete(el)) onExternalStop(); el.removeEventListener('pause', cleanup, true); el.removeEventListener('ended', cleanup, true); };
      el.addEventListener('pause', cleanup, true);
      el.addEventListener('ended', cleanup, true);
    }
  }, true);

  // B) YouTube / Vimeo – postMessage handshake
  const YT_HOSTS = ['youtube.com','youtu.be','youtube-nocookie.com'];
  const VIMEO_HOST = 'vimeo.com';
  const ytRegistered = new WeakSet();
  const vimeoReady   = new WeakSet();

  function ensureYouTubeApiParam(root=document){
    root.querySelectorAll('iframe').forEach(ifr=>{
      const src = (ifr.getAttribute('src')||'').toLowerCase();
      if (!YT_HOSTS.some(h=>src.includes(h))) return;
      try{
        const u = new URL(ifr.src, window.location.origin);
        if (u.searchParams.get('enablejsapi')!=='1') u.searchParams.set('enablejsapi','1');
        u.searchParams.set('playsinline','1');
        if (!u.searchParams.has('origin')) u.searchParams.set('origin', window.location.origin);
        ifr.src = u.toString();
      }catch(_){}
    });
  }
  ensureYouTubeApiParam();

  function registerYouTube(ifr){
    if (!ifr.contentWindow || ytRegistered.has(ifr)) return;
    try{
      const id = ifr.id || (ifr.id = 'yt_ifr_' + Math.random().toString(36).slice(2));
      const cw = ifr.contentWindow;
      cw.postMessage(JSON.stringify({event:'listening', id, channel:'widget'}), '*');
      cw.postMessage(JSON.stringify({event:'command',  func:'addEventListener', args:['onStateChange'], id, channel:'widget'}), '*');
      ytRegistered.add(ifr);
    }catch(_){}
  }
  function registerVimeo(_ifr){ /* готово при 'ready' */ }

  // Първоначален пас
  document.querySelectorAll('iframe').forEach(ifr=>{
    const src = (ifr.getAttribute('src')||'').toLowerCase();
    if (YT_HOSTS.some(h=>src.includes(h))) registerYouTube(ifr);
  });

  // Динамично добавени iframes
  const mo = new MutationObserver(muts=>{
    muts.forEach(m=>m.addedNodes.forEach(n=>{
      if (n.nodeType!==1) return;
      ensureYouTubeApiParam(n);
      n.querySelectorAll?.('iframe').forEach(ifr=>{
        const src = (ifr.getAttribute('src')||'').toLowerCase();
        if (YT_HOSTS.some(h=>src.includes(h))) registerYouTube(ifr);
      });
    }));
  });
  try{ mo.observe(document.documentElement, {childList:true, subtree:true}); }catch(_){}

  // Събития от iframes
  window.addEventListener('message', (e)=>{
    const origin = (e.origin||'').toLowerCase();
    const isYT   = YT_HOSTS.some(h=>origin.includes(h));
    const isVM   = origin.includes(VIMEO_HOST);
    if (!isYT && !isVM) return;

    let data = e.data;
    if (typeof data === 'string') { try{ data = JSON.parse(data); }catch(_){ return; } }
    if (!data) return;

    // YouTube
    if (isYT){
      const ev = data.event || '';
      if (ev === 'onStateChange'){
        const st = Number(data.info);
        if (st === 1) onExternalStart();                 // PLAYING
        else if (st === 0 || st === 2 || st === 5) onExternalStop(); // ENDED/PAUSED/CUED
      }
      // re-register защитно
      document.querySelectorAll('iframe').forEach(ifr=>{
        const src = (ifr.getAttribute('src')||'').toLowerCase();
        if (YT_HOSTS.some(h=>src.includes(h))) registerYouTube(ifr);
      });
    }

    // Vimeo (минимално: play/pause/ended/finish)
    if (isVM){
      const ev = data.event || data.method || '';
      if (ev === 'ready'){
        document.querySelectorAll('iframe').forEach(ifr=>{
          const src = (ifr.getAttribute('src')||'').toLowerCase();
          if (!src.includes(VIMEO_HOST) || !ifr.contentWindow) return;
          if (vimeoReady.has(ifr)) return;
          try{
            const cw = ifr.contentWindow;
            cw.postMessage({method:'addEventListener', value:'play'},   '*');
            cw.postMessage({method:'addEventListener', value:'pause'},  '*');
            cw.postMessage({method:'addEventListener', value:'ended'},  '*');
            cw.postMessage({method:'addEventListener', value:'finish'}, '*');
            vimeoReady.add(ifr);
          }catch(_){}
        });
      } else if (ev === 'play') {
        onExternalStart();
      } else if (ev === 'pause' || ev === 'ended' || ev === 'finish') {
        onExternalStop();
      }
    }
  }, false);

  // „Оптимистично“: клик по YT/Vimeo iframe → пауза
  document.addEventListener('click', (ev)=>{
    const ifr = ev.target.closest?.('iframe');
    if (!ifr) return;
    const src = (ifr.getAttribute('src')||'').toLowerCase();
    if (src.includes('youtube') || src.includes('youtu.be') || src.includes('youtube-nocookie.com') || src.includes('vimeo.com')) {
      pauseBgm({persist:false});
    }
  }, true);
})();

/* ================================
   БЛОК 8.3 – Падащи подменюта (трите „папки“)
================================ */
(function(){
  const wraps  = document.querySelectorAll('.mx-worldwrap');

  function closeAll(){
    document.querySelectorAll('.mx-submenu:not([hidden])').forEach(m => m.hidden = true);
    document.querySelectorAll('.mx-folder-burger[aria-expanded="true"]').forEach(b => b.setAttribute('aria-expanded','false'));
  }

  wraps.forEach(wrap=>{
    const btn  = wrap.querySelector('.mx-folder-burger');
    const menu = wrap.querySelector('.mx-submenu');
    if (!btn || !menu) return;

    btn.addEventListener('click', function(e){
      e.stopPropagation();
      const willOpen = menu.hidden;
      closeAll();
      menu.hidden = !willOpen;
      btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });
  });

  document.addEventListener('click', function(e){
    if (!e.target.closest('.mx-worldwrap')) closeAll();
  });

  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') closeAll();
  });
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
