// Main JavaScript for E-Shop
$(document).ready(function() {
    
    // Initialize MDBootstrap components
    // Already initialized via MDB JS
    
    // Add to Cart functionality - with event delegation for dynamic content
    $(document).on('click', '.add-to-cart', function(e) {
        e.preventDefault();
        const productId = $(this).data('product-id');
        const quantity = $(this).data('quantity') || 1;
        
        addToCart(productId, quantity);
    });
    
    // Quick view functionality
    $('.quick-view').on('click', function(e) {
        e.preventDefault();
        const productId = $(this).data('product-id');
        loadQuickView(productId);
    });
    
    // Quantity buttons
    $(document).on('click', '.qty-minus', function() {
        const input = $(this).siblings('.quantity-input');
        let val = parseInt(input.val()) || 1;
        if (val > 1) {
            input.val(val - 1).trigger('change');
        }
    });
    
    $(document).on('click', '.qty-plus', function() {
        const input = $(this).siblings('.quantity-input');
        let val = parseInt(input.val()) || 1;
        const max = parseInt(input.attr('max')) || 99;
        if (val < max) {
            input.val(val + 1).trigger('change');
        }
    });
    
    // Update cart quantity
    $(document).on('change', '.cart-qty-input', function() {
        const cartId = $(this).data('cart-id');
        const quantity = $(this).val();
        updateCartQuantity(cartId, quantity);
    });
    
    // Remove from cart
    $(document).on('click', '.remove-from-cart', function(e) {
        e.preventDefault();
        const cartId = $(this).data('cart-id');
        removeFromCart(cartId);
    });
    
    // Product thumbnail gallery
    $('.thumbnail').on('click', function() {
        const src = $(this).attr('src');
        $('.main-image').attr('src', src);
        $('.thumbnail').removeClass('active');
        $(this).addClass('active');
    });
    
    // Form validation
    $('form[data-validate]').on('submit', function(e) {
        let isValid = true;
        const requiredFields = $(this).find('[required]');
        
        requiredFields.each(function() {
            if (!$(this).val().trim()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showToast('Please fill in all required fields', 'error');
        }
    });
    
    // Password strength indicator
    $('#password').on('input', function() {
        const password = $(this).val();
        const strength = checkPasswordStrength(password);
        updatePasswordStrength(strength);
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow', function() {
            $(this).alert('close');
        });
    }, 5000);
    
    // Lazy loading images
    const lazyImages = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    lazyImages.forEach(img => imageObserver.observe(img));
    
    // Smooth scroll for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 800);
        }
    });
});

// Add to Cart function
function addToCart(productId, quantity = 1) {
    $.ajax({
        url: 'cart_actions.php',
        type: 'POST',
        data: {
            action: 'add',
            product_id: productId,
            quantity: quantity
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast('Product added to cart!', 'success');
                updateCartBadge(response.cart_count);
            } else {
                showToast(response.message || 'Failed to add product', 'error');
            }
        },
        error: function() {
            showToast('An error occurred. Please try again.', 'error');
        }
    });
}

// Update cart quantity
function updateCartQuantity(cartId, quantity) {
    $.ajax({
        url: 'cart_actions.php',
        type: 'POST',
        data: {
            action: 'update',
            cart_id: cartId,
            quantity: quantity
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateCartBadge(response.cart_count);
                updateCartTotals(response.subtotal, response.total);
            } else {
                showToast(response.message || 'Failed to update cart', 'error');
            }
        }
    });
}

// Remove from cart
function removeFromCart(cartId) {
    if (confirm('Are you sure you want to remove this item?')) {
        $.ajax({
            url: 'cart_actions.php',
            type: 'POST',
            data: {
                action: 'remove',
                cart_id: cartId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $(`[data-cart-item="${cartId}"]`).fadeOut(300, function() {
                        $(this).remove();
                    });
                    updateCartBadge(response.cart_count);
                    updateCartTotals(response.subtotal, response.total);
                    showToast('Item removed from cart', 'success');
                }
            }
        });
    }
}

// Update cart badge
function updateCartBadge(count) {
    const badge = $('.navbar .badge');
    if (count > 0) {
        if (badge.length) {
            badge.text(count);
        } else {
            $('.fa-shopping-cart').after(`<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">${count}</span>`);
        }
    } else {
        badge.remove();
    }
}

// Update cart totals
function updateCartTotals(subtotal, total) {
    $('#cart-subtotal').text('$' + subtotal.toFixed(2));
    $('#cart-total').text('$' + total.toFixed(2));
}

// Show toast notification
function showToast(message, type = 'info') {
    const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-primary';
    const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
    
    const toast = `
        <div class="toast align-items-center ${bgClass} text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${icon} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-mdb-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    if (!$('.toast-container').length) {
        $('body').append('<div class="toast-container"></div>');
    }
    
    const $toast = $(toast);
    $('.toast-container').append($toast);
    
    const toastEl = new mdb.Toast($toast[0]);
    toastEl.show();
    
    setTimeout(() => {
        $toast.fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}

// Check password strength
function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&!]+/)) strength++;
    return strength;
}

// Update password strength indicator
function updatePasswordStrength(strength) {
    const indicator = $('#password-strength');
    const texts = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
    const colors = ['#dc4c64', '#e4a11b', '#54b4d3', '#14a44d', '#14a44d', '#14a44d'];
    
    indicator.text(texts[strength]).css('color', colors[strength]);
}

// Load quick view modal content
function loadQuickView(productId) {
    $.ajax({
        url: 'quick_view.php',
        type: 'GET',
        data: { id: productId },
        success: function(response) {
            $('#quickViewModal .modal-body').html(response);
            const modal = new mdb.Modal(document.getElementById('quickViewModal'));
            modal.show();
        }
    });
}

// Confirm delete
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// Format price
function formatPrice(price) {
    return '$' + parseFloat(price).toFixed(2);
}

// Debounce function for search
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
