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

    const closeDrawer = () => setOpen('nav-open', false, drawerToggle);
    const closeAdminMenu = () => setOpen('side-open', false, adminToggle);
    const closeFilters = () => {
        filters?.classList.remove('open');
        setOpen('shop-filters-open', false, filterToggle);
    };

    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target : null;
        if (!target) return;

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
                const control = controls[0];
                if (!control) return;
                control.classList.add('field-invalid');
                control.setAttribute('aria-invalid', 'true');

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
})();
