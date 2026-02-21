/* Zurihub Technology - Main JavaScript */

document.addEventListener('DOMContentLoaded', function() {
  /* Header scroll behavior */
  const header = document.getElementById('siteHeader');
  if (header) {
    const onScroll = () => {
      header.classList.toggle('scrolled', window.scrollY > 50);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* Mobile nav */
  const menuBtn = document.getElementById('mobileMenuBtn');
  const closeBtn = document.getElementById('mobileCloseBtn');
  const mobileNav = document.getElementById('mobileNav');
  const overlay = document.getElementById('mobileOverlay');

  function openMobile() {
    mobileNav && mobileNav.classList.add('active');
    overlay && overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    menuBtn && menuBtn.setAttribute('aria-expanded', 'true');
  }

  function closeMobile() {
    mobileNav && mobileNav.classList.remove('active');
    overlay && overlay.classList.remove('active');
    document.body.style.overflow = '';
    menuBtn && menuBtn.setAttribute('aria-expanded', 'false');
  }

  menuBtn && menuBtn.addEventListener('click', openMobile);
  closeBtn && closeBtn.addEventListener('click', closeMobile);
  overlay && overlay.addEventListener('click', closeMobile);

  document.querySelectorAll('.mobile-toggle').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var target = document.getElementById(this.dataset.target);
      if (target) {
        target.classList.toggle('active');
        var icon = this.querySelector('svg');
        if (icon) icon.style.transform = target.classList.contains('active') ? 'rotate(180deg)' : '';
      }
    });
  });

  /* Scroll reveal */
  var reveals = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');
  if (reveals.length) {
    var observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    reveals.forEach(function(el) { observer.observe(el); });
  }

  /* Back to top */
  var btt = document.getElementById('backToTop');
  if (btt) {
    window.addEventListener('scroll', function() {
      btt.classList.toggle('visible', window.scrollY > 600);
    }, { passive: true });
    btt.addEventListener('click', function() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  /* Sticky CTA */
  var stickyCta = document.getElementById('stickyCta');
  if (stickyCta) {
    window.addEventListener('scroll', function() {
      stickyCta.classList.toggle('visible', window.scrollY > 800);
    }, { passive: true });
  }

  /* FAQ accordion */
  document.querySelectorAll('.faq-question').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var item = this.closest('.faq-item');
      var answer = item.querySelector('.faq-answer');
      var isActive = item.classList.contains('active');

      item.closest('.faq-list') && item.closest('.faq-list').querySelectorAll('.faq-item').forEach(function(fi) {
        fi.classList.remove('active');
        var a = fi.querySelector('.faq-answer');
        if (a) a.style.maxHeight = '0';
      });

      if (!isActive) {
        item.classList.add('active');
        answer.style.maxHeight = answer.scrollHeight + 'px';
      }
    });
  });

  /* Lazy load images */
  if ('IntersectionObserver' in window) {
    var imgObserver = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          var img = entry.target;
          if (img.dataset.src) {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
          }
          img.classList.add('loaded');
          imgObserver.unobserve(img);
        }
      });
    }, { rootMargin: '100px' });
    document.querySelectorAll('img[data-src]').forEach(function(img) {
      imgObserver.observe(img);
    });
  }

  /* Exit intent popup - ONLY on homepage, ONLY for first-time visitors */
  var exitShown = false;
  var isHomepage = window.location.pathname === '/' || window.location.pathname === '/index.html' || window.location.pathname === '/index';
  var hasSeenPopup = localStorage.getItem('zurihub_popup_shown');
  
  if (isHomepage && !hasSeenPopup) {
    document.addEventListener('mouseout', function(e) {
      if (!e.toElement && !e.relatedTarget && !exitShown && window.scrollY > 300) {
        var popup = document.getElementById('exitPopup');
        if (popup) {
          popup.classList.add('active');
          exitShown = true;
          localStorage.setItem('zurihub_popup_shown', 'true');
        }
      }
    });
    
    // Also show popup after 45 seconds if user hasn't seen it yet (for mobile/touchscreen)
    setTimeout(function() {
      if (!exitShown && !localStorage.getItem('zurihub_popup_shown')) {
        var popup = document.getElementById('exitPopup');
        if (popup && window.scrollY > 200) {
          popup.classList.add('active');
          exitShown = true;
          localStorage.setItem('zurihub_popup_shown', 'true');
        }
      }
    }, 45000);
  }

  /* Counter animation */
  document.querySelectorAll('[data-count]').forEach(function(el) {
    var counted = false;
    var obs = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting && !counted) {
          counted = true;
          animateCounter(el, parseInt(el.dataset.count));
          obs.unobserve(el);
        }
      });
    }, { threshold: 0.5 });
    obs.observe(el);
  });

  /* Portfolio filter */
  document.querySelectorAll('.portfolio-filter-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.portfolio-filter-btn').forEach(function(b) { b.classList.remove('active'); });
      this.classList.add('active');
      var filter = this.dataset.filter;
      document.querySelectorAll('[data-category]').forEach(function(card) {
        if (filter === 'all' || card.dataset.category === filter) {
          card.style.display = '';
          card.style.animation = 'fadeInStep 400ms ease forwards';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });

  /* Testimonial slider */
  var slider = document.querySelector('.testimonial-slider');
  if (slider) {
    var slides = slider.querySelectorAll('.testimonial-slide');
    var currentSlide = 0;
    var totalSlides = slides.length;
    var dots = document.querySelectorAll('.testimonial-dot');

    function showSlide(index) {
      slides.forEach(function(s, i) {
        s.style.display = i === index ? 'block' : 'none';
      });
      dots.forEach(function(d, i) {
        d.classList.toggle('active', i === index);
      });
    }

    if (totalSlides > 0) {
      showSlide(0);
      setInterval(function() {
        currentSlide = (currentSlide + 1) % totalSlides;
        showSlide(currentSlide);
      }, 5000);
    }

    dots.forEach(function(dot, i) {
      dot.addEventListener('click', function() {
        currentSlide = i;
        showSlide(i);
      });
    });
  }

  /* Multi-step form */
  var formSteps = document.querySelectorAll('.form-step');
  if (formSteps.length > 1) {
    var currentStep = 0;
    var stepDots = document.querySelectorAll('.step-dot');

    function updateStep(step) {
      formSteps.forEach(function(s, i) {
        s.classList.toggle('active', i === step);
      });
      stepDots.forEach(function(d, i) {
        d.classList.remove('active', 'completed');
        if (i < step) d.classList.add('completed');
        if (i === step) d.classList.add('active');
      });
      currentStep = step;
    }

    document.querySelectorAll('.next-step').forEach(function(btn) {
      btn.addEventListener('click', function() {
        if (currentStep < formSteps.length - 1) updateStep(currentStep + 1);
      });
    });

    document.querySelectorAll('.prev-step').forEach(function(btn) {
      btn.addEventListener('click', function() {
        if (currentStep > 0) updateStep(currentStep - 1);
      });
    });
  }

  /* Page loader */
  var loader = document.getElementById('pageLoader');
  if (loader) {
    window.addEventListener('load', function() {
      setTimeout(function() {
        loader.classList.add('hidden');
        setTimeout(function() { loader.remove(); }, 400);
      }, 300);
    });
  }

  /* Smooth scroll for anchor links */
  document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
    anchor.addEventListener('click', function(e) {
      var target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
});

function animateCounter(el, target) {
  var duration = 2000;
  var start = 0;
  var startTime = null;

  function step(timestamp) {
    if (!startTime) startTime = timestamp;
    var progress = Math.min((timestamp - startTime) / duration, 1);
    var eased = 1 - Math.pow(1 - progress, 3);
    var current = Math.floor(eased * target);
    el.textContent = current;
    if (progress < 1) requestAnimationFrame(step);
    else el.textContent = target;
  }

  requestAnimationFrame(step);
}

function closeExitPopup() {
  var popup = document.getElementById('exitPopup');
  if (popup) popup.classList.remove('active');
}

/* Blog slider for homepage */
function initBlogSlider() {
  var container = document.querySelector('.blog-slider-track');
  if (!container) return;
  var items = container.children;
  var index = 0;

  setInterval(function() {
    index = (index + 1) % items.length;
    container.style.transform = 'translateX(-' + (index * 100 / 3) + '%)';
  }, 4000);
}
