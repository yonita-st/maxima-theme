// == COMMON (зарежда се навсякъде) =============================

// Малък helper за localStorage с fallback
window.mxStorage = {
  get(key, fallback = null){
    try { const v = localStorage.getItem(key); return v === null ? fallback : JSON.parse(v); }
    catch(e){ return fallback; }
  },
  set(key, value){
    try { localStorage.setItem(key, JSON.stringify(value)); } catch(e){}
  }
};

// Хелпър за „намалявай движенията“
window.mxPrefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
