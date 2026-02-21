/**
 * Zurihub Technology — Global SEO Enhancer
 * Runs on EVERY page. Injects:
 * - Hreflang alternate tags (en, en-US, en-KE, en-GB, en-AU, en-CA)
 * - Robots meta with max snippet/image settings
 * - Breadcrumb structured data (auto-generated from URL)
 * - Local Business schema signals
 * - Preconnect hints
 * - Open Graph locale alternates
 */

(function() {
  'use strict';

  var BASE_URL = 'https://zurihub.co.ke';
  var currentUrl = BASE_URL + window.location.pathname.replace(/\.html$/, '').replace(/\/$/, '') || BASE_URL + '/';

  // Normalize URL
  if (currentUrl === BASE_URL) currentUrl = BASE_URL + '/';

  /* ===== 1. HREFLANG INJECTION ===== */
  function injectHreflang() {
    var langs = [
      { hreflang: 'en',         href: currentUrl },
      { hreflang: 'en-us',      href: currentUrl },
      { hreflang: 'en-ke',      href: currentUrl },
      { hreflang: 'en-gb',      href: currentUrl },
      { hreflang: 'en-au',      href: currentUrl },
      { hreflang: 'en-ca',      href: currentUrl },
      { hreflang: 'en-za',      href: currentUrl },
      { hreflang: 'en-ng',      href: currentUrl },
      { hreflang: 'x-default',  href: currentUrl }
    ];

    langs.forEach(function(l) {
      // Don't duplicate if already exists
      if (document.querySelector('link[hreflang="' + l.hreflang + '"]')) return;
      var link = document.createElement('link');
      link.rel = 'alternate';
      link.hreflang = l.hreflang;
      link.href = l.href;
      document.head.appendChild(link);
    });
  }

  /* ===== 2. ROBOTS META ===== */
  function injectRobotsMeta() {
    if (document.querySelector('meta[name="robots"]')) return;
    var meta = document.createElement('meta');
    meta.name = 'robots';
    meta.content = 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1';
    document.head.appendChild(meta);

    var meta2 = document.createElement('meta');
    meta2.name = 'googlebot';
    meta2.content = 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1';
    document.head.appendChild(meta2);
  }

  /* ===== 3. OG LOCALE ALTERNATES ===== */
  function injectOGLocale() {
    if (document.querySelector('meta[property="og:locale"]')) return;
    var ogLocale = document.createElement('meta');
    ogLocale.setAttribute('property', 'og:locale');
    ogLocale.content = 'en_US';
    document.head.appendChild(ogLocale);

    var alts = ['en_KE', 'en_GB', 'en_AU'];
    alts.forEach(function(loc) {
      var m = document.createElement('meta');
      m.setAttribute('property', 'og:locale:alternate');
      m.content = loc;
      document.head.appendChild(m);
    });
  }

  /* ===== 4. DYNAMIC BREADCRUMB SCHEMA ===== */
  function injectBreadcrumbSchema() {
    var path = window.location.pathname.replace(/\.html$/, '');
    var parts = path.split('/').filter(Boolean);
    var items = [
      { '@type': 'ListItem', position: 1, name: 'Home', item: BASE_URL + '/' }
    ];

    var accum = '';
    parts.forEach(function(part, idx) {
      accum += '/' + part;
      var name = part.replace(/-/g, ' ').replace(/\b\w/g, function(c) { return c.toUpperCase(); });
      items.push({ '@type': 'ListItem', position: idx + 2, name: name, item: BASE_URL + accum });
    });

    if (items.length < 2) return; // Don't add for homepage

    // Check if breadcrumb schema already exists
    var existing = document.querySelectorAll('script[type="application/ld+json"]');
    for (var i = 0; i < existing.length; i++) {
      try {
        var parsed = JSON.parse(existing[i].textContent);
        if (parsed['@type'] === 'BreadcrumbList') return;
      } catch(e) {}
    }

    var script = document.createElement('script');
    script.type = 'application/ld+json';
    script.textContent = JSON.stringify({
      '@context': 'https://schema.org',
      '@type': 'BreadcrumbList',
      'itemListElement': items
    });
    document.head.appendChild(script);
  }

  /* ===== 5. GLOBAL ORGANIZATION SCHEMA (light version for all pages) ===== */
  function injectOrganizationSchema() {
    // Only inject if not already on homepage (homepage has full version)
    if (window.location.pathname === '/' || window.location.pathname === '/index.html') return;

    var existing = document.querySelectorAll('script[type="application/ld+json"]');
    for (var i = 0; i < existing.length; i++) {
      try {
        var parsed = JSON.parse(existing[i].textContent);
        if (parsed['@type'] === 'Organization' || parsed['@type'] === 'ProfessionalService') return;
      } catch(e) {}
    }

    var script = document.createElement('script');
    script.type = 'application/ld+json';
    script.textContent = JSON.stringify({
      '@context': 'https://schema.org',
      '@type': 'Organization',
      'name': 'Zurihub Technology',
      'url': BASE_URL,
      'logo': BASE_URL + '/assets/zurihub-logo.png',
      'contactPoint': {
        '@type': 'ContactPoint',
        'telephone': '+254758256440',
        'contactType': 'sales',
        'email': 'info@zurihub.co.ke',
        'areaServed': 'Worldwide',
        'availableLanguage': ['English', 'Swahili']
      },
      'sameAs': [
        'https://facebook.com/zurihub',
        'https://twitter.com/zurihub',
        'https://linkedin.com/company/zurihub',
        'https://instagram.com/zurihub'
      ]
    });
    document.head.appendChild(script);
  }

  /* ===== 6. PRECONNECT HINTS ===== */
  function injectPreconnects() {
    var hints = [
      { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
      { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: true },
      { rel: 'dns-prefetch', href: 'https://cdn.tailwindcss.com' }
    ];
    hints.forEach(function(hint) {
      if (document.querySelector('link[href="' + hint.href + '"]')) return;
      var link = document.createElement('link');
      link.rel = hint.rel;
      link.href = hint.href;
      if (hint.crossorigin) link.crossOrigin = 'anonymous';
      document.head.appendChild(link);
    });
  }

  /* ===== 7. THEME COLOR (for mobile browsers) ===== */
  function injectThemeColor() {
    if (document.querySelector('meta[name="theme-color"]')) return;
    var meta = document.createElement('meta');
    meta.name = 'theme-color';
    meta.content = '#101F4C';
    document.head.appendChild(meta);
  }

  /* ===== 8. AUTHOR META ===== */
  function injectAuthorMeta() {
    if (document.querySelector('meta[name="author"]')) return;
    var meta = document.createElement('meta');
    meta.name = 'author';
    meta.content = 'Zurihub Technology';
    document.head.appendChild(meta);
  }

  /* ===== 9. MANIFEST LINK ===== */
  function injectManifest() {
    if (document.querySelector('link[rel="manifest"]')) return;
    var link = document.createElement('link');
    link.rel = 'manifest';
    link.href = '/manifest.json';
    document.head.appendChild(link);
  }

  /* ===== 10. SITEMAP LINK ===== */
  function injectSitemapLink() {
    if (document.querySelector('link[rel="sitemap"]')) return;
    var link = document.createElement('link');
    link.rel = 'sitemap';
    link.type = 'application/xml';
    link.href = '/sitemap_index.xml';
    document.head.appendChild(link);
  }

  /* ===== 11. SEARCH ENGINE VERIFICATION ===== */
  function injectVerificationMeta() {
    // Google - Replace with your actual verification code
    if (!document.querySelector('meta[name="google-site-verification"]')) {
      var google = document.createElement('meta');
      google.name = 'google-site-verification';
      google.content = 'REPLACE_WITH_YOUR_GOOGLE_VERIFICATION_CODE';
      document.head.appendChild(google);
    }
    // Bing
    if (!document.querySelector('meta[name="msvalidate.01"]')) {
      var bing = document.createElement('meta');
      bing.name = 'msvalidate.01';
      bing.content = 'REPLACE_WITH_YOUR_BING_VERIFICATION_CODE';
      document.head.appendChild(bing);
    }
    // Yandex
    if (!document.querySelector('meta[name="yandex-verification"]')) {
      var yandex = document.createElement('meta');
      yandex.name = 'yandex-verification';
      yandex.content = 'REPLACE_WITH_YOUR_YANDEX_CODE';
      document.head.appendChild(yandex);
    }
  }

  /* ===== 12. OPEN SEARCH ===== */
  function injectOpenSearch() {
    if (document.querySelector('link[type="application/opensearchdescription+xml"]')) return;
    var link = document.createElement('link');
    link.rel = 'search';
    link.type = 'application/opensearchdescription+xml';
    link.title = 'Zurihub Technology';
    link.href = '/opensearch.xml';
    document.head.appendChild(link);
  }

  /* ===== 13. STRUCTURED DATA FOR SERVICE PAGES ===== */
  function injectServiceSchema() {
    var path = window.location.pathname.replace(/\.html$/, '');
    var servicePages = {
      '/web-development': { name: 'Web Development Services', description: 'Professional web development services including custom websites, ecommerce, WordPress, and Shopify development.' },
      '/software-development': { name: 'Custom Software Development', description: 'Custom software solutions including CRM, ERP, POS systems and business automation software.' },
      '/seo-services': { name: 'SEO Services', description: 'Search engine optimization services to improve your website ranking and organic traffic.' },
      '/digital-marketing': { name: 'Digital Marketing Services', description: 'Comprehensive digital marketing including social media, PPC, content marketing and email campaigns.' },
      '/crm-system': { name: 'CRM System Development', description: 'Custom CRM solutions for managing customer relationships, sales pipelines and business processes.' },
      '/pos-system': { name: 'POS System Development', description: 'Point of sale systems for retail, restaurants, supermarkets and hospitality businesses.' },
      '/erp-system': { name: 'ERP System Development', description: 'Enterprise resource planning solutions for manufacturing, distribution and service businesses.' },
      '/ecommerce-development': { name: 'Ecommerce Development', description: 'Online store development with payment integration, inventory management and order processing.' }
    };

    if (!servicePages[path]) return;

    // Check if Service schema already exists
    var existing = document.querySelectorAll('script[type="application/ld+json"]');
    for (var i = 0; i < existing.length; i++) {
      try {
        var parsed = JSON.parse(existing[i].textContent);
        if (parsed['@type'] === 'Service') return;
      } catch(e) {}
    }

    var service = servicePages[path];
    var script = document.createElement('script');
    script.type = 'application/ld+json';
    script.textContent = JSON.stringify({
      '@context': 'https://schema.org',
      '@type': 'Service',
      'name': service.name,
      'description': service.description,
      'provider': {
        '@type': 'Organization',
        'name': 'Zurihub Technology',
        'url': BASE_URL
      },
      'areaServed': ['Worldwide', 'United States', 'Kenya', 'United Kingdom', 'Canada', 'Australia'],
      'serviceType': service.name
    });
    document.head.appendChild(script);
  }

  /* ===== RUN ALL ===== */
  injectHreflang();
  injectRobotsMeta();
  injectOGLocale();
  injectBreadcrumbSchema();
  injectOrganizationSchema();
  injectPreconnects();
  injectThemeColor();
  injectAuthorMeta();
  injectManifest();
  injectSitemapLink();
  injectVerificationMeta();
  injectOpenSearch();
  injectServiceSchema();

})();
