document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.querySelector('.nav-toggle');
    const nav = document.querySelector('.main-nav');

    if (toggleButton && nav) {
        toggleButton.addEventListener('click', function () {
            const isOpen = nav.classList.toggle('is-open');
            toggleButton.setAttribute('aria-expanded', String(isOpen));
        });
    }

    const adminToggle = document.querySelector('[data-admin-menu-toggle]');
    const adminSidebar = document.querySelector('[data-admin-sidebar]');
    if (adminToggle && adminSidebar) {
        adminToggle.addEventListener('click', function () {
            const isOpen = adminSidebar.classList.toggle('is-open');
            adminToggle.setAttribute('aria-expanded', String(isOpen));
        });
    }
});
