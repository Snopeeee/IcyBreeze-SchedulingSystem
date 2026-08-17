(function () {
    const toggle = document.querySelector('[data-menu-toggle]');
    const menu = document.querySelector('[data-site-menu]');

    toggle?.addEventListener('click', function () {
        const open = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', String(!open));
        menu?.classList.toggle('is-open', !open);
        toggle.querySelector('i')?.classList.toggle('ph-list', open);
        toggle.querySelector('i')?.classList.toggle('ph-x', !open);
    });

    menu?.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            toggle?.setAttribute('aria-expanded', 'false');
            menu.classList.remove('is-open');
            toggle?.querySelector('i')?.classList.add('ph-list');
            toggle?.querySelector('i')?.classList.remove('ph-x');
        });
    });

    document.querySelectorAll('[data-faq-button]').forEach(function (button) {
        button.addEventListener('click', function () {
            const item = button.closest('[data-faq-item]');
            const expanded = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', String(!expanded));
            item?.classList.toggle('is-open', !expanded);
        });
    });
}());

