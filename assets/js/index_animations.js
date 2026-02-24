// =========================================
// MOBILE MENU TOGGLE
// =========================================
(function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mainNav = document.getElementById('mainNav');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const body = document.body;

    function toggleMobileMenu() {
        mobileMenuToggle.classList.toggle('active');
        mainNav.classList.toggle('active');
        mobileOverlay.classList.toggle('active');
        body.style.overflow = mainNav.classList.contains('active') ? 'hidden' : '';
    }

    function closeMobileMenu() {
        mobileMenuToggle.classList.remove('active');
        mainNav.classList.remove('active');
        mobileOverlay.classList.remove('active');
        body.style.overflow = '';
    }

    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', toggleMobileMenu);
    }

    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', closeMobileMenu);
    }

    // Close menu when clicking nav links
    document.querySelectorAll('.idx-hdr-nav-link').forEach(link => {
        link.addEventListener('click', closeMobileMenu);
    });

    // Close menu on window resize to desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            closeMobileMenu();
        }
    });
})();
// =========================================
// SCROLL EFFECTS FOR HEADER
// =========================================
window.addEventListener('scroll', function() {
    const header = document.getElementById('mainHeader');
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// =========================================
// SMOOTH SCROLL FOR ANCHOR LINKS
// =========================================
document.querySelectorAll('.idx-scroll-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        const targetSection = document.querySelector(targetId);
        
        if (targetSection) {
            const header = document.getElementById('mainHeader');
            const headerHeight = header.offsetHeight;
            const isMobile = window.innerWidth <= 768;
            const offset = isMobile ? 20 : 20;
            const targetPosition = targetSection.offsetTop - headerHeight - offset;
            
            // Close mobile menu if open
            closeMobileMenu();
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
});

// =========================================
// INTERSECTION OBSERVER OPTIONS
// =========================================
const observerOptions = {
    threshold: 0.15,
    rootMargin: '0px 0px -50px 0px'
};

// =========================================
// TRUST SECTION ANIMATIONS
// =========================================
const trustObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
            setTimeout(() => {
                entry.target.style.opacity = '0';
                entry.target.style.transform = 'translateY(20px)';
                entry.target.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, 50);
            }, index * 150);
            
            trustObserver.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observe trust items
document.querySelectorAll('.idx-trust-item').forEach(item => {
    trustObserver.observe(item);
});


// =========================================
// CARD FADE-IN ANIMATIONS
// =========================================
const cardObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
            setTimeout(() => {
                entry.target.style.opacity = '0';
                entry.target.style.transform = 'translateY(30px)';
                entry.target.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                
                // Trigger animation
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, 50);
            }, index * 100); // Stagger effect
            
            cardObserver.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observe all cards
document.querySelectorAll('.idx-card').forEach(card => {
    cardObserver.observe(card);
});

// =========================================
// PARALLAX EFFECT FOR HERO SECTION
// =========================================
window.addEventListener('scroll', () => {
    const hero = document.querySelector('.idx-hero');
    if (hero) {
        const scrolled = window.pageYOffset;
        const parallaxSpeed = 0.5;
        hero.style.backgroundPositionY = scrolled * parallaxSpeed + 'px';
    }
});

// =========================================
// SCROLL INDICATOR CLICK
// =========================================
const scrollIndicator = document.querySelector('.idx-scroll-indicator');
if (scrollIndicator) {
    scrollIndicator.addEventListener('click', () => {
        const servicesSection = document.getElementById('services');
        if (servicesSection) {
            const header = document.getElementById('mainHeader');
            const headerHeight = header.offsetHeight;
            const targetPosition = servicesSection.offsetTop - headerHeight - 20;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
}

// =========================================
// FADE IN ON SCROLL FOR SECTIONS
// =========================================
const fadeInObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            fadeInObserver.unobserve(entry.target);
        }
    });
}, {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
});

// Add fade-in-section class to sections that should animate
const sectionsToAnimate = [
    document.getElementById('services'),
    document.getElementById('features'),
    document.querySelector('.idx-cta-section')
];

sectionsToAnimate.forEach(section => {
    if (section) {
        section.classList.add('fade-in-section');
        fadeInObserver.observe(section);
    }
});

// =========================================
// HOVER EFFECTS FOR SERVICE CARDS
// =========================================
document.querySelectorAll('.idx-service-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-15px) scale(1.02)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});

// =========================================
// LOADING ANIMATION
// =========================================
window.addEventListener('load', () => {
    // Trigger hero animations after page load
    const heroContent = document.querySelector('.idx-hero-content');
    if (heroContent) {
        heroContent.style.opacity = '0';
        setTimeout(() => {
            heroContent.style.transition = 'opacity 1s ease';
            heroContent.style.opacity = '1';
        }, 100);
    }
});

// =========================================
// MOBILE MENU ENHANCEMENTS
// =========================================
function isMobile() {
    return window.innerWidth <= 768;
}

// Add mobile-specific interactions
if (isMobile()) {
    document.querySelectorAll('.idx-hdr-nav-link').forEach(link => {
        link.addEventListener('click', function() {
            // Add ripple effect on mobile
            const ripple = document.createElement('span');
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(255,255,255,0.5)';
            ripple.style.width = ripple.style.height = '10px';
            ripple.style.animation = 'ripple 0.6s ease-out';
            
            this.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });
    });
}

// =========================================
// PRELOAD ANIMATIONS
// =========================================
document.addEventListener('DOMContentLoaded', () => {
    // Add initial animation states
    const animatedElements = document.querySelectorAll('.animate-fade-in-up');
    animatedElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
    });
    
    // Trigger animations
    setTimeout(() => {
        animatedElements.forEach((element, index) => {
            setTimeout(() => {
                element.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 200);
        });
    }, 100);
});

// =========================================
// PERFORMANCE OPTIMIZATION
// =========================================
// Debounce scroll events for better performance
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Apply debounce to scroll handlers
const debouncedScroll = debounce(() => {
    // Your scroll logic here
}, 10);

window.addEventListener('scroll', debouncedScroll);

// =========================================
// ACCESSIBILITY ENHANCEMENTS
// =========================================
// Add keyboard navigation support
document.querySelectorAll('.idx-card, .idx-hdr-nav-link').forEach(element => {
    element.setAttribute('tabindex', '0');
    
    element.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            this.click();
        }
    });
});

// =========================================
// CONSOLE GREETING
// =========================================
console.log('%c🎨 RGA Frames - Custom Framing & Printing', 'font-size: 20px; font-weight: bold; color: #0f473a;');
console.log('%cWelcome to our website! We hope you enjoy browsing our services.', 'font-size: 14px; color: #2d6a5f;');