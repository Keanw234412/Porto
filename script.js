<<<<<<< HEAD
document.addEventListener('DOMContentLoaded', function() {
            initializePortfolio();
        });

        function initializePortfolio() {
            initParticles();
            initCustomCursor();
            initLoadingScreen();
            initThemeToggle();
            initMobileMenu();
            initSmoothScrolling();
            initScrollEffects();
            initTypingAnimation();
            initIntersectionObserver();
            initProjectFilters();
            initContactForm();
            initCounterAnimation();
            initBackToTop();
        }

        // Particle Background
        function initParticles() {
            const canvas = document.getElementById('particles-canvas');
            const ctx = canvas.getContext('2d');
            const particles = [];
            const particleCount = 10;

            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;

            class Particle {
                constructor() {
                    this.x = Math.random() * canvas.width;
                    this.y = Math.random() * canvas.height;
                    this.vx = (Math.random() - 0.5) * 0.5;
                    this.vy = (Math.random() - 0.5) * 0.5;
                    this.radius = Math.random() * 2 + 1;
                    this.opacity = Math.random() * 0.5 + 0.2;
                }

                update() {
                    this.x += this.vx;
                    this.y += this.vy;

                    if (this.x < 0 || this.x > canvas.width) this.vx = -this.vx;
                    if (this.y < 0 || this.y > canvas.height) this.vy = -this.vy;
                }

                draw() {
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                    ctx.fillStyle = `rgba(102, 126, 234, ${this.opacity})`;
                    ctx.fill();
                }
            }

            // Create particles
            for (let i = 0; i < particleCount; i++) {
                particles.push(new Particle());
            }

            function animate() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                particles.forEach(particle => {
                    particle.update();
                    particle.draw();
                });

                // Connect nearby particles
                particles.forEach((p1, i) => {
                    particles.slice(i + 1).forEach(p2 => {
                        const dx = p1.x - p2.x;
                        const dy = p1.y - p2.y;
                        const distance = Math.sqrt(dx * dx + dy * dy);

                        if (distance < 100) {
                            ctx.beginPath();
                            ctx.moveTo(p1.x, p1.y);
                            ctx.lineTo(p2.x, p2.y);
                            ctx.strokeStyle = `rgba(102, 126, 234, ${0.1 * (1 - distance / 100)})`;
                            ctx.stroke();
                        }
                    });
                });

                requestAnimationFrame(animate);
            }

            animate();

            // Resize canvas
            window.addEventListener('resize', () => {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            });
        }

        // Custom Cursor
        function initCustomCursor() {
            if (window.innerWidth <= 768) return;

            const cursor = document.querySelector('.cursor');
            const cursorDot = document.querySelector('.cursor-dot');

            document.addEventListener('mousemove', (e) => {
                cursor.style.left = e.clientX + 'px';
                cursor.style.top = e.clientY + 'px';
                cursorDot.style.left = e.clientX + 'px';
                cursorDot.style.top = e.clientY + 'px';
            });

            document.querySelectorAll('a, button, .project-card, .skill-card, .stat-card').forEach(el => {
                el.addEventListener('mouseenter', () => cursor.classList.add('expand'));
                el.addEventListener('mouseleave', () => cursor.classList.remove('expand'));
            });
        }

        // Loading Screen
        function initLoadingScreen() {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    document.getElementById('loadingScreen').classList.add('hidden');
                }, 1500);
            });
        }

        // Theme Toggle
        function initThemeToggle() {
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;
            const themeBall = themeToggle.querySelector('.theme-toggle-ball');

            // Check saved theme
            const savedTheme = localStorage.getItem('theme') || 'light';
            if (savedTheme === 'dark') {
                body.setAttribute('data-theme', 'dark');
                themeBall.textContent = '☀️';
            }

            themeToggle.addEventListener('click', () => {
                const currentTheme = body.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                body.setAttribute('data-theme', newTheme);
                themeBall.textContent = newTheme === 'dark' ? '☀️' : '🌙';
                localStorage.setItem('theme', newTheme);
            });
        }

        // Mobile Menu
        function initMobileMenu() {
            const toggle = document.getElementById('mobileMenuToggle');
            const navLinks = document.getElementById('navLinks');

            toggle.addEventListener('click', () => {
                toggle.classList.toggle('active');
                navLinks.classList.toggle('active');
            });

            // Close on link click
            navLinks.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    toggle.classList.remove('active');
                    navLinks.classList.remove('active');
                });
            });
        }

        // Smooth Scrolling
        function initSmoothScrolling() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        const navHeight = document.getElementById('navbar').offsetHeight;
                        const targetPosition = target.offsetTop - navHeight;
                        
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        }

        // Scroll Effects
        function initScrollEffects() {
            const navbar = document.getElementById('navbar');
            const scrollProgress = document.getElementById('scrollProgress');

            window.addEventListener('scroll', () => {
                // Navbar background
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }

                // Scroll progress
                const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const scrolled = (winScroll / height) * 100;
                scrollProgress.style.width = scrolled + '%';
            });
        }

        // Typing Animation
        function initTypingAnimation() {
            const texts = ['Creative Developer', 'UI/UX Designer', 'Problem Solver', 'Tech Enthusiast'];
            const typingElement = document.querySelector('.typing-text');
            let textIndex = 0;
            let charIndex = 0;
            let isDeleting = false;

            function type() {
                const currentText = texts[textIndex];
                
                if (isDeleting) {
                    typingElement.textContent = currentText.substring(0, charIndex - 1);
                    charIndex--;
                } else {
                    typingElement.textContent = currentText.substring(0, charIndex + 1);
                    charIndex++;
                }

                let typeSpeed = isDeleting ? 50 : 100;

                if (!isDeleting && charIndex === currentText.length) {
                    typeSpeed = 2000;
                    isDeleting = true;
                } else if (isDeleting && charIndex === 0) {
                    isDeleting = false;
                    textIndex = (textIndex + 1) % texts.length;
                    typeSpeed = 500;
                }

                setTimeout(type, typeSpeed);
            }

            type();
        }

        // Intersection Observer for animations
        function initIntersectionObserver() {
            const options = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        
                        // Stagger animations for skill cards
                        if (entry.target.classList.contains('skill-card')) {
                            const cards = document.querySelectorAll('.skill-card');
                            cards.forEach((card, index) => {
                                setTimeout(() => {
                                    card.classList.add('visible');
                                }, index * 100);
                            });
                        }
                    }
                });
            }, options);

            // Observe elements
            document.querySelectorAll('.section-title, .section-subtitle, .skill-card, .project-card, .stat-card').forEach(el => {
                observer.observe(el);
            });
        }

        // Project Filters
        function initProjectFilters() {
            const filterBtns = document.querySelectorAll('.filter-btn');
            const projectCards = document.querySelectorAll('.project-card');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Update active button
                    filterBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');

                    const filter = btn.getAttribute('data-filter');

                    // Filter projects
                    projectCards.forEach((card, index) => {
                        const category = card.getAttribute('data-category');
                        
                        if (filter === 'all' || category === filter) {
                            card.style.display = 'block';
                            setTimeout(() => {
                                card.classList.add('visible');
                            }, index * 100);
                        } else {
                            card.classList.remove('visible');
                            setTimeout(() => {
                                card.style.display = 'none';
                            }, 300);
                        }
                    });
                });
            });
        }

        // Contact Form
        function initContactForm() {
            const form = document.getElementById('contactForm');
            
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                
                // Get form data
                const formData = new FormData(form);
                const name = formData.get('name');
                
                // Show notification
                showNotification(`Thank you ${name}! I'll get back to you soon.`);
                
                // Reset form
                form.reset();
            });
        }

        // Show notification
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // Counter Animation
        function initCounterAnimation() {
            const counters = document.querySelectorAll('.stat-number');
            
            const options = {
                threshold: 0.5
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                        const target = parseInt(entry.target.getAttribute('data-count'));
                        const increment = target / 50;
                        let current = 0;
                        
                        entry.target.classList.add('counted');
                        
                        const updateCounter = () => {
                            current += increment;
                            if (current < target) {
                                entry.target.textContent = Math.floor(current);
                                requestAnimationFrame(updateCounter);
                            } else {
                                entry.target.textContent = target + '+';
                            }
                        };
                        
                        updateCounter();
                    }
                });
            }, options);

            counters.forEach(counter => observer.observe(counter));
        }

        // Back to Top
        function initBackToTop() {
            const backToTop = document.getElementById('backToTop');
            
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    backToTop.classList.add('visible');
                } else {
                    backToTop.classList.remove('visible');
                }
            });

            backToTop.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

        // Add some extra interactive features
        
        // Magnetic hover effect for buttons
        document.querySelectorAll('.cta-button, .filter-btn, .submit-btn').forEach(btn => {
            btn.addEventListener('mousemove', (e) => {
                const rect = btn.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                
                btn.style.transform = `translate(${x * 0.1}px, ${y * 0.1}px)`;
            });

            btn.addEventListener('mouseleave', () => {
                btn.style.transform = '';
            });
        });

        // Parallax effect for hero section
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.hero-content');
            const shapes = document.querySelectorAll('.shape');
            
            if (hero) {
                hero.style.transform = `translateY(${scrolled * 0.5}px)`;
            }
            
            shapes.forEach((shape, index) => {
                const speed = 0.5 + (index * 0.1);
                shape.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });

        // Add ripple effect to buttons
        document.querySelectorAll('button, .cta-button').forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');
                
                // Add ripple styles if not exists
                if (!document.querySelector('#ripple-style')) {
                    const style = document.createElement('style');
                    style.id = 'ripple-style';
                    style.textContent = `
                        .ripple {
                            position: absolute;
                            border-radius: 50%;
                            background: rgba(255, 255, 255, 0.5);
                            transform: scale(0);
                            animation: ripple-animation 0.6s ease-out;
                            pointer-events: none;
                        }
                        @keyframes ripple-animation {
                            to {
                                transform: scale(4);
                                opacity: 0;
                            }
                        }
                        button, .cta-button {
                            position: relative;
                            overflow: hidden;
                        }
                    `;
                    document.head.appendChild(style);
                }
                
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Easter egg: Konami code
        const konamiCode = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];
        let konamiIndex = 0;

        document.addEventListener('keydown', (e) => {
            if (e.key === konamiCode[konamiIndex]) {
                konamiIndex++;
                if (konamiIndex === konamiCode.length) {
                    activateEasterEgg();
                    konamiIndex = 0;
                }
            } else {
                konamiIndex = 0;
            }
        });

        function activateEasterEgg() {
            showNotification('🎉 Konami Code Activated! You found the easter egg!');
            
            // Add rainbow animation to page
            document.body.style.animation = 'rainbow 3s ease-in-out';
            
            const style = document.createElement('style');
            style.textContent = `
                @keyframes rainbow {
                    0% { filter: hue-rotate(0deg); }
                    100% { filter: hue-rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
            
            setTimeout(() => {
                document.body.style.animation = '';
                style.remove();
            }, 3000);
        }

        // Console message
        console.log('%c Welcome to my portfolio! 🚀', 'background: linear-gradient(45deg, #667eea, #764ba2); color: white; font-size: 20px; padding: 10px 20px; border-radius: 10px;');
=======
document.addEventListener('DOMContentLoaded', function() {
            initializePortfolio();
        });

        function initializePortfolio() {
            initParticles();
            initCustomCursor();
            initLoadingScreen();
            initThemeToggle();
            initMobileMenu();
            initSmoothScrolling();
            initScrollEffects();
            initTypingAnimation();
            initIntersectionObserver();
            initProjectFilters();
            initContactForm();
            initCounterAnimation();
            initBackToTop();
        }

        // Particle Background
        function initParticles() {
            const canvas = document.getElementById('particles-canvas');
            const ctx = canvas.getContext('2d');
            const particles = [];
            const particleCount = 10;

            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;

            class Particle {
                constructor() {
                    this.x = Math.random() * canvas.width;
                    this.y = Math.random() * canvas.height;
                    this.vx = (Math.random() - 0.5) * 0.5;
                    this.vy = (Math.random() - 0.5) * 0.5;
                    this.radius = Math.random() * 2 + 1;
                    this.opacity = Math.random() * 0.5 + 0.2;
                }

                update() {
                    this.x += this.vx;
                    this.y += this.vy;

                    if (this.x < 0 || this.x > canvas.width) this.vx = -this.vx;
                    if (this.y < 0 || this.y > canvas.height) this.vy = -this.vy;
                }

                draw() {
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                    ctx.fillStyle = `rgba(102, 126, 234, ${this.opacity})`;
                    ctx.fill();
                }
            }

            // Create particles
            for (let i = 0; i < particleCount; i++) {
                particles.push(new Particle());
            }

            function animate() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                particles.forEach(particle => {
                    particle.update();
                    particle.draw();
                });

                // Connect nearby particles
                particles.forEach((p1, i) => {
                    particles.slice(i + 1).forEach(p2 => {
                        const dx = p1.x - p2.x;
                        const dy = p1.y - p2.y;
                        const distance = Math.sqrt(dx * dx + dy * dy);

                        if (distance < 100) {
                            ctx.beginPath();
                            ctx.moveTo(p1.x, p1.y);
                            ctx.lineTo(p2.x, p2.y);
                            ctx.strokeStyle = `rgba(102, 126, 234, ${0.1 * (1 - distance / 100)})`;
                            ctx.stroke();
                        }
                    });
                });

                requestAnimationFrame(animate);
            }

            animate();

            // Resize canvas
            window.addEventListener('resize', () => {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            });
        }

        // Custom Cursor
        function initCustomCursor() {
            if (window.innerWidth <= 768) return;

            const cursor = document.querySelector('.cursor');
            const cursorDot = document.querySelector('.cursor-dot');

            document.addEventListener('mousemove', (e) => {
                cursor.style.left = e.clientX + 'px';
                cursor.style.top = e.clientY + 'px';
                cursorDot.style.left = e.clientX + 'px';
                cursorDot.style.top = e.clientY + 'px';
            });

            document.querySelectorAll('a, button, .project-card, .skill-card, .stat-card').forEach(el => {
                el.addEventListener('mouseenter', () => cursor.classList.add('expand'));
                el.addEventListener('mouseleave', () => cursor.classList.remove('expand'));
            });
        }

        // Loading Screen
        function initLoadingScreen() {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    document.getElementById('loadingScreen').classList.add('hidden');
                }, 1500);
            });
        }

        // Theme Toggle
        function initThemeToggle() {
            const themeToggle = document.getElementById('themeToggle');
            const body = document.body;
            const themeBall = themeToggle.querySelector('.theme-toggle-ball');

            // Check saved theme
            const savedTheme = localStorage.getItem('theme') || 'light';
            if (savedTheme === 'dark') {
                body.setAttribute('data-theme', 'dark');
                themeBall.textContent = '☀️';
            }

            themeToggle.addEventListener('click', () => {
                const currentTheme = body.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                body.setAttribute('data-theme', newTheme);
                themeBall.textContent = newTheme === 'dark' ? '☀️' : '🌙';
                localStorage.setItem('theme', newTheme);
            });
        }

        // Mobile Menu
        function initMobileMenu() {
            const toggle = document.getElementById('mobileMenuToggle');
            const navLinks = document.getElementById('navLinks');

            toggle.addEventListener('click', () => {
                toggle.classList.toggle('active');
                navLinks.classList.toggle('active');
            });

            // Close on link click
            navLinks.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    toggle.classList.remove('active');
                    navLinks.classList.remove('active');
                });
            });
        }

        // Smooth Scrolling
        function initSmoothScrolling() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        const navHeight = document.getElementById('navbar').offsetHeight;
                        const targetPosition = target.offsetTop - navHeight;
                        
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        }

        // Scroll Effects
        function initScrollEffects() {
            const navbar = document.getElementById('navbar');
            const scrollProgress = document.getElementById('scrollProgress');

            window.addEventListener('scroll', () => {
                // Navbar background
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }

                // Scroll progress
                const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const scrolled = (winScroll / height) * 100;
                scrollProgress.style.width = scrolled + '%';
            });
        }

        // Typing Animation
        function initTypingAnimation() {
            const texts = ['Creative Developer', 'UI/UX Designer', 'Problem Solver', 'Tech Enthusiast'];
            const typingElement = document.querySelector('.typing-text');
            let textIndex = 0;
            let charIndex = 0;
            let isDeleting = false;

            function type() {
                const currentText = texts[textIndex];
                
                if (isDeleting) {
                    typingElement.textContent = currentText.substring(0, charIndex - 1);
                    charIndex--;
                } else {
                    typingElement.textContent = currentText.substring(0, charIndex + 1);
                    charIndex++;
                }

                let typeSpeed = isDeleting ? 50 : 100;

                if (!isDeleting && charIndex === currentText.length) {
                    typeSpeed = 2000;
                    isDeleting = true;
                } else if (isDeleting && charIndex === 0) {
                    isDeleting = false;
                    textIndex = (textIndex + 1) % texts.length;
                    typeSpeed = 500;
                }

                setTimeout(type, typeSpeed);
            }

            type();
        }

        // Intersection Observer for animations
        function initIntersectionObserver() {
            const options = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        
                        // Stagger animations for skill cards
                        if (entry.target.classList.contains('skill-card')) {
                            const cards = document.querySelectorAll('.skill-card');
                            cards.forEach((card, index) => {
                                setTimeout(() => {
                                    card.classList.add('visible');
                                }, index * 100);
                            });
                        }
                    }
                });
            }, options);

            // Observe elements
            document.querySelectorAll('.section-title, .section-subtitle, .skill-card, .project-card, .stat-card').forEach(el => {
                observer.observe(el);
            });
        }

        // Project Filters
        function initProjectFilters() {
            const filterBtns = document.querySelectorAll('.filter-btn');
            const projectCards = document.querySelectorAll('.project-card');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Update active button
                    filterBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');

                    const filter = btn.getAttribute('data-filter');

                    // Filter projects
                    projectCards.forEach((card, index) => {
                        const category = card.getAttribute('data-category');
                        
                        if (filter === 'all' || category === filter) {
                            card.style.display = 'block';
                            setTimeout(() => {
                                card.classList.add('visible');
                            }, index * 100);
                        } else {
                            card.classList.remove('visible');
                            setTimeout(() => {
                                card.style.display = 'none';
                            }, 300);
                        }
                    });
                });
            });
        }

        // Contact Form
        function initContactForm() {
            const form = document.getElementById('contactForm');
            
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                
                // Get form data
                const formData = new FormData(form);
                const name = formData.get('name');
                
                // Show notification
                showNotification(`Thank you ${name}! I'll get back to you soon.`);
                
                // Reset form
                form.reset();
            });
        }

        // Show notification
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // Counter Animation
        function initCounterAnimation() {
            const counters = document.querySelectorAll('.stat-number');
            
            const options = {
                threshold: 0.5
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                        const target = parseInt(entry.target.getAttribute('data-count'));
                        const increment = target / 50;
                        let current = 0;
                        
                        entry.target.classList.add('counted');
                        
                        const updateCounter = () => {
                            current += increment;
                            if (current < target) {
                                entry.target.textContent = Math.floor(current);
                                requestAnimationFrame(updateCounter);
                            } else {
                                entry.target.textContent = target + '+';
                            }
                        };
                        
                        updateCounter();
                    }
                });
            }, options);

            counters.forEach(counter => observer.observe(counter));
        }

        // Back to Top
        function initBackToTop() {
            const backToTop = document.getElementById('backToTop');
            
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    backToTop.classList.add('visible');
                } else {
                    backToTop.classList.remove('visible');
                }
            });

            backToTop.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

        // Add some extra interactive features
        
        // Magnetic hover effect for buttons
        document.querySelectorAll('.cta-button, .filter-btn, .submit-btn').forEach(btn => {
            btn.addEventListener('mousemove', (e) => {
                const rect = btn.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                
                btn.style.transform = `translate(${x * 0.1}px, ${y * 0.1}px)`;
            });

            btn.addEventListener('mouseleave', () => {
                btn.style.transform = '';
            });
        });

        // Parallax effect for hero section
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.hero-content');
            const shapes = document.querySelectorAll('.shape');
            
            if (hero) {
                hero.style.transform = `translateY(${scrolled * 0.5}px)`;
            }
            
            shapes.forEach((shape, index) => {
                const speed = 0.5 + (index * 0.1);
                shape.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });

        // Add ripple effect to buttons
        document.querySelectorAll('button, .cta-button').forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');
                
                // Add ripple styles if not exists
                if (!document.querySelector('#ripple-style')) {
                    const style = document.createElement('style');
                    style.id = 'ripple-style';
                    style.textContent = `
                        .ripple {
                            position: absolute;
                            border-radius: 50%;
                            background: rgba(255, 255, 255, 0.5);
                            transform: scale(0);
                            animation: ripple-animation 0.6s ease-out;
                            pointer-events: none;
                        }
                        @keyframes ripple-animation {
                            to {
                                transform: scale(4);
                                opacity: 0;
                            }
                        }
                        button, .cta-button {
                            position: relative;
                            overflow: hidden;
                        }
                    `;
                    document.head.appendChild(style);
                }
                
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Easter egg: Konami code
        const konamiCode = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];
        let konamiIndex = 0;

        document.addEventListener('keydown', (e) => {
            if (e.key === konamiCode[konamiIndex]) {
                konamiIndex++;
                if (konamiIndex === konamiCode.length) {
                    activateEasterEgg();
                    konamiIndex = 0;
                }
            } else {
                konamiIndex = 0;
            }
        });

        function activateEasterEgg() {
            showNotification('🎉 Konami Code Activated! You found the easter egg!');
            
            // Add rainbow animation to page
            document.body.style.animation = 'rainbow 3s ease-in-out';
            
            const style = document.createElement('style');
            style.textContent = `
                @keyframes rainbow {
                    0% { filter: hue-rotate(0deg); }
                    100% { filter: hue-rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
            
            setTimeout(() => {
                document.body.style.animation = '';
                style.remove();
            }, 3000);
        }

        // Console message
        console.log('%c Welcome to my portfolio! 🚀', 'background: linear-gradient(45deg, #667eea, #764ba2); color: white; font-size: 20px; padding: 10px 20px; border-radius: 10px;');
>>>>>>> 344cc73 (Initial Commit)
        console.log('%c Feel free to explore the code! If you have any questions, reach out through the contact form.', 'color: #667eea; font-size: 14px;');