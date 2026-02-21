/* Zurihub Technology - Shared Components */

function getHeader(activePage) {
  return `
  <header class="site-header" id="siteHeader" role="banner">
    <div class="header-inner max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-[80px]">
      <a href="/" class="flex-shrink-0" aria-label="Zurihub Technology Home">
        <img src="/assets/zurihub_technology_logo_no_bg.png" alt="Zurihub Technology" class="header-logo-light" style="height:138px;width:auto;margin:-34px 0" width="276" height="138">
        <img src="/assets/zurihub Technology No bg Logo.png" alt="Zurihub Technology" class="header-logo-dark" style="height:138px;width:auto;margin:-34px 0" width="276" height="138">
      </a>
      <nav class="hidden lg:flex items-center gap-8" role="navigation" aria-label="Main navigation">
        <a href="/" class="nav-link ${activePage==='home'?'active':''}">Home</a>
        <div class="nav-item relative">
          <a href="/about" class="nav-link ${activePage==='about'?'active':''}">About <svg class="inline w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></a>
          <div class="nav-dropdown">
            <a href="/about">About Zurihub</a>
            <a href="/mission-vision">Mission & Vision</a>
            <a href="/testimonials">Testimonials</a>
            <a href="/faq">FAQs</a>
            <a href="/career">Careers</a>
          </div>
        </div>
        <div class="nav-item relative">
          <a href="/services" class="nav-link ${activePage==='services'?'active':''}">Services <svg class="inline w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></a>
          <div class="mega-menu">
            <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
              <div class="grid grid-cols-4 gap-8">
                <div class="mega-menu-col">
                  <h4>Web Services</h4>
                  <a href="/web-development">Web Development</a>
                  <a href="/ecommerce-development">Ecommerce Development</a>
                  <a href="/shopify-development">Shopify Development</a>
                  <a href="/wordpress-development">WordPress Development</a>
                  <a href="/website-maintenance">Website Maintenance</a>
                </div>
                <div class="mega-menu-col">
                  <h4>Software Systems</h4>
                  <a href="/software-development">Software Development</a>
                  <a href="/crm-system">CRM Systems</a>
                  <a href="/pos-system">POS Systems</a>
                  <a href="/erp-system">ERP Systems</a>
                  <a href="/lms-system">LMS Systems</a>
                </div>
                <div class="mega-menu-col">
                  <h4>Industry Solutions</h4>
                  <a href="/real-estate-crm">Real Estate CRM</a>
                  <a href="/law-firm-crm">Law Firm CRM</a>
                  <a href="/hotel-restaurant-system">Hotel & Restaurant</a>
                  <a href="/supermarket-pos-system">Supermarket POS</a>
                  <a href="/manufacturing-management-system">Manufacturing</a>
                  <a href="/banking-loan-system">Banking & Loan</a>
                  <a href="/gate-management-system">Gate Management</a>
                  <a href="/property-management-system">Property Management</a>
                </div>
                <div class="mega-menu-col">
                  <h4>Digital Marketing</h4>
                  <a href="/seo-services">SEO Services</a>
                  <a href="/digital-marketing">Digital Marketing</a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <a href="/portfolio" class="nav-link ${activePage==='portfolio'?'active':''}">Portfolio</a>
        <a href="/blog" class="nav-link ${activePage==='blog'?'active':''}">Blog</a>
        <a href="/contact" class="nav-link ${activePage==='contact'?'active':''}">Contact</a>
      </nav>
      <div class="hidden lg:flex items-center gap-3">
        <a href="/quotation" class="btn btn-primary btn-sm">Get a Quote</a>
      </div>
      <button class="lg:hidden p-2 mobile-menu-btn" id="mobileMenuBtn" aria-label="Open menu" aria-expanded="false">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
    </div>
  </header>
  <div class="mobile-overlay" id="mobileOverlay"></div>
  <nav class="mobile-nav" id="mobileNav" role="navigation" aria-label="Mobile navigation">
    <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100">
      <div class="mobile-logo-wrap" style="max-width:140px;overflow:hidden">
        <img src="/assets/zurihub Technology No bg Logo.png" alt="Zurihub" class="mobile-nav-logo" width="140" height="50">
      </div>
      <button id="mobileCloseBtn" class="p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100" aria-label="Close menu">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <a href="/" class="mobile-nav-link">Home</a>
    <div>
      <button class="mobile-nav-link flex items-center justify-between w-full mobile-toggle" data-target="mob-about">
        About <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="mobile-sub-menu" id="mob-about">
        <a href="/about" class="mobile-sub-link">About Zurihub</a>
        <a href="/mission-vision" class="mobile-sub-link">Mission & Vision</a>
        <a href="/testimonials" class="mobile-sub-link">Testimonials</a>
        <a href="/faq" class="mobile-sub-link">FAQs</a>
        <a href="/career" class="mobile-sub-link">Careers</a>
      </div>
    </div>
    <div>
      <button class="mobile-nav-link flex items-center justify-between w-full mobile-toggle" data-target="mob-services">
        Services <svg class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div class="mobile-sub-menu" id="mob-services">
        <a href="/services" class="mobile-sub-link font-semibold">All Services</a>
        <a href="/web-development" class="mobile-sub-link">Web Development</a>
        <a href="/software-development" class="mobile-sub-link">Software Development</a>
        <a href="/seo-services" class="mobile-sub-link">SEO Services</a>
        <a href="/digital-marketing" class="mobile-sub-link">Digital Marketing</a>
        <a href="/ecommerce-development" class="mobile-sub-link">Ecommerce</a>
        <a href="/shopify-development" class="mobile-sub-link">Shopify</a>
        <a href="/wordpress-development" class="mobile-sub-link">WordPress</a>
        <a href="/crm-system" class="mobile-sub-link">CRM Systems</a>
        <a href="/pos-system" class="mobile-sub-link">POS Systems</a>
        <a href="/erp-system" class="mobile-sub-link">ERP Systems</a>
      </div>
    </div>
    <a href="/portfolio" class="mobile-nav-link">Portfolio</a>
    <a href="/blog" class="mobile-nav-link">Blog</a>
    <a href="/contact" class="mobile-nav-link">Contact</a>
    <a href="/quotation" class="btn btn-primary w-full mt-4 text-center">Get a Quote</a>
    <div class="mt-6 pt-4 border-t border-gray-100">
      <a href="tel:+254758256440" class="flex items-center gap-2 text-sm text-gray-500 mb-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>+254 758 256 440</a>
      <a href="mailto:info@zurihub.co.ke" class="flex items-center gap-2 text-sm text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>info@zurihub.co.ke</a>
    </div>
  </nav>`;
}

