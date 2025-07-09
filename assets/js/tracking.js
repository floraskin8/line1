(function () {
  console.log('BlackBeast Analytics Tracking Script Loaded');
  const TRACKING_URL = bba_tracker.ajax_url;
const NONCE = bba_tracker.nonce;

const sessionId = getSessionId(); // ✅ définir AVANT de l’utiliser

const data = {
  action: 'bba_track_visit',
  nonce: NONCE,
  session_id: sessionId,
  url: window.location.href,
  referrer: document.referrer,
  lang: navigator.language,
  browser: navigator.userAgent,
  duration: 0
};


fetch(TRACKING_URL, {
  method: 'POST',
  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  body: new URLSearchParams(data)
});


  function generateSessionId() {
    return 'sess_' + Math.random().toString(36).substring(2, 12);
  }

  function getSessionId() {
    const cookie = document.cookie
      .split('; ')
      .find(row => row.startsWith('bba_session='));

    let sessionId = cookie ? cookie.split('=')[1] : null;

    if (!sessionId) {
      sessionId = generateSessionId();
      document.cookie = `bba_session=${sessionId}; path=/; max-age=1800`;
    }

    return sessionId;
  }

  
  const startTime = Date.now();

  function collectTrackingData(duration = 0) {
    return {
      action: 'bba_track_visit',
      nonce: NONCE,
      session_id: sessionId,
      url: window.location.href,
      referrer: document.referrer,
      lang: navigator.language,
      browser: navigator.userAgent,
      duration: duration
    };
  }

  // ✅ ENVOI initial avec fetch()
  function sendTrackingData(data) {
    fetch(TRACKING_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: new URLSearchParams(data) // ⬅ C’est ici que tu places les données
    });
  }

  sendTrackingData(collectTrackingData());

  // ✅ ENVOI avant de quitter la page (durée de session)
  window.addEventListener('beforeunload', () => {
    const duration = Math.round((Date.now() - startTime) / 1000);
    const data = collectTrackingData(duration);
    navigator.sendBeacon(TRACKING_URL, new URLSearchParams(data));
  });
})();
