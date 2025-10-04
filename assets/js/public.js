// == PUBLIC SHELL =============================================
// Логика за аудио контролите: on/off + volume + запомняне.
// Работи върху маркировката от template-parts/audio-controls.php

(function(){
  const wrap = document.querySelector('.bgm-wrap');
  if(!wrap) return;

  const audio = wrap.querySelector('.bgm-audio');
  const toggle = wrap.querySelector('.bgm-toggle');
  const volume = wrap.querySelector('.bgm-volume');

  // 1) Инициализация на аудио източника
  const dataSrc = wrap.getAttribute('data-audio-src');
  if (dataSrc && audio) {
    audio.src = dataSrc;
  }

  // 2) Зареждаме предишни предпочитания (ако има)
  const SAVED = window.mxStorage.get('mx-bgm', { on:false, vol:0.5 });

  // volume
  if (typeof SAVED.vol === 'number' && volume) {
    volume.value = String(SAVED.vol);
    if (audio) audio.volume = SAVED.vol;
  }

  // on/off (aria-pressed = true означава "ON")
  if (toggle) {
    toggle.setAttribute('aria-pressed', SAVED.on ? 'true' : 'false');
  }

  // Ако е включено и няма предпочитание за по-малко движение — опитай да пуснеш
  function tryPlay(){
    if (!audio) return;
    if (SAVED.on && !window.mxPrefersReduced) {
      audio.play().catch(()=>{/* браузърът може да блокира autoplay, не е проблем */});
    } else {
      audio.pause();
    }
  }
  tryPlay();

  // 3) Слушатели
  // ON/OFF
  toggle && toggle.addEventListener('click', () => {
    const isOn = toggle.getAttribute('aria-pressed') === 'true';
    const next = !isOn;
    toggle.setAttribute('aria-pressed', next ? 'true' : 'false');

    if (audio) {
      if (next) audio.play().catch(()=>{}); else audio.pause();
    }

    window.mxStorage.set('mx-bgm', { on: next, vol: parseFloat(volume?.value ?? audio?.volume ?? .5) });
  });

  // Volume
  volume && volume.addEventListener('input', e => {
    const v = parseFloat(e.currentTarget.value || '0.5');
    if (audio) audio.volume = v;
    const isOn = toggle?.getAttribute('aria-pressed') === 'true';
    window.mxStorage.set('mx-bgm', { on: !!isOn, vol: v });
  });

  // 4) Ако потребителят стартира възпроизвеждане ръчно (iOS/Chrome)
  audio && audio.addEventListener('play', () => {
    toggle?.setAttribute('aria-pressed', 'true');
    window.mxStorage.set('mx-bgm', { on:true, vol: parseFloat(volume?.value || audio.volume || .5) });
  });
  audio && audio.addEventListener('pause', () => {
    toggle?.setAttribute('aria-pressed', 'false');
    window.mxStorage.set('mx-bgm', { on:false, vol: parseFloat(volume?.value || audio.volume || .5) });
  });

})();