function getFooter() {
  const year = new Date().getFullYear();
  return `
  <footer class="site-footer" role="contentinfo">
    <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
      <div class="footer-grid">
        <div class="footer-brand">
          <img src="/assets/zurihub_technology_logo_no_bg.png" alt="Zurihub Technology - Best SEO & Web Design Agency" style="height:165px;width:auto;margin:-38px 0" width="330" height="165" loading="lazy">
          <p>Zurihub Technology offers expert website development, custom software, ERP, CRM, and SEO solutions. We help businesses build powerful digital presences that drive growth and efficiency.</p>
          <div class="footer-social mt-4">
            <a href="https://facebook.com/zurihub" aria-label="Facebook" target="_blank" rel="noopener"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
            <a href="https://twitter.com/zurihub" aria-label="Twitter / X" target="_blank" rel="noopener"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
            <a href="https://linkedin.com/company/zurihub" aria-label="LinkedIn" target="_blank" rel="noopener"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg></a>
            <a href="https://instagram.com/zurihub" aria-label="Instagram" target="_blank" rel="noopener"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg></a>
          </div>
        </div>
        <div>
          <h3 class="footer-heading">Quick Links</h3>
          <div class="footer-links">
            <a href="/">Home</a>
            <a href="/about">About Us</a>
            <a href="/services">Services</a>
            <a href="/portfolio">Portfolio</a>
            <a href="/blog">Blog</a>
            <a href="/contact">Contact Us</a>
            <a href="/quotation">Get a Quote</a>
            <a href="/getting-started">Getting Started</a>
            <a href="/faq">FAQs</a>
          </div>
        </div>
        <div>
          <h3 class="footer-heading">Our Services</h3>
          <div class="footer-links">
            <a href="/web-development">Web Development</a>
            <a href="/software-development">Software Development</a>
            <a href="/seo-services">SEO Services</a>
            <a href="/digital-marketing">Digital Marketing</a>
            <a href="/crm-system">CRM Systems</a>
            <a href="/pos-system">POS Systems</a>
            <a href="/erp-system">ERP Systems</a>
            <a href="/ecommerce-development">Ecommerce Development</a>
          </div>
        </div>
        <div>
          <h3 class="footer-heading">Contact Info</h3>
          <div class="footer-contact-item">
            <svg class="footer-contact-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <div><a href="tel:+254758256440" class="hover:text-white transition">+254 758 256 440</a></div>
          </div>
          <div class="footer-contact-item">
            <svg class="footer-contact-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <div><a href="mailto:info@zurihub.co.ke" class="hover:text-white transition">info@zurihub.co.ke</a></div>
          </div>
          <div class="footer-contact-item">
            <svg class="footer-contact-icon w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <div>Ruiru, Kamakis,<br>Kiambu, Kenya</div>
          </div>
          <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid rgba(255,255,255,0.06)">
            <p style="color:rgba(255,255,255,0.35);font-size:0.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.75rem">Available Globally · Remote-First</p>
            <div style="display:flex;flex-wrap:wrap;gap:0.375rem">
              <span style="font-size:0.6875rem;color:rgba(255,255,255,0.4);padding:0.2rem 0.5rem;border:1px solid rgba(255,255,255,0.08);border-radius:4px">🇺🇸 USA</span>
              <span style="font-size:0.6875rem;color:rgba(255,255,255,0.4);padding:0.2rem 0.5rem;border:1px solid rgba(255,255,255,0.08);border-radius:4px">🇬🇧 UK</span>
              <span style="font-size:0.6875rem;color:rgba(255,255,255,0.4);padding:0.2rem 0.5rem;border:1px solid rgba(255,255,255,0.08);border-radius:4px">🇨🇦 Canada</span>
              <span style="font-size:0.6875rem;color:rgba(255,255,255,0.4);padding:0.2rem 0.5rem;border:1px solid rgba(255,255,255,0.08);border-radius:4px">🇰🇪 Kenya</span>
              <span style="font-size:0.6875rem;color:rgba(255,255,255,0.4);padding:0.2rem 0.5rem;border:1px solid rgba(255,255,255,0.08);border-radius:4px">🌍 Africa</span>
            </div>
          </div>
        </div>
      </div>
      <!-- Kenya City & International SEO Links -->
      <div style="padding:1.5rem 0;border-top:1px solid rgba(255,255,255,0.06);margin-top:0">
        <p style="color:rgba(255,255,255,0.3);font-size:0.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.875rem">Web Development Services — Kenya &amp; Global</p>
        <div style="display:flex;flex-wrap:wrap;gap:0.5rem">
          <a href="/web-development-nairobi" style="font-size:0.75rem;color:rgba(255,255,255,0.4);text-decoration:none;transition:color 200ms" onmouseover="this.style.color='#FB5041'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">Best Web Development Nairobi</a>
          <span style="color:rgba(255,255,255,0.15);font-size:0.75rem">·</span>
          <a href="/web-development-ruiru" style="font-size:0.75rem;color:rgba(255,255,255,0.4);text-decoration:none;transition:color 200ms" onmouseover="this.style.color='#FB5041'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">Web Design Agency Ruiru</a>
          <span style="color:rgba(255,255,255,0.15);font-size:0.75rem">·</span>
          <a href="/web-development-mombasa" style="font-size:0.75rem;color:rgba(255,255,255,0.4);text-decoration:none;transition:color 200ms" onmouseover="this.style.color='#FB5041'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">Web Development Mombasa</a>
          <span style="color:rgba(255,255,255,0.15);font-size:0.75rem">·</span>
          <a href="/web-development-nakuru" style="font-size:0.75rem;color:rgba(255,255,255,0.4);text-decoration:none;transition:color 200ms" onmouseover="this.style.color='#FB5041'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">Web Developers Nakuru</a>
          <span style="color:rgba(255,255,255,0.15);font-size:0.75rem">·</span>
          <a href="/web-development-eldoret" style="font-size:0.75rem;color:rgba(255,255,255,0.4);text-decoration:none;transition:color 200ms" onmouseover="this.style.color='#FB5041'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">Web Development Agency Eldoret</a>
          <span style="color:rgba(255,255,255,0.15);font-size:0.75rem">·</span>
          <a href="/seo-services-nairobi" style="font-size:0.75rem;color:rgba(255,255,255,0.4);text-decoration:none;transition:color 200ms" onmouseover="this.style.color='#FB5041'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">SEO Services Nairobi</a>
          <span style="color:rgba(255,255,255,0.15);font-size:0.75rem">·</span>
          <a href="/software-development-kenya" style="font-size:0.75rem;color:rgba(255,255,255,0.4);text-decoration:none;transition:color 200ms" onmouseover="this.style.color='#FB5041'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">Software Development Kenya</a>
          <span style="color:rgba(255,255,255,0.15);font-size:0.75rem">·</span>
          <a href="/crm-system" style="font-size:0.75rem;color:rgba(255,255,255,0.4);text-decoration:none;transition:color 200ms" onmouseover="this.style.color='#FB5041'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">CRM System Development</a>
          <span style="color:rgba(255,255,255,0.15);font-size:0.75rem">·</span>
          <a href="/pos-system" style="font-size:0.75rem;color:rgba(255,255,255,0.4);text-decoration:none;transition:color 200ms" onmouseover="this.style.color='#FB5041'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">POS System Kenya</a>
          <span style="color:rgba(255,255,255,0.15);font-size:0.75rem">·</span>
          <a href="/ecommerce-development-kenya" style="font-size:0.75rem;color:rgba(255,255,255,0.4);text-decoration:none;transition:color 200ms" onmouseover="this.style.color='#FB5041'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">Ecommerce Development Kenya</a>
          <span style="color:rgba(255,255,255,0.15);font-size:0.75rem">·</span>
          <a href="/web-development-usa" style="font-size:0.75rem;color:rgba(255,255,255,0.4);text-decoration:none;transition:color 200ms" onmouseover="this.style.color='#FB5041'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">Web Development Company USA</a>
          <span style="color:rgba(255,255,255,0.15);font-size:0.75rem">·</span>
          <a href="/web-development-kenya" style="font-size:0.75rem;color:rgba(255,255,255,0.4);text-decoration:none;transition:color 200ms" onmouseover="this.style.color='#FB5041'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">Web Development Kenya</a>
          <span style="color:rgba(255,255,255,0.15);font-size:0.75rem">·</span>
          <a href="/real-estate-crm" style="font-size:0.75rem;color:rgba(255,255,255,0.4);text-decoration:none;transition:color 200ms" onmouseover="this.style.color='#FB5041'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">Real Estate CRM System</a>
          <span style="color:rgba(255,255,255,0.15);font-size:0.75rem">·</span>
          <a href="/erp-system" style="font-size:0.75rem;color:rgba(255,255,255,0.4);text-decoration:none;transition:color 200ms" onmouseover="this.style.color='#FB5041'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">ERP Solutions Africa</a>
        </div>
      </div>
      <div class="footer-bottom">
        <p class="text-sm">&copy; ${year} Zurihub Technology. All rights reserved. Web Development &amp; Software Solutions — Global.</p>
        <div class="flex items-center gap-4 text-sm">
          <a href="/privacy-policy" class="hover:text-white transition">Privacy Policy</a>
          <a href="/terms-and-conditions" class="hover:text-white transition">Terms of Service</a>
          <a href="/sitemap.xml" class="hover:text-white transition" target="_blank" rel="noopener">Sitemap</a>
        </div>
      </div>
    </div>
  </footer>`;
}

