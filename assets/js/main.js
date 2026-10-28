/**
 * Portfolio Main JavaScript
 * Features: Navigation, Scroll animations, Skill bars, Contact form
 */
(function() {
  'use strict';

  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ============================================================
     NAVBAR
     ============================================================ */
  const navbar = document.getElementById('navbar');
  const navToggle = document.getElementById('navToggle');
  const navMenu = document.getElementById('navMenu');
  const navLinks = document.querySelectorAll('.nav-link');

  // Scroll effect on navbar
  window.addEventListener('scroll', function() {
    if (window.scrollY > 50) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
    updateActiveNav();
  }, { passive: true });

  // Mobile toggle
  navToggle.addEventListener('click', function() {
    const isOpen = navMenu.classList.toggle('open');
    navToggle.classList.toggle('open');
    navToggle.setAttribute('aria-expanded', isOpen);
  });

  // Close mobile menu when link clicked
  navLinks.forEach(function(link) {
    link.addEventListener('click', function() {
      navMenu.classList.remove('open');
      navToggle.classList.remove('open');
      navToggle.setAttribute('aria-expanded', 'false');
    });
  });

  // Active nav link based on scroll position
  function updateActiveNav() {
    const sections = document.querySelectorAll('section[id]');
    const scrollPos = window.scrollY + 120;
    sections.forEach(function(section) {
      const top = section.offsetTop;
      const height = section.offsetHeight;
      const id = section.getAttribute('id');
      const link = document.querySelector('.nav-link[href="#' + id + '"]');
      if (link) {
        if (scrollPos >= top && scrollPos < top + height) {
          navLinks.forEach(l => l.classList.remove('active'));
          link.classList.add('active');
        }
      }
    });
  }

  /* ============================================================
     SCROLL REVEAL (Intersection Observer)
     ============================================================ */
  if (!prefersReducedMotion) {
    const revealObserver = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          // Animate skill bars when visible
          const bars = entry.target.querySelectorAll ? entry.target.querySelectorAll('.skill-bar') : [];
          bars.forEach(function(bar) {
            const level = bar.getAttribute('data-level');
            bar.style.width = level + '%';
          });
        }
      });
    }, {
      threshold: 0.12,
      rootMargin: '0px 0px -40px 0px'
    });

    // Observe all reveal elements
    document.querySelectorAll('.reveal').forEach(function(el) {
      revealObserver.observe(el);
    });

    // Skill bar observer
    const skillObserver = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          const bar = entry.target;
          const level = bar.getAttribute('data-level');
          setTimeout(function() {
            bar.style.width = level + '%';
          }, parseInt(bar.closest('.skill-card')?.style.getPropertyValue('--stagger') || '0') * 1000 || 0);
          skillObserver.unobserve(bar);
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('.skill-bar').forEach(function(bar) {
      skillObserver.observe(bar);
    });

  } else {
    // Reduced motion: show everything immediately
    document.querySelectorAll('.reveal').forEach(el => el.classList.add('visible'));
    document.querySelectorAll('.skill-bar').forEach(function(bar) {
      bar.style.width = bar.getAttribute('data-level') + '%';
    });
  }

  /* ============================================================
     CONTACT FORM (Real AJAX to api/contact.php -> MySQL)
     ============================================================ */
  const contactForm = document.getElementById('contactForm');
  const formMessage = document.getElementById('formMessage');

  if (contactForm) {
    contactForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      const btn = contactForm.querySelector('button[type="submit"]');
      const originalText = btn.innerHTML;
      btn.innerHTML = 'Sending...';
      btn.disabled = true;

      const formData = new FormData(contactForm);

      try {
        const res = await fetch('api/contact.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();
        formMessage.style.display = 'block';

        if (res.ok && data.status === 'success') {
          formMessage.className = 'form-feedback success';
          formMessage.textContent = '[OK] ' + (data.message || 'Message sent! Saved to database.');
          contactForm.reset();
        } else {
          formMessage.className = 'form-feedback error';
          formMessage.textContent = '[Error] ' + (data.message || 'Unable to submit message.');
        }
      } catch (err) {
        formMessage.style.display = 'block';
        formMessage.className = 'form-feedback error';
        formMessage.textContent = '[Error] Connection error. Please contact directly at kianaguinaldo7@gmail.com';
      } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
        setTimeout(() => {
          if (formMessage && formMessage.classList.contains('success')) {
            formMessage.style.display = 'none';
          }
        }, 7000);
      }
    });
  }

  /* ============================================================
     SMOOTH SCROLL for anchor links
     ============================================================ */
  document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
    anchor.addEventListener('click', function(e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        const offset = 80;
        const top = target.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top: top, behavior: prefersReducedMotion ? 'auto' : 'smooth' });
      }
    });
  });

  // Initial check on load
  updateActiveNav();

})();
