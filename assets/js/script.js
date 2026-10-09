document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.querySelector('.nav-toggle');
    const nav = document.querySelector('.main-nav');

    if (toggleButton && nav) {
        toggleButton.addEventListener('click', function () {
            const isOpen = nav.classList.toggle('is-open');
            toggleButton.setAttribute('aria-expanded', String(isOpen));
        });
    }

    const whatsappProductLink = document.querySelector('[data-whatsapp-product]');
    const quantityInput = document.querySelector('#quantity');
    const cartQuantityInput = document.querySelector('[data-cart-quantity]');
    if (whatsappProductLink && quantityInput) {
        const updateWhatsAppLink = function () {
            const quantity = Math.max(1, Math.min(Number(quantityInput.value) || 1, Number(quantityInput.max) || 1));
            quantityInput.value = String(quantity);
            if (cartQuantityInput) {
                cartQuantityInput.value = String(quantity);
            }
            const message = 'Hello ' + (whatsappProductLink.dataset.siteName || 'Maison Gift Co.') + ', I would like to order:\n'
                + 'Product: ' + whatsappProductLink.dataset.productName + '\n'
                + 'Product ID: #' + whatsappProductLink.dataset.productId + '\n'
                + 'Quantity: ' + quantity + '\n'
                + 'Price: ' + whatsappProductLink.dataset.productPrice + '\n'
                + 'Subtotal: ₹' + (Number(whatsappProductLink.dataset.productUnitPrice) * quantity).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '\n'
                + 'Product page: ' + whatsappProductLink.dataset.productUrl;
            const whatsappBaseUrl = whatsappProductLink.href.split('?')[0];
            whatsappProductLink.href = whatsappBaseUrl + '?text=' + encodeURIComponent(message);
        };
        quantityInput.addEventListener('input', updateWhatsAppLink);
        updateWhatsAppLink();
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
