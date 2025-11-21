// ========================================
// PAGE LOADING ANIMATION
// ========================================

// Show loading animation on page load
window.addEventListener('load', function() {
    const loader = document.querySelector('.page-loader');
    
    // 🛑 FIX 1: Mag-check muna kung nahanap ang loader
    if (!loader) return; 

    // Hide loader after 2 seconds
    setTimeout(() => {
        loader.classList.add('fade-out');
        
        // Remove loader from DOM after fade animation
        setTimeout(() => {
            loader.style.display = 'none';
        }, 500);
    }, 2000);
});

// Show loading animation when clicking links
document.addEventListener('DOMContentLoaded', function() {
    // Get all navigation links
    const links = document.querySelectorAll('a:not([href^="#"]):not([target="_blank"])');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            // Only show loader for internal links
            const href = this.getAttribute('href');
            if (href && !href.startsWith('#') && !href.startsWith('http')) {
                e.preventDefault();
                
                // Show loader
                const loader = document.querySelector('.page-loader');
                
                // 🛑 FIX 2: Tiyakin na may loader bago gamitin ang style (Ito ang nag-fix sa line 75 error)
                if (loader) {
                        loader.style.display = 'flex';
                        loader.classList.remove('fade-out');
                }

                // Navigate after 500ms
                setTimeout(() => {
                    window.location.href = href;
                }, 500);
            }
        });
    });
});

// ========================================
// SMOOTH SCROLL FOR ANCHOR LINKS
// ========================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// ========================================
// NAVBAR SCROLL EFFECT
// ========================================
let lastScroll = 0;
const navbar = document.querySelector('.navbar');

window.addEventListener('scroll', () => {
    // 🛑 FIX 3: Tiyakin na may navbar bago gamitin ang style
    if (!navbar) return;

    const currentScroll = window.pageYOffset;
    
    if (currentScroll > 100) {
        navbar.style.background = 'rgba(15, 23, 42, 0.98)';
        navbar.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.3)';
    } else {
        navbar.style.background = 'rgba(15, 23, 42, 0.95)';
        navbar.style.boxShadow = 'none';
    }
    
    lastScroll = currentScroll;
});

// ========================================
// INTERSECTION OBSERVER FOR ANIMATIONS
// ========================================
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
        }
    });
}, observerOptions);

// Observe elements with animation
document.querySelectorAll('.feature-card, .team-card, .step, .mission-card').forEach(el => {
    observer.observe(el);
});

// ========================================
// MOBILE MENU TOGGLE (for future use)
// ========================================
const menuToggle = document.querySelector('.menu-toggle');
const navMenu = document.querySelector('.nav-menu');

if (menuToggle && navMenu) { // Added check for navMenu just in case
    menuToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
    });
}

// ========================================
// STATS COUNTER ANIMATION
// ========================================
function animateCounter(element, target) {
    let current = 0;
    const increment = target / 100;
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 20);
}

// Animate stats on scroll
const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const stat = entry.target;
            const target = parseInt(stat.textContent.replace(/[^0-9]/g, ''));
            animateCounter(stat, target);
            statsObserver.unobserve(stat);
        }
    });
}, { threshold: 0.5 });

document.querySelectorAll('.stat h3').forEach(stat => {
    statsObserver.observe(stat);
});

// ========================================
// LOGOUT MODAL FUNCTIONALITY (Dinamagdag)
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    // Kukunin ang mga elements
    const logoutModal = document.getElementById('logoutModal');
    const openLogoutBtn = document.getElementById('openLogoutModal');
    const cancelLogoutBtn = document.getElementById('cancelLogout');
    const confirmLogoutBtn = document.getElementById('confirmLogout');

    // 1. Open Modal when sidebar button is clicked
    if (openLogoutBtn && logoutModal) {
        openLogoutBtn.addEventListener('click', function(e) {
            e.preventDefault(); 
            logoutModal.classList.add('active'); 
        });
    }

    // 2. Close Modal when Cancel is clicked
    if (cancelLogoutBtn && logoutModal) {
        cancelLogoutBtn.addEventListener('click', function() {
            logoutModal.classList.remove('active');
        });
    }

    // 3. Confirm Logout: Redirect sa logout.php (May kasamang loader effect)
    if (confirmLogoutBtn) {
        confirmLogoutBtn.addEventListener('click', function() {
            // Ipakita ang page loader bago mag-redirect
            const loader = document.querySelector('.page-loader');
            if (loader) {
                loader.style.display = 'flex';
                loader.classList.remove('fade-out');
            }

            // Mag-redirect sa logout script pagkatapos ng 500ms
            setTimeout(() => {
                window.location.href = 'auth.php'; 
            }, 500); 
        });
    }

    // 4. Optional: Close modal when clicking the dark overlay
    if (logoutModal) {
        logoutModal.addEventListener('click', function(e) {
            // Tiyakin na ang click ay sa mismong overlay
            if (e.target.id === 'logoutModal') {
                logoutModal.classList.remove('active');
            }
        });
    }
});