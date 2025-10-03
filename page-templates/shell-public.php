<?php
/*
Template Name: Maxima Public Shell
Description: Публичен shell (Login / Register / Lost password) – прозрачен хедър/футър, центрирани музикални контроли (60px), glass карта. Работи с всякакво съдържание/шорткъти от PMPro.
*/

/* ============================================================
   ИНДЕКС НА БЛОКОВЕТЕ
   ------------------------------------------------------------
   БЛОК 1.0 – Проверка за логнати + подготовка на фон/аудио
       1.1 – Redirect за логнати
       1.2 – Фоново изображение (mx_bg > Featured Image > fallback)
       1.3 – Аудио URL (mx_audio > /assets/audio/login.mp3)

   БЛОК 2.0 – Фонова картинка (HTML + CSS)
       2.1 – HTML контейнер #mx-bg
       2.2 – CSS: cover + overlay

   БЛОК 3.0 – Контроли за музика (прозрачен „хедър“)
       3.1 – HTML: .bgm-wrap (ON/OFF + плъзгач + говорители)
       3.2 – CSS: стил на контролите (top: 60px, ред: ON → OFF → volume)
       3.3 – AUDIO елемент (скрит, за iOS)
       3.4 – JS: mute/unmute, volume, localStorage, hard stop

   БЛОК 4.0 – Съдържание (центровано) – без „закован“ шорткът
       4.1 – Рендерира the_content() → PMPro шорткътът идва от самата страница
       4.2 – Нулиране на PMPro кожата + стъклен ефект (по твоите файлове)

   БЛОК 5.0 – Футър (напълно прозрачен)
       5.1 – © 2025 Следващи нива ЕООД
       5.2 – Общи условия
       5.3 – Цени и абонамент
============================================================ */
?>

