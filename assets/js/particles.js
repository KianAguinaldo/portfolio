/**
 * Particle Background System - Modern Tech Aesthetic
 * Designed for Kian Lee Raian M. Aguinaldo's IT Portfolio
 *
 * Features:
 * - High-performance HTML5 Canvas rendering
 * - Multi-layer vertical scroll parallax (different speeds per depth layer)
 * - Gentle horizontal/diagonal drifting
 * - Desktop mouse reaction: subtle repulsion & brightness boost
 * - Minimal, ultra-subtle proximity connection lines
 * - Screen-size adaptive particle density (reduced on mobile)
 * - Complete prefers-reduced-motion accessibility support
 */
(function() {
  'use strict';

  const canvas = document.getElementById('particleCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  // Motion preference query
  const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
  let prefersReducedMotion = motionQuery.matches;

  motionQuery.addEventListener('change', (e) => {
    prefersReducedMotion = e.matches;
    if (prefersReducedMotion) {
      if (animId) cancelAnimationFrame(animId);
      drawStatic();
    } else {
      animate();
    }
  });

  let W = 0, H = 0;
  let particles = [];
  let mouse = { x: -9999, y: -9999, active: false };
  let currentScrollY = window.scrollY || window.pageYOffset || 0;
  let targetScrollY = currentScrollY;
  let animId = null;

  // Custom Portfolio Palette: #5680E9, #84CEEB, #5AB9EA, #C1C8C4, #8860D0
  const PALETTE = [
    { r: 86,  g: 128, b: 233 }, // Royal Blue #5680E9
    { r: 132, g: 206, b: 235 }, // Sky Blue #84CEEB
    { r: 90,  g: 185, b: 234 }, // Cerulean #5AB9EA
    { r: 193, g: 200, b: 196 }, // Silver/Tech #C1C8C4
    { r: 136, g: 96,  b: 208 }  // Tech Violet #8860D0
  ];

  function getScreenParticleCount() {
    const width = window.innerWidth;
    if (width < 640) return 32;       // Mobile
    if (width < 1024) return 55;      // Tablet
    return 80;                        // Desktop
  }

  function isMobile() {
    return window.innerWidth < 768;
  }

  function createParticle(index, total) {
    // 3 depth layers: 1 = deep (small/slow), 2 = mid, 3 = near (larger/subtle brightness)
    const layer = (index % 3) + 1;
    const color = PALETTE[Math.floor(Math.random() * PALETTE.length)];

    let radius, baseAlpha, parallaxFactor, vx, vy;

    if (layer === 1) {
      radius = 1.0 + Math.random() * 0.5;
      baseAlpha = 0.15 + Math.random() * 0.15;
      parallaxFactor = 0.05; // moves slower with scroll
      vx = (Math.random() - 0.5) * 0.15;
      vy = (Math.random() - 0.5) * 0.10;
    } else if (layer === 2) {
      radius = 1.4 + Math.random() * 0.8;
      baseAlpha = 0.25 + Math.random() * 0.20;
      parallaxFactor = 0.10; // mid speed
      vx = (Math.random() - 0.5) * 0.22;
      vy = (Math.random() - 0.5) * 0.14;
    } else {
      radius = 2.0 + Math.random() * 0.9;
      baseAlpha = 0.40 + Math.random() * 0.25;
      parallaxFactor = 0.18; // foreground layer
      vx = (Math.random() - 0.5) * 0.28;
      vy = (Math.random() - 0.5) * 0.18;
    }

    return {
      x: Math.random() * W,
      y: Math.random() * H,
      baseX: Math.random() * W,
      baseY: Math.random() * H,
      radius: radius,
      baseAlpha: baseAlpha,
      currentAlpha: baseAlpha,
      color: color,
      layer: layer,
      parallaxFactor: parallaxFactor,
      vx: vx,
      vy: vy,
      driftAngle: Math.random() * Math.PI * 2,
      driftSpeed: 0.004 + Math.random() * 0.006,
      pushX: 0,
      pushY: 0
    };
  }

  function initParticles() {
    const count = getScreenParticleCount();
    particles = [];
    for (let i = 0; i < count; i++) {
      particles.push(createParticle(i, count));
    }
  }

  function resize() {
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    W = window.innerWidth;
    H = window.innerHeight;

    canvas.width = Math.floor(W * dpr);
    canvas.height = Math.floor(H * dpr);
    canvas.style.width = W + 'px';
    canvas.style.height = H + 'px';

    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    initParticles();

    if (prefersReducedMotion) {
      drawStatic();
    }
  }

  function drawStatic() {
    ctx.clearRect(0, 0, W, H);
    for (let i = 0; i < particles.length; i++) {
      const p = particles[i];
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(${p.color.r}, ${p.color.g}, ${p.color.b}, ${p.baseAlpha})`;
      ctx.fill();
    }
  }

  function updateAndDraw() {
    ctx.clearRect(0, 0, W, H);

    // Smooth scroll interpolation
    currentScrollY += (targetScrollY - currentScrollY) * 0.08;

    const desktop = !isMobile();

    for (let i = 0; i < particles.length; i++) {
      const p = particles[i];

      // 1. Organic gentle drift
      p.driftAngle += p.driftSpeed;
      p.x += p.vx + Math.cos(p.driftAngle) * 0.08;
      p.y += p.vy + Math.sin(p.driftAngle) * 0.06;

      // Decay repulsion push
      p.pushX *= 0.92;
      p.pushY *= 0.92;
      p.x += p.pushX;
      p.y += p.pushY;

      // 2. Parallax vertical adjustment based on layer
      const scrollShift = (currentScrollY * p.parallaxFactor) % H;
      let renderY = (p.y - scrollShift) % H;
      if (renderY < 0) renderY += H;

      let renderX = p.x % W;
      if (renderX < 0) renderX += W;

      // 3. Desktop mouse cursor interaction (gentle repel & brighten)
      let displayAlpha = p.baseAlpha;
      if (desktop && mouse.active) {
        const dx = renderX - mouse.x;
        const dy = renderY - mouse.y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        const repelRadius = 85;

        if (dist < repelRadius && dist > 1) {
          const force = (1 - dist / repelRadius) * 1.5;
          p.pushX += (dx / dist) * force;
          p.pushY += (dy / dist) * force;

          // Subtle brightness boost near cursor
          const brighten = (1 - dist / repelRadius) * 0.35;
          displayAlpha = Math.min(0.95, displayAlpha + brighten);
        }
      }

      // 4. Draw particle dot
      ctx.beginPath();
      ctx.arc(renderX, renderY, p.radius, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(${p.color.r}, ${p.color.g}, ${p.color.b}, ${displayAlpha})`;
      ctx.fill();

      // Subtle soft glow for foreground layer particles
      if (p.layer === 3 && displayAlpha > 0.45 && desktop) {
        ctx.beginPath();
        ctx.arc(renderX, renderY, p.radius * 2.2, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(${p.color.r}, ${p.color.g}, ${p.color.b}, ${displayAlpha * 0.18})`;
        ctx.fill();
      }

      // Store rendered positions for distance calculation
      p.renderX = renderX;
      p.renderY = renderY;
    }

    // 5. Extremely subtle proximity connection lines (desktop only, limited distance)
    if (desktop) {
      const maxConnectDist = 75;
      ctx.lineWidth = 0.4;

      for (let i = 0; i < particles.length; i++) {
        const p1 = particles[i];
        for (let j = i + 1; j < particles.length; j++) {
          const p2 = particles[j];
          const dx = p1.renderX - p2.renderX;
          const dy = p1.renderY - p2.renderY;
          const dist = Math.sqrt(dx * dx + dy * dy);

          if (dist < maxConnectDist) {
            const lineAlpha = (1 - dist / maxConnectDist) * 0.07;
            ctx.beginPath();
            ctx.moveTo(p1.renderX, p1.renderY);
            ctx.lineTo(p2.renderX, p2.renderY);
            ctx.strokeStyle = `rgba(86, 128, 233, ${lineAlpha})`;
            ctx.stroke();
          }
        }
      }
    }
  }

  function animate() {
    if (prefersReducedMotion) {
      drawStatic();
      return;
    }
    updateAndDraw();
    animId = requestAnimationFrame(animate);
  }

  // Event Listeners
  window.addEventListener('scroll', () => {
    targetScrollY = window.scrollY || window.pageYOffset || 0;
  }, { passive: true });

  if (!isMobile()) {
    window.addEventListener('mousemove', (e) => {
      mouse.x = e.clientX;
      mouse.y = e.clientY;
      mouse.active = true;
    }, { passive: true });

    document.addEventListener('mouseleave', () => {
      mouse.active = false;
      mouse.x = -9999;
      mouse.y = -9999;
    });
  }

  let resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(resize, 150);
  });

  // Initialization
  resize();
  if (!prefersReducedMotion) {
    animate();
  }
})();
