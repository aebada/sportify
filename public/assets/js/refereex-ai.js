(function () {
  'use strict';

  if (!document.querySelector('[data-refereex-page]')) return;

  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Adaptive fusion confidence sliders */
  var fusionSliders = document.querySelectorAll('[data-rx-conf-slider]');
  var fusionResult = document.querySelector('[data-rx-fusion-result]');

  function updateFusion() {
    var total = 0;
    var penaltyWeight = 0;
    fusionSliders.forEach(function (slider) {
      var cam = slider.closest('[data-rx-fusion-cam]');
      var conf = parseInt(slider.value, 10) || 0;
      var display = cam && cam.querySelector('[data-rx-conf-display]');
      if (display) display.textContent = conf + '%';
      total += conf;
      var decision = cam && cam.querySelector('.fw-800');
      if (decision && /penalty/i.test(decision.textContent) && !/no/i.test(decision.textContent)) {
        penaltyWeight += conf;
      }
    });
    if (fusionResult && total > 0) {
      var pct = Math.round((penaltyWeight / total) * 100);
      fusionResult.textContent = (pct >= 50 ? 'Penalty' : 'No penalty') + ' — ' + pct + '%';
    }
  }

  fusionSliders.forEach(function (slider) {
    slider.addEventListener('input', updateFusion);
  });
  updateFusion();

  /* Digital twin timeline sync */
  var twinTimeline = document.querySelector('[data-rx-twin-timeline]');
  if (twinTimeline && !reducedMotion) {
    var segs = twinTimeline.querySelectorAll('[data-rx-twin-seg]');
    var idx = 0;
    setInterval(function () {
      segs.forEach(function (s, i) {
        s.classList.toggle('is-synced', i <= idx);
      });
      idx = (idx + 1) % segs.length;
    }, 800);
  } else if (twinTimeline) {
    twinTimeline.querySelectorAll('[data-rx-twin-seg]').forEach(function (s) {
      s.classList.add('is-synced');
    });
  }
})();