function getStickyCTA() {
  return ``; // Disabled - no longer showing sticky CTA
}

function getWhatsApp() {
  return `<a href="https://wa.me/254758256440?text=Hello%20Zurihub,%20I%20would%20like%20to%20discuss%20a%20project." class="whatsapp-float" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp"><svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg></a>`;
}

function getBackToTop() {
  return `<button class="back-to-top" id="backToTop" aria-label="Scroll to top"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg></button>`;
}

function getExitPopup() {
  return `
  <div class="exit-popup" id="exitPopup" role="dialog" aria-modal="true" aria-label="Special offer">
    <div class="exit-popup-inner">
      <!-- Close button -->
      <button onclick="closeExitPopup()" class="exit-popup-close" aria-label="Close popup">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
      
      <!-- Left decorative side -->
      <div class="exit-popup-left">
        <div class="exit-popup-confetti"></div>
        <div class="exit-popup-icon-wrap">
          <div class="exit-popup-icon">
            <svg width="32" height="32" fill="none" stroke="#fff" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </div>
        </div>
        <div class="exit-popup-gift">
          <svg width="100" height="100" viewBox="0 0 100 100" fill="none">
            <rect x="10" y="25" width="80" height="55" rx="4" fill="#F5D5C8"/>
            <rect x="10" y="25" width="80" height="55" rx="4" stroke="#E8B4A0" stroke-width="2"/>
            <rect x="15" y="30" width="70" height="42" rx="2" fill="#fff"/>
            <rect x="20" y="35" width="30" height="4" rx="2" fill="#FB5041"/>
            <rect x="20" y="42" width="50" height="3" rx="1" fill="#E5E7EB"/>
            <rect x="20" y="48" width="45" height="3" rx="1" fill="#E5E7EB"/>
            <rect x="20" y="54" width="35" height="3" rx="1" fill="#E5E7EB"/>
            <rect x="20" y="62" width="25" height="6" rx="2" fill="#FB5041"/>
            <circle cx="70" cy="50" r="12" fill="#101F4C" opacity="0.1"/>
            <path d="M66 50l3 3 6-6" stroke="#FB5041" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
      </div>
      
      <!-- Right content side -->
      <div class="exit-popup-right">
        <div class="exit-popup-badge">LIMITED TIME OFFER</div>
        <h3 class="exit-popup-title">Get 20% OFF<br>Web Development!</h3>
        <p class="exit-popup-text">Launch your professional website with stunning design, fast performance & SEO optimization. First-time visitors save 20% on all web development packages!</p>
        <ul class="exit-popup-features">
          <li><svg width="16" height="16" fill="none" stroke="#22C55E" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Mobile-Responsive Design</li>
          <li><svg width="16" height="16" fill="none" stroke="#22C55E" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>SEO Optimized</li>
          <li><svg width="16" height="16" fill="none" stroke="#22C55E" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>Free SSL Certificate</li>
        </ul>
        <a href="/quotation" class="exit-popup-btn" onclick="closeExitPopup()">Claim Your 20% Discount</a>
        <p class="exit-popup-terms">Offer valid for new clients only. <a href="/pricing">View Pricing</a></p>
      </div>
    </div>
  </div>`;
}

