(() => {
    const body = document.body;
    document.querySelectorAll('form').forEach((form) => {
        form.noValidate = true;
    });
    const drawerToggle = document.querySelector('[data-drawer]');
    const adminToggle = document.querySelector('[data-admin-toggle]');
    const filterToggle = document.querySelector('[data-filter-open]');
    const filters = document.getElementById('shop-filters');
    const accountNavigation = document.querySelector('.acc-nav');
    const accountLinks = accountNavigation
        ? [...accountNavigation.querySelectorAll('a[href^="#"]')]
        : [];

    const activateAccountSection = (hash) => {
        const requestedSection = hash ? document.getElementById(hash.slice(1)) : null;
        const section = requestedSection?.parentElement?.classList.contains('acc-main')
            ? requestedSection
            : null;
        const activeSection = section || document.querySelector('.acc-main > section#overview');
        if (!activeSection) return;

        accountLinks.forEach((link) => {
            const isActive = link.hash === `#${activeSection.id}`;
            link.classList.toggle('on', isActive);
            if (isActive) link.setAttribute('aria-current', 'location');
            else link.removeAttribute('aria-current');
        });
        document.querySelectorAll('.acc-main > section').forEach((item) => {
            item.classList.toggle('on', item === activeSection);
        });
    };

    const setOpen = (className, isOpen, toggle, expanded = isOpen) => {
        body.classList.toggle(className, isOpen);
        if (toggle) toggle.setAttribute('aria-expanded', String(expanded));
    };

    const testimonialCarousel = document.querySelector('[data-testimonial-carousel]');
    const setupTestimonialCarousel = () => {
        if (!testimonialCarousel) return;

        testimonialCarousel.querySelectorAll('[data-carousel-clone]').forEach((clone) => clone.remove());
        testimonialCarousel.classList.remove('is-auto-carousel');
        testimonialCarousel.style.removeProperty('max-width');
        testimonialCarousel.style.removeProperty('--testimonial-card-width');

        const cards = [...testimonialCarousel.querySelectorAll('.tcard')];
        cards.forEach((card) => card.style.removeProperty('flex'));
        if (cards.length < 2) return;

        const styles = getComputedStyle(testimonialCarousel);
        const columns = Math.max(1, styles.gridTemplateColumns.split(' ').length);
        const gap = parseFloat(styles.columnGap) || 0;
        const cardWidth = cards[0].getBoundingClientRect().width;
        const visibleCards = Math.min(columns, cards.length);
        const viewportWidth = visibleCards * cardWidth + (visibleCards - 1) * gap;

        testimonialCarousel.classList.add('is-auto-carousel');
        testimonialCarousel.style.setProperty('--testimonial-card-width', `${cardWidth}px`);
        if (cards.length < columns) {
            testimonialCarousel.style.maxWidth = `${viewportWidth}px`;
        }

        let cloneIndex = 0;
        while (
            testimonialCarousel.scrollWidth <= testimonialCarousel.clientWidth + 1
            && cloneIndex < cards.length * 2
        ) {
            const clone = cards[cloneIndex % cards.length].cloneNode(true);
            clone.dataset.carouselClone = 'true';
            clone.setAttribute('aria-hidden', 'true');
            testimonialCarousel.append(clone);
            cloneIndex++;
        }
    };

    setupTestimonialCarousel();
    let testimonialResizeTimer;
    window.addEventListener('resize', () => {
        window.clearTimeout(testimonialResizeTimer);
        testimonialResizeTimer = window.setTimeout(setupTestimonialCarousel, 150);
    });

    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.querySelectorAll('[data-category-rail], [data-testimonial-carousel]').forEach((carousel) => {
            const firstCard = carousel.firstElementChild;
            if (!firstCard) return;
            const interactionArea = carousel.closest('.rail-wrap') || carousel;

            let interactionUntil = 0;
            const pauseAfterInteraction = () => {
                interactionUntil = Date.now() + 10000;
            };
            interactionArea.addEventListener('pointerdown', pauseAfterInteraction);
            interactionArea.addEventListener('wheel', pauseAfterInteraction, { passive: true });
            interactionArea.addEventListener('keydown', pauseAfterInteraction);

            window.setInterval(() => {
                if (
                    document.hidden
                    || interactionArea.matches(':hover')
                    || interactionArea.contains(document.activeElement)
                    || Date.now() < interactionUntil
                ) {
                    return;
                }

                const gap = parseFloat(getComputedStyle(carousel).columnGap) || 0;
                const distance = firstCard.getBoundingClientRect().width + gap;
                const end = carousel.scrollWidth - carousel.clientWidth;
                if (end <= 1) return;

                if (carousel.scrollLeft >= end - 2) {
                    carousel.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    carousel.scrollBy({ left: distance, behavior: 'smooth' });
                }
            }, 4000);
        });
    }

    const closeDrawer = () => setOpen('nav-open', false, drawerToggle);
    const closeAdminMenu = () => setOpen('side-open', false, adminToggle);
    const closeFilters = () => {
        filters?.classList.remove('open');
        setOpen('shop-filters-open', false, filterToggle);
    };

    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        if (!target) return;

        const railButton = target.closest('[data-rail-scroll]');
        if (railButton) {
            const rail = railButton.closest('.rail-wrap')?.querySelector('[data-category-rail]');
            const direction = railButton.dataset.railScroll === 'previous' ? -1 : 1;
            if (rail) rail.scrollBy({ left: direction * rail.clientWidth * 0.8, behavior: 'smooth' });
        }

        if (target.closest('[data-drawer]')) {
            setOpen('nav-open', true, drawerToggle);
        } else if (target.closest('[data-drawer-close]')) {
            closeDrawer();
        }

        if (target.closest('[data-admin-toggle]')) {
            setOpen('side-open', !body.classList.contains('side-open'), adminToggle);
        } else if (
            target.closest('[data-admin-close]') ||
            target.closest('.side nav a')
        ) {
            closeAdminMenu();
        }

        if (target.closest('[data-filter-open]')) {
            filters?.classList.add('open');
            setOpen('shop-filters-open', true, filterToggle);
        } else if (target.closest('[data-filter-close]')) {
            closeFilters();
        }

        const accordionButton = target.closest('.drawer-acc-btn');
        if (accordionButton) {
            const accordion = accordionButton.closest('.drawer-acc');
            const panelId = accordionButton.getAttribute('aria-controls');
            const panel = panelId ? document.getElementById(panelId) : null;
            const isOpen = accordion?.classList.toggle('open') ?? false;
            accordionButton.setAttribute('aria-expanded', String(isOpen));
            if (panel) {
                if (isOpen) panel.removeAttribute('inert');
                else panel.setAttribute('inert', '');
            }
        }

        const accountLink = target.closest('.acc-nav a[href^="#"]');
        if (accountLink && accountLinks.includes(accountLink)) {
            activateAccountSection(accountLink.hash);
        }
    });

    if (accountLinks.length) {
        activateAccountSection(window.location.hash);
        window.addEventListener('hashchange', () => activateAccountSection(window.location.hash));
    }

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        closeDrawer();
        closeAdminMenu();
        closeFilters();
    });

    document.getElementById('shop-sort')?.addEventListener('change', (event) => {
        event.currentTarget.form?.requestSubmit();
    });

    document.addEventListener('click', (event) => {
        const tab = event.target instanceof Element
            ? event.target.closest('[data-feature-filter]')
            : null;
        if (!tab) return;

        const tabList = tab.closest('[role="tablist"]');
        const panelId = tab.getAttribute('aria-controls');
        const activePanel = panelId ? document.getElementById(panelId) : null;
        if (!tabList || !activePanel) return;

        tabList.querySelectorAll('[data-feature-filter]').forEach((item) => {
            const isActive = item === tab;
            item.classList.toggle('on', isActive);
            item.setAttribute('aria-selected', String(isActive));
        });

        document.querySelectorAll('[data-feature-panel]').forEach((panel) => {
            panel.hidden = panel !== activePanel;
        });

        const viewAll = tab.closest('.wrap')?.querySelector('[data-featured-view-all]');
        const filterHref = tab.getAttribute('data-filter-href');
        if (viewAll && filterHref) viewAll.setAttribute('href', filterHref);
    });

    const validationErrors = window.laravelValidationErrors;
    if (validationErrors && typeof validationErrors === 'object') {
        const entries = Object.entries(validationErrors)
            .map(([name, messages]) => [name, Array.isArray(messages) ? messages : [messages]])
            .filter(([, messages]) => messages.length > 0);

        if (entries.length) {
            let errorIndex = 0;
            entries.forEach(([name, messages]) => {
                const fieldName = name.replace(/\.\d+(?=\.|$)/g, '').replace(/\[\]$/, '');
                const controls = [...document.querySelectorAll('input[name], select[name], textarea[name]')]
                    .filter((control) => {
                        const controlName = control.name.replace(/\[\]$/, '');
                        return controlName === fieldName || fieldName.startsWith(`${controlName}.`);
                    });

                // const control = controls[0];
                // if (!control) return;
                // control.classList.add('field-invalid');
                // control.setAttribute('aria-invalid', 'true');
                // const field = control.closest('.field') || control.parentElement;
                
                const control = controls[0];
                if (!control) return;

                control.classList.add('field-invalid');
                control.setAttribute('aria-invalid', 'true');

                // Coupon errors are displayed by Laravel Blade.
                // Prevent JavaScript from displaying the same error again.
                if (control.name === 'code' && control.closest('form.coupon')) {
                    return;
                }

                const field = control.closest('.field') || control.parentElement;

                if (field?.querySelector('[data-validation-error]')) return;

                const describedBy = new Set((control.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
                messages.forEach((message) => {
                    const error = document.createElement('small');
                    error.className = 'field-error-message';
                    error.id = `laravel-error-${errorIndex++}`;
                    error.setAttribute('role', 'alert');
                    error.textContent = String(message);
                    field?.append(error);
                    describedBy.add(error.id);
                });
                control.setAttribute('aria-describedby', [...describedBy].join(' '));
            });
        }
    }

    // Password toggle functionality for login and account forms and change password forms.

    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-password-toggle], #loginPasswordToggle');

        if (!button) return;

        const wrapper = button.closest('.pw');
        const input = wrapper?.querySelector('input');

        if (!input) return;

        const showPassword = input.type === 'password';

        // Toggle password visibility
        input.type = showPassword ? 'text' : 'password';

        // Support both login and account password icons
        const eye = button.querySelector('.icon-eye, #eyeOpen');
        const eyeOff = button.querySelector('.icon-eye-off, #eyeClosed');

        if (eye) {
            eye.style.display = showPassword ? 'none' : 'block';
        }

        if (eyeOff) {
            eyeOff.style.display = showPassword ? 'block' : 'none';
        }

        button.setAttribute(
            'aria-label',
            showPassword ? 'Hide password' : 'Show password'
        );

        button.setAttribute('aria-pressed', String(showPassword));
    });


    document.addEventListener('click', function (event) {
        const button = event.target.closest(
            '#accountChangePasswordForm .account-password-eye'
        );

        if (!button) return;

        const wrapper = button.closest('.account-password-wrapper');
        const input = wrapper?.querySelector('input');
        const eyeVisible = button.querySelector('.account-eye-visible');
        const eyeHidden = button.querySelector('.account-eye-hidden');

        if (!input || !eyeVisible || !eyeHidden) return;

        const showPassword = input.type === 'password';

        input.type = showPassword ? 'text' : 'password';

        // Force exactly one icon to be visible.
        eyeVisible.style.display = showPassword ? 'none' : 'block';
        eyeHidden.style.display = showPassword ? 'block' : 'none';

        button.setAttribute(
            'aria-label',
            showPassword ? 'Hide password' : 'Show password'
        );

        button.setAttribute('aria-pressed', String(showPassword));
    });

    // set admin password toggle functionality for admin password settings form

    document.addEventListener('click', function (event) {
        const button = event.target.closest(
            '#adminPasswordSettings .admin-pw-toggle'
        );

        if (!button) return;

        const wrapper = button.closest('.admin-pw-wrapper');
        const input = wrapper?.querySelector('input');

        const eyeOpen = button.querySelector('.admin-eye-open');
        const eyeClosed = button.querySelector('.admin-eye-closed');

        if (!input || !eyeOpen || !eyeClosed) return;

        const shouldShow = input.type === 'password';

        input.type = shouldShow ? 'text' : 'password';

        eyeOpen.style.display = shouldShow ? 'none' : 'block';
        eyeClosed.style.display = shouldShow ? 'block' : 'none';

        button.setAttribute(
            'aria-label',
            shouldShow ? 'Hide password' : 'Show password'
        );

        button.setAttribute('aria-pressed', String(shouldShow));
    });


})();
