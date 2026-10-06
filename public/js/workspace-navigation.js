(() => {
    const sidebar = document.getElementById('workspace-sidebar');
    const toggle = document.getElementById('sidebar-toggle');
    const mobileToggle = document.getElementById('mobile-sidebar-toggle');
    const backdrop = document.getElementById('sidebar-backdrop');
    if (!sidebar || !toggle || !mobileToggle || !backdrop) return;

    const desktop = window.matchMedia('(min-width: 992px)');
    const storageKey = 'laundry-wash-sidebar-collapsed';
    let collapsed = false;
    let mobileOpen = false;
    try { collapsed = window.localStorage.getItem(storageKey) === 'true'; } catch { /* Storage may be disabled. */ }
    const background = [...document.querySelectorAll('.workspace-navbar, .app-content, body > footer')];

    function render() {
        document.body.classList.toggle('sidebar-collapsed', desktop.matches && collapsed);
        document.body.classList.toggle('mobile-sidebar-open', !desktop.matches && mobileOpen);
        sidebar.inert = !desktop.matches && !mobileOpen;
        sidebar.toggleAttribute('aria-modal', !desktop.matches && mobileOpen);
        if (!desktop.matches && mobileOpen) {
            sidebar.setAttribute('role', 'dialog');
            sidebar.setAttribute('aria-modal', 'true');
        } else {
            sidebar.removeAttribute('role');
        }
        background.forEach(element => { element.inert = !desktop.matches && mobileOpen; });
        mobileToggle.setAttribute('aria-expanded', String(mobileOpen));
        toggle.setAttribute('aria-expanded', String(desktop.matches ? !collapsed : mobileOpen));
        const label = desktop.matches ? (collapsed ? 'Lebarkan sidebar' : 'Ciutkan sidebar') : 'Tutup navigasi';
        toggle.setAttribute('aria-label', label);
        toggle.title = label;
    }

    function closeMobile(restoreFocus = true) {
        mobileOpen = false;
        render();
        if (restoreFocus) mobileToggle.focus();
    }

    toggle.addEventListener('click', () => {
        if (!desktop.matches) return closeMobile();
        collapsed = !collapsed;
        try { window.localStorage.setItem(storageKey, String(collapsed)); } catch { /* Keep the current session usable. */ }
        render();
    });
    mobileToggle.addEventListener('click', () => {
        if (mobileOpen) {
            closeMobile(false);
            return;
        }

        mobileOpen = true;
        render();
        toggle.focus();
    });
    backdrop.addEventListener('click', () => closeMobile());
    desktop.addEventListener('change', () => {
        mobileOpen = false;
        render();
        if (!desktop.matches && sidebar.contains(document.activeElement)) mobileToggle.focus();
    });
    document.addEventListener('keydown', event => {
        if (!mobileOpen || desktop.matches) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            closeMobile();
        }
        if (event.key === 'Tab') {
            const items = [...sidebar.querySelectorAll('a[href], button:not([disabled]), input')].filter(element => element.getClientRects().length);
            const first = items[0];
            const last = items[items.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
    sidebar.querySelectorAll('.nav-link').forEach(link => {
        if (link.classList.contains('active')) link.setAttribute('aria-current', 'page');
    });
    render();

    const search = document.getElementById('workspace-page-search');
    if (search) {
        const input = search.querySelector('input');
        const choices = document.getElementById('workspace-page-options');
        const links = [...sidebar.querySelectorAll('.nav-link')];
        links.forEach(link => {
            const option = document.createElement('option');
            option.value = link.textContent.trim();
            choices.append(option);
        });
        input.addEventListener('input', () => input.setCustomValidity(''));
        search.addEventListener('submit', event => {
            event.preventDefault();
            const query = input.value.trim().toLocaleLowerCase('id');
            const link = links.find(item => item.textContent.trim().toLocaleLowerCase('id') === query);
            if (link) window.location.assign(link.href);
            else { input.setCustomValidity('Pilih halaman yang tersedia dari daftar.'); input.reportValidity(); }
        });
    }
})();