function getCTASection() {
  return ``; // Disabled - no longer showing bottom CTA section
}

function getChatWidget() {
  return `<div id="zuri-chat-widget" class="zuri-chat-widget" aria-live="polite">
  <link rel="stylesheet" href="/css/chat-widget.css">
  <button type="button" id="zuri-chat-toggle" class="zuri-chat-toggle" aria-label="Open chat support">
    <span class="zuri-chat-toggle-icon">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
    </span>
    <span class="zuri-chat-toggle-label">Chat</span>
    <span class="zuri-chat-toggle-dot" id="zuri-chat-unread-dot" aria-hidden="true"></span>
  </button>
  <div id="zuri-chat-panel" class="zuri-chat-panel" hidden>
    <div class="zuri-chat-panel-header">
      <div class="zuri-chat-panel-title">
        <span class="zuri-chat-panel-avatar">Z</span>
        <div>
          <strong>Support</strong>
          <small>We typically reply in minutes</small>
        </div>
      </div>
      <button type="button" id="zuri-chat-close" class="zuri-chat-close" aria-label="Close chat">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="zuri-chat-panel-body">
      <div id="zuri-chat-welcome" class="zuri-chat-screen">
        <p class="zuri-chat-welcome-text">Start a conversation. Tell us your name and email to begin.</p>
        <div class="zuri-chat-form-group">
          <input type="text" id="zuri-chat-name" placeholder="Your name" autocomplete="name" class="zuri-chat-input">
          <input type="email" id="zuri-chat-email" placeholder="Your email" autocomplete="email" class="zuri-chat-input">
        </div>
        <button type="button" id="zuri-chat-start" class="zuri-chat-btn-primary">Start chat</button>
        <p class="zuri-chat-ticket-link">Need a support ticket? <button type="button" id="zuri-chat-show-ticket" class="zuri-chat-link-btn">Create a ticket</button></p>
      </div>
      <div id="zuri-chat-ticket-form" class="zuri-chat-screen" hidden>
        <p class="zuri-chat-welcome-text">Submit a support ticket and we’ll get back to you.</p>
        <input type="text" id="zuri-ticket-subject" placeholder="Subject" class="zuri-chat-input">
        <textarea id="zuri-ticket-message" placeholder="Describe your issue..." rows="4" class="zuri-chat-input"></textarea>
        <div class="zuri-chat-form-group">
          <input type="text" id="zuri-ticket-name" placeholder="Your name" class="zuri-chat-input">
          <input type="email" id="zuri-ticket-email" placeholder="Your email" class="zuri-chat-input">
        </div>
        <button type="button" id="zuri-ticket-submit" class="zuri-chat-btn-primary">Submit ticket</button>
        <button type="button" id="zuri-ticket-back" class="zuri-chat-link-btn">← Back to chat</button>
      </div>
      <div id="zuri-chat-thread" class="zuri-chat-screen" hidden>
        <div id="zuri-chat-messages" class="zuri-chat-messages"></div>
        <div class="zuri-chat-compose">
          <textarea id="zuri-chat-input" placeholder="Type a message..." rows="2" class="zuri-chat-input" maxlength="2000"></textarea>
          <button type="button" id="zuri-chat-send" class="zuri-chat-send-btn" aria-label="Send message">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
          </button>
        </div>
        <button type="button" id="zuri-chat-create-ticket-from-chat" class="zuri-chat-link-btn mt-2">Create a support ticket from this chat</button>
      </div>
    </div>
  </div>
</div>`;
}

