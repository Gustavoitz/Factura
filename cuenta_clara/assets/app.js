(() => {
  const $ = s => document.querySelector(s);
  const sleep = ms => new Promise(r => setTimeout(r, ms));
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const logo = $('#logo'), slot = $('#slot'), loader = $('#loader'), SIZE = 160;
  const sinLoader = document.documentElement.classList.contains('sin-loader');
  let docked = false;

  const place = (x, y, s) => logo.style.transform = `translate(${x}px,${y}px) scale(${s})`;
  const center = () => place(innerWidth / 2 - SIZE / 2, innerHeight / 2 - SIZE / 2 - 20, 1);
  const toSlot = () => { const r = slot.getBoundingClientRect(); place(r.left, r.top, r.width / SIZE); };

  // El logo viaja del centro de la pantalla a la cabecera
  function dock(animar) {
    docked = true;
    logo.style.transition = animar && !reduce ? 'transform 1s cubic-bezier(.65,0,.2,1)' : 'none';
    toSlot();
    logo.classList.add('docked');
    loader.classList.add('hide');
    document.body.classList.add('ready');
    const u = $('input'); if (u && matchMedia('(pointer: fine)').matches) u.focus({ preventScroll: true });
    countUp();
  }

  if (sinLoader) {
    dock(false);
  } else {
    center();
    Promise.all([document.fonts.ready, sleep(reduce ? 400 : 2500)]).then(() => {
      try { sessionStorage.setItem('cc_loader', '1'); } catch (e) {}
      dock(true);
    });
  }

  addEventListener('resize', () => {
    logo.style.transition = 'none';
    docked ? toSlot() : center();
  });

  // Rellenar la cuenta de prueba
  document.querySelectorAll('.usarDemo').forEach(demo => {
    demo.onclick = () => {
      $('#usuario').value = demo.dataset.u;
      $('#password').value = demo.dataset.p;
      $('#password').focus();
    };
  });

  // Mostrar / ocultar contraseña
  const ver = $('#ver');
  if (ver) ver.onclick = () => {
    const p = $('#password'), oculto = p.type === 'password';
    p.type = oculto ? 'text' : 'password';
    ver.textContent = oculto ? 'Ocultar' : 'Mostrar';
    ver.setAttribute('aria-label', oculto ? 'Ocultar contraseña' : 'Mostrar contraseña');
  };

  // El total sube contando desde cero
  function countUp() {
    const el = $('#total'); if (!el || reduce) return;
    const meta = +el.dataset.total, t0 = performance.now(), dur = 1100;
    const fmt = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 });
    (function paso(t) {
      const p = Math.min((t - t0) / dur, 1);
      el.textContent = fmt.format(Math.round(meta * (1 - Math.pow(1 - p, 3))));
      if (p < 1) requestAnimationFrame(paso);
    })(t0);
  }
})();