<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
  <style>
    :root{
      --mx-brand:#0e3a46;
      --mx-text:#1f2a2a;
      --mx-white:#fff;
      --mx-glass-bg: rgba(255,255,255,0.42);
      --mx-glass-brd: rgba(255,255,255,0.55);
      --mx-overlay: rgba(0,0,0,0.25);
      --mx-shadow: 0 20px 60px rgba(0,0,0,0.22);
      --mx-radius: 16px;
    }
    *{box-sizing:border-box}
    body.public-shell{ margin:0; font:16px/1.45 system-ui,-apple-system,"Segoe UI",Roboto,Arial,"Noto Sans","Helvetica Neue"; color:var(--mx-text); background:#000; }
    a{ color:var(--mx-white); text-decoration:none }
    a:hover{ text-decoration:underline }

    /* ========== БЛОК 2.0 – Фонова картинка ========== */
    #mx-bg{ position:fixed; inset:0; background:#0b0f10 center/cover no-repeat; z-index:-1; }
    #mx-bg::before{ content:""; position:absolute; inset:0; background:var(--mx-img, none) center/cover no-repeat; }
    #mx-bg::after{ content:""; position:absolute; inset:0; background:var(--mx-overlay); }

    /* ========== БЛОК 3.0 – Контроли за музика (прозрачен „хедър“) ========== */
    #mx-header{ position:relative; z-index:10; background:transparent; border:0; }

    /* Бутоните: ON (вляво) → OFF (вдясно) → volume със 🔈 и 🔊; стиловете са по подадения файл. */ /* :contentReference[oaicite:1]{index=1} */
    .bgm-wrap {
      --bg: #e9ecef;
      --shadow-dark: rgba(0, 0, 0, 0.20);
      --shadow-light: rgba(255, 255, 255, 0.9);
      --accent: #3e4c40;
      --text: #6b7280;
      --radius-xl: 999px;

      display: flex;
      gap: 12px;
      align-items: center;
      justify-content: center;
      user-select: none;
      -webkit-tap-highlight-color: transparent;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;

      position: absolute;
      top: 40px;                 /* фиксирано на 60px под горния ръб */
      left: 50%;
      transform: translateX(-50%);
      z-index: 9999;
    }
    .bgm-toggle {
      display: grid;
      grid-template-columns: 1fr 1fr;
      width: 100px;
      height: 30px;
      padding: 4px;
      border-radius: var(--radius-xl);
      background: rgba(255, 255, 255, 0.6);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }
    .bgm-seg {
      border: 0; cursor: pointer; margin: 0;
      border-radius: calc(var(--radius-xl) - 4px);
      background: linear-gradient(180deg, #f7f9fb, #e9edf1);
      box-shadow: inset 1px 1px 2px rgba(0,0,0,0.08), inset -1px -1px 2px rgba(255,255,255,0.9);
      transition: transform .15s ease, background .2s ease, opacity .2s ease;
      display: grid; place-items: center;
      font-size: 16px; color: var(--text); outline: none;
    }
    .bgm-seg:focus { box-shadow: 0 0 0 2px #fff inset; }
    .bgm-seg.is-active { background: linear-gradient(180deg, #ffffff, #eef2f6); color: var(--accent); }
    .bgm-seg:not(.is-active) { opacity: .6; }

    .bgm-volume {
      display: grid; grid-template-columns: auto 1fr auto; /* 🔈 [range] 🔊 */
      align-items: center; gap: 6px;
      width: 180px; height: 30px; padding: 6px 10px;
      border-radius: var(--radius-xl);
      background: rgba(255, 255, 255, 0.6);
      backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
      font-size: 12px;
    }
    .bgm-volume .vol-icon{ line-height:1; font-size:14px; }
    .bgm-volume input[type="range"]{ width: 100%; -webkit-appearance:none; appearance:none; background:transparent; cursor:pointer; }
    .bgm-volume input[type="range"]::-webkit-slider-runnable-track{ height:4px; border-radius:2px; background:linear-gradient(90deg,#ccc,#888); }
    .bgm-volume input[type="range"]::-moz-range-track{ height:4px; border-radius:2px; background:linear-gradient(90deg,#ccc,#888); }
    .bgm-volume input[type="range"]::-webkit-slider-thumb{
      -webkit-appearance:none; appearance:none; width:12px; height:12px; border-radius:50%;
      background:#fff; border:1px solid #bbb; box-shadow:1px 1px 3px rgba(0,0,0,0.3); margin-top:-4px;
    }
    .bgm-volume input[type="range"]::-moz-range-thumb{
      width:12px; height:12px; border-radius:50%;
      background:#fff; border:1px solid #bbb; box-shadow:1px 1px 3px rgba(0,0,0,0.3);
    }

    /* ========== БЛОК 4.0 – Съдържание и стъклен ефект (центриране) ========== */
#mx-content{
  min-height:100vh;
  display:grid;
  place-items:center;
  padding:24px;
}

/* Палитра за glass (лесно за донагласяне) */
:root{
  --mx-glass-bg: rgba(255,255,255,0.16);
  --mx-glass-brd: rgba(255,255,255,0.35);
  --mx-glass-blur: 12px;
  --mx-brand: #3e4c40;   /* бутоните */
  --mx-text:  #ffffff;   /* основен текст върху тъмен фон */
}

/* 4.1 – Стъклени обвивки (само реалните форми, не кореновия .pmpro) */
:is(
  .pmpro_login_wrap,
  .pmpro_checkout,
  .pmpro_levels,
  .pmpro_account,
  .pmpro_confirmation, .pmpro_confirmation_wrap,
  .pmpro_billing, .pmpro_billing_wrap, .pmpro_billing_output,
  .pmpro_member_profile_edit_wrap,
  .pmpro_form                              /* fallback за някои шаблони */
){
  position: relative;
  padding: clamp(16px, 2.5vw, 32px) !important;
  border-radius: 20px;
}

/* 4.2 – Стъклена плоча (::before) — също само за горните обвивки  */
:is(
  .pmpro_login_wrap,
  .pmpro_checkout,
  .pmpro_levels,
  .pmpro_account,
  .pmpro_confirmation, .pmpro_confirmation_wrap,
  .pmpro_billing, .pmpro_billing_wrap, .pmpro_billing_output,
  .pmpro_member_profile_edit_wrap,
  .pmpro_form
)::before{
  content: "";
  position: absolute; inset: 0;
  background: var(--mx-glass-bg);
  backdrop-filter: blur(var(--mx-glass-blur));
  -webkit-backdrop-filter: blur(var(--mx-glass-blur));
  border-radius: inherit;
  box-shadow: 0 8px 32px rgba(0,0,0,0.25);
  z-index: 0;
}

/* всичко вътре остава над стъклото */
:is(
  .pmpro_login_wrap,
  .pmpro_checkout,
  .pmpro_levels,
  .pmpro_account,
  .pmpro_confirmation, .pmpro_confirmation_wrap,
  .pmpro_billing, .pmpro_billing_wrap, .pmpro_billing_output,
  .pmpro_member_profile_edit_wrap,
  .pmpro_form
) > *{ position: relative; z-index: 1; }

/* всичко вътре да е над стъклото */
:is(
  .pmpro,
  .pmpro_login_wrap,
  .pmpro_checkout,
  .pmpro_levels,
  .pmpro_account,
  .pmpro_confirmation, .pmpro_confirmation_wrap,
  .pmpro_billing, .pmpro_billing_wrap, .pmpro_billing_output,
  .pmpro_member_profile_edit_wrap,
  .pmpro_form
) > *{ position:relative; z-index:1; }

/* 4.3 – Нулиране на “белите карти” вътре в PMPro блоковете */
:is(
  .pmpro,
  .pmpro_login_wrap,
  .pmpro_checkout,
  .pmpro_levels,
  .pmpro_account,
  .pmpro_confirmation, .pmpro_confirmation_wrap,
  .pmpro_billing, .pmpro_billing_wrap, .pmpro_billing_output,
  .pmpro_member_profile_edit_wrap,
  .pmpro_form
) :is(
  .pmpro_card, .pmpro_actions_nav, .pmpro_message,
  .pmpro_section, .pmpro_table, fieldset, legend
){
  background: transparent !important;
  border: 0 !important;
  box-shadow: none !important;
}

/* 4.4 – Четимост на тъмен фон */
:is(
  .pmpro, .pmpro_login_wrap, .pmpro_checkout, .pmpro_levels,
  .pmpro_account, .pmpro_confirmation, .pmpro_confirmation_wrap,
  .pmpro_billing, .pmpro_billing_wrap, .pmpro_billing_output,
  .pmpro_member_profile_edit_wrap, .pmpro_form
) :is(h1,h2,h3,label,.pmpro_lost_password a,.pmpro_actions_nav a,
      .pmpro_show_password_wrap, .pmpro_show_password_wrap label){
  color: var(--mx-text) !important;
}

/* 4.5 – Полета */
:is(
  .pmpro, .pmpro_login_wrap, .pmpro_checkout, .pmpro_levels,
  .pmpro_account, .pmpro_confirmation, .pmpro_confirmation_wrap,
  .pmpro_billing, .pmpro_billing_wrap, .pmpro_billing_output,
  .pmpro_member_profile_edit_wrap, .pmpro_form
) :is(input[type="text"], input[type="email"], input[type="password"],
      input[type="tel"], input[type="number"], select, textarea){
  background: rgba(255,255,255,0.22) !important;
  border:1px solid var(--mx-glass-brd) !important;
  color: var(--mx-text) !important;
}
:is(.pmpro, .pmpro_login_wrap, .pmpro_checkout, .pmpro_form)
  input::placeholder{ color: rgba(255,255,255,0.85) !important; }

/* 4.6 – Бутони (primary) — без „Покажи паролата“ */
:is(.pmpro, .pmpro_login_wrap, .pmpro_checkout, .pmpro_form)
  :is(
    input[type="submit"],
    button[type="submit"],
    .pmpro_btn
  )
  :not(.wp-hide-pw)
  :not(.pmpro_show_password_button)
  :not(.pmpro_btn-show-password)
  :not([type="button"])
  :not(.button-secondary){
  background: var(--mx-brand) !important;
  border: 0 !important;
  color: #fff !important;
  padding: 12px 20px !important;
  border-radius: 10px !important;
  transition: transform .15s ease, opacity .15s ease;
}

:is(.pmpro, .pmpro_login_wrap, .pmpro_checkout, .pmpro_form)
  :is(
    input[type="submit"],
    button[type="submit"],
    .pmpro_btn
  )
  :not(.wp-hide-pw)
  :not(.pmpro_show_password_button)
  :not(.pmpro_btn-show-password)
  :not([type="button"])
  :not(.button-secondary):hover{
  transform: translateY(-1px);
  opacity: .95;
}

/* 4.6.1 – Show Password: прозрачен бутон + син акцент */
.pmpro_show_password_wrap button,
.pmpro_show_password_wrap .button,
.pmpro_show_password_wrap .pmpro_btn,
button.wp-hide-pw,
.pmpro_login_wrap button.wp-hide-pw,
.pmpro_form button.wp-hide-pw{
  background: transparent !important;
  background-color: transparent !important; /* за всеки случай */
  color: var(--mx-accent, #0ea5ff) !important;
  border: 0 !important;
  box-shadow: none !important;
  padding: 0 !important;
  line-height: 1;
}

.pmpro_show_password_wrap label{
  color: var(--mx-accent, #0ea5ff) !important;
}

.pmpro_show_password_wrap button:focus,
button.wp-hide-pw:focus{
  outline: 2px solid currentColor;
  outline-offset: 2px;
}


/* 4.7 – Линк “Регистрация” (ако го показваш под login) */
#pmpro-register-link{ margin-top:20px; text-align:center; }
#pmpro-register-link .pmpro-register-btn{
  display:block; width:100%; max-width:100%; padding:12px 0; border-radius:10px;
  font-weight:600; text-align:center; text-decoration:none;
  background:rgba(255,255,255,0.7); color:#000;
  border:1px solid rgba(255,255,255,0.8);
  backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
  box-shadow:0 6px 18px rgba(0,0,0,0.15); transition: all .2s ease;
}
#pmpro-register-link .pmpro-register-btn:hover{
  background: rgba(255,255,255,0.9);
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

/* 4.8 – Опционален “kill switch”: добави body клас .pmpro-glass-off за изключване */
.pmpro-glass-off
  :is(.pmpro, .pmpro_login_wrap, .pmpro_checkout, .pmpro_levels,
      .pmpro_account, .pmpro_confirmation, .pmpro_confirmation_wrap,
      .pmpro_billing, .pmpro_billing_wrap, .pmpro_billing_output,
      .pmpro_member_profile_edit_wrap, .pmpro_form)::before{
  display:none !important;
}

    /* ========== БЛОК 5.0 – Футър ========== */
    #mx-footer{ text-align:center; padding:80px; background:transparent; border:0; font-size:22px;}
    #mx-footer .mx-foot-inner{ display:flex; flex-wrap:wrap; gap:14px; justify-content:center; align-items:center; }
    #mx-footer, #mx-footer a{ color:#fff; }
    #mx-footer a{ text-decoration:none; }
    #mx-footer a:hover{ text-decoration:underline; }

    /* ========== Респонсив корекции (мобилен изглед) ========== */
    @media (max-width: 768px){
      /* Контролите влизат в потока, за да не застъпват формата */
      #mx-header { padding: 10px 12px 0; }
      .bgm-wrap{
        position: static;    /* вместо absolute */
        top: auto; left: auto; transform: none;
        flex-wrap: wrap;     /* ако няма място – пренасяне */
        gap: 10px 12px;
      }
      /* Центриране и дишане около формата */
      #mx-content{
        min-height: calc(100vh - 100px);   /* отстъп за хедъра/контролите */
        padding: 24px 16px 24px;
        place-items: center;
      }
      /* Футър: трите линка един под друг */
      #mx-footer .mx-foot-inner{
        flex-direction: column;
        gap: 8px;
        align-items: center;
      }
    }
  </style>
</head>
<body <?php body_class('public-shell'); ?>>

<?php
/* ============================================================
   БЛОК 1.0 – Проверка за логнати + подготовка на фон/аудио
============================================================ */
if ( is_user_logged_in() ) { wp_redirect( home_url('/') ); exit; }

/* 1.2 – Фоново изображение */
$mx_bg_meta      = get_post_meta(get_the_ID(), 'mx_bg', true);
$mx_bg_featured  = get_the_post_thumbnail_url(get_the_ID(), 'full');
$mx_bg_fallback  = 'https://maxima-membership.next-lvls.com/wp-content/uploads/2025/08/Background_Login.png';
$mx_bg_url       = $mx_bg_meta ? esc_url($mx_bg_meta) : ($mx_bg_featured ? esc_url($mx_bg_featured) : esc_url($mx_bg_fallback));

/* 1.3 – Аудио URL */
$mx_audio_meta     = get_post_meta(get_the_ID(), 'mx_audio', true);
$mx_audio_fallback = get_stylesheet_directory_uri() . '/assets/audio/login.mp3';
$mx_audio_url      = $mx_audio_meta ? esc_url($mx_audio_meta) : esc_url($mx_audio_fallback);
?>

<!-- ============================================================
     БЛОК 2.0 – Фонова картинка
============================================================ -->
<div id="mx-bg" style="--mx-img:url('<?php echo $mx_bg_url; ?>');"></div>

<!-- ============================================================
     БЛОК 3.0 – Контроли за музика (прозрачен „хедър“)
============================================================ -->
<header id="mx-header" role="banner" aria-label="Контрол на звука">
  <div class="bgm-wrap">
    <!-- ВНИМАНИЕ: Размяната е само визуална – логиката и ID остават същите -->
    <div id="bgmToggle" class="bgm-toggle" role="group" aria-label="Звук">
      <button id="bgmOn"  class="bgm-seg"         type="button" aria-pressed="false" aria-label="Включен звук">🔊</button>
      <button id="bgmOff" class="bgm-seg is-active" type="button" aria-pressed="true"  aria-label="Изключен звук">🔇</button>
    </div>
    <div class="bgm-volume" aria-label="Сила на звука">
      <span class="vol-icon" aria-hidden="true">🔈</span>
      <input id="bgmVolume" type="range" min="0" max="100" step="1" value="7" aria-valuemin="0" aria-valuemax="100" aria-valuenow="7" />
      <span class="vol-icon" aria-hidden="true">🔊</span>
    </div>
  </div>
</header>

<!-- 3.3 – Скрит AUDIO елемент (за iOS) -->
<audio id="loginMusic" preload="auto" muted loop>
  <source src="<?php echo $mx_audio_url; ?>" type="audio/mpeg">
</audio>

<!-- ============================================================
     БЛОК 4.0 – Съдържание (центровано) – без закован шорткът
     (PMPro шорткътът идва от съдържанието на страницата)
============================================================ -->
<main id="mx-content" role="main">
  <?php
    while ( have_posts() ) : the_post();
      the_content();
    endwhile;
  ?>
</main>

<!-- ============================================================
     БЛОК 5.0 – Футър (прозрачен, без линии)
============================================================ -->
<footer id="mx-footer" role="contentinfo">
  <div class="mx-foot-inner">
    <span>© 2025 Следващи нива ЕООД</span>
    <a href="/obshti-usloviya">Общи условия</a>
    <a href="/ceni-i-abonamenti">Цени и абонамент</a>
  </div>
</footer>

<!-- ============================================================
     3.4 – JS: Управление на музиката (mute/unmute, volume, localStorage, hard stop)
============================================================ -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const audio = document.getElementById('loginMusic');
  if (!audio) return;
  audio.loop = true;

  const LS_KEY_MUTE = 'loginAudioMuted';
  const LS_KEY_VOL  = 'loginAudioVolume';

  let isMuted      = true;
  let wantUnmuted  = false;
  let userUnlocked = false;

  audio.muted  = true;
  audio.volume = 0.07;
  audio.play().catch(() => {});

  try { wantUnmuted = (localStorage.getItem(LS_KEY_MUTE) === 'false'); } catch (_) {}
  try {
    const savedVol = localStorage.getItem(LS_KEY_VOL);
    if (savedVol !== null) {
      const v = Math.max(0, Math.min(100, Number(savedVol))) / 100;
      audio.volume = v;
    }
  } catch (_) {}

  function onFirstInteraction(ev) {
    if (ev && ev.target && ev.target.closest && ev.target.closest('#bgmToggle')) return;
    userUnlocked = true;
    ['click','pointerup','keydown'].forEach(evName => document.removeEventListener(evName, onFirstInteraction, true));
    if (wantUnmuted) { unmuteAndPlayStrict(false); }
  }
  ['click','pointerup','keydown'].forEach(evName => document.addEventListener(evName, onFirstInteraction, { once:true, capture:true }));

  const onBtn    = document.getElementById('bgmOn');
  const offBtn   = document.getElementById('bgmOff');
  const volInput = document.getElementById('bgmVolume');
  const toggleEl = document.getElementById('bgmToggle');
  if (!onBtn || !offBtn || !volInput || !toggleEl) return;

  function setToggleByState(muted) {
    onBtn.classList.toggle('is-active', !muted);
    offBtn.classList.toggle('is-active', muted);
    onBtn.setAttribute('aria-pressed', String(!muted));
    offBtn.setAttribute('aria-pressed', String(muted));
  }
  setToggleByState(true);
  volInput.value = String(Math.round((audio.volume ?? 0.07) * 100));
  volInput.setAttribute('aria-valuenow', volInput.value);

  onBtn.addEventListener('click', async (e) => {
    e.stopPropagation();
    wantUnmuted = true;
    const ok = await unmuteAndPlayStrict(true);
    if (ok) {
      userUnlocked = true;
      ['click','pointerup','keydown'].forEach(evName => document.removeEventListener(evName, onFirstInteraction, true));
    }
  });

  offBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    wantUnmuted = false;
    setMuted(true, true);
  });

  volInput.addEventListener('input', (e) => {
    const raw = Number(e.target.value || 0);
    const v   = Math.max(0, Math.min(100, raw)) / 100;
    audio.volume = v;
    volInput.setAttribute('aria-valuenow', String(Math.round(v*100)));
    try { localStorage.setItem(LS_KEY_VOL, String(Math.round(v*100))); } catch (_) {}
  });

  const AUDIO_SRC = (audio.querySelector('source') && audio.querySelector('source').src) || audio.src || '';
  let sourceDetached = false;

  function hardStop() {
    try { audio.pause(); } catch (_){}
    try {
      const srcEl = audio.querySelector('source');
      if (srcEl) srcEl.removeAttribute('src');
      audio.removeAttribute('src');
      audio.load();
      sourceDetached = true;
    } catch (_){}
  }
  function restoreSource() {
    const srcEl = audio.querySelector('source');
    if (srcEl && !srcEl.src) srcEl.src = AUDIO_SRC;
    if (!audio.src && AUDIO_SRC) audio.src = AUDIO_SRC;
    audio.load();
    sourceDetached = false;
  }
  document.addEventListener('visibilitychange', function () {
    if (document.hidden) { hardStop(); }
    else {
      if (sourceDetached) restoreSource();
      if (!isMuted && userUnlocked) { audio.play().catch(()=>{}); }
    }
  });
  window.addEventListener('pagehide', hardStop);
  window.addEventListener('beforeunload', hardStop);

  function setMuted(state, persist=false) {
    isMuted = state; audio.muted = state; setToggleByState(state);
    if (persist) { try { localStorage.setItem(LS_KEY_MUTE, String(state)); } catch (_){ } }
  }
  async function unmuteAndPlayStrict(persist=false) {
    try {
      audio.muted = false; audio.removeAttribute('muted');
      if (audio.readyState < 2) audio.load();
      if (audio.paused) audio.currentTime = 0;
      await audio.play();
      setMuted(false, persist);
      return true;
    } catch (err) {
      setMuted(true, persist);
      return false;
    }
  }
});
</script>

<!-- Доп. JS за бутон „Регистрация“ (оставен непроменен функционално) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const wrap     = document.querySelector('.pmpro_login_wrap');
  const lost     = document.querySelector('.pmpro_lost_password, a[href*="lostpassword"]');
  const loginBtn = document.querySelector('.pmpro_login_wrap input[type="submit"]');
  if (!wrap || !loginBtn) return;
  if (document.getElementById('pmpro-register-link')) return;

  const holder = document.createElement('div');
  holder.id = 'pmpro-register-link';
  holder.style.display = 'flex';
  holder.style.justifyContent = 'center';
  holder.style.marginTop = '22px';

  const btn = document.createElement('a');
  btn.className   = 'pmpro-register-btn';
  btn.href        = '<?php echo esc_url( function_exists("pmpro_url") ? pmpro_url("checkout") : home_url("/membership-checkout/") ); ?>';
  btn.textContent = 'Регистрация';

  const cs = window.getComputedStyle(loginBtn);
  const loginW   = loginBtn.getBoundingClientRect().width;
  const padY     = cs.paddingTop;
  const radius   = cs.borderRadius;
  const fSize    = cs.fontSize;
  const fFamily  = cs.fontFamily;

  btn.style.display = 'inline-block';
  btn.style.width   = loginW + 'px';
  btn.style.paddingTop = padY;
  btn.style.paddingBottom = padY;
  btn.style.borderRadius  = radius;
  btn.style.fontSize  = fSize;
  btn.style.fontFamily= fFamily;
  btn.style.fontWeight= '400';
  btn.style.textAlign = 'center';
  btn.style.textDecoration = 'none';

  btn.style.color      = '#111';
  btn.style.background = 'rgba(255,255,255,0.72)';
  btn.style.border     = '1px solid rgba(255,255,255,0.85)';
  btn.style.backdropFilter = 'blur(8px)';
  btn.style.webkitBackdropFilter = 'blur(8px)';
  btn.style.boxShadow  = '0 6px 18px rgba(0,0,0,0.18)';
  btn.style.transition = 'transform .15s ease, background .15s ease, box-shadow .15s ease';

  btn.addEventListener('mouseenter', () => {
    btn.style.background = 'rgba(255,255,255,0.9)';
    btn.style.boxShadow  = '0 10px 26px rgba(0,0,0,0.24)';
    btn.style.transform  = 'translateY(-1px)';
  });
  btn.addEventListener('mouseleave', () => {
    btn.style.background = 'rgba(255,255,255,0.72)';
    btn.style.boxShadow  = '0 6px 18px rgba(0,0,0,0.18)';
    btn.style.transform  = 'none';
  });

  if (lost && lost.parentElement) {
    lost.parentElement.insertAdjacentElement('afterend', holder);
  } else {
    wrap.appendChild(holder);
  }
  holder.appendChild(btn);
});
</script>

<?php wp_footer(); ?>
</body>
</html>