function initComponents(activePage) {
  document.getElementById('header-placeholder').innerHTML = getHeader(activePage);
  document.getElementById('footer-placeholder').innerHTML = getFooter();

  const stickyCta = document.getElementById('sticky-cta-placeholder');
  if (stickyCta) stickyCta.innerHTML = getStickyCTA();

  const whatsapp = document.getElementById('whatsapp-placeholder');
  if (whatsapp) whatsapp.innerHTML = getWhatsApp();

  const btt = document.getElementById('back-to-top-placeholder');
  if (btt) btt.innerHTML = getBackToTop();

  const exit = document.getElementById('exit-popup-placeholder');
  if (exit) exit.innerHTML = getExitPopup();

  const ctaSec = document.getElementById('cta-section-placeholder');
  if (ctaSec) ctaSec.innerHTML = getCTASection();

  // Chat support widget (inject once, append to body if no placeholder)
  let chatContainer = document.getElementById('chat-support-placeholder');
  if (!chatContainer) {
    chatContainer = document.createElement('div');
    chatContainer.id = 'chat-support-placeholder';
    document.body.appendChild(chatContainer);
  }
  chatContainer.innerHTML = getChatWidget();
  if (!document.querySelector('script[src*="chat-widget.js"]')) {
    var cw = document.createElement('script');
    cw.src = '/js/chat-widget.js';
    cw.async = true;
    document.body.appendChild(cw);
  } else if (window.initZuriChatWidget) {
    window.initZuriChatWidget();
  }

  // === LOAD SEO ENHANCER ON ALL PAGES ===
  const seoScript = document.createElement('script');
  seoScript.src = '/js/seo.js';
  seoScript.defer = true;
  document.head.appendChild(seoScript);
}
