// Main JavaScript file for TechPioneer

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all functionality
    initAddToCartButtons();
    initCartQuantityButtons();
    initRemoveFromCartButtons();
    initDashboardNavigation();
    initProductSpecifications();
    initFilters();
});

// Initialize Add to Cart buttons
function initAddToCartButtons() {
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            addToCart(productId, 1);
        });
    });
}

// Add product to cart
function addToCart(productId, quantity) {
    fetch('../api/add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product added to cart successfully!', 'success');
            updateCartCount();
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while adding the product to cart.', 'error');
    });
}

// Initialize cart quantity buttons
function initCartQuantityButtons() {
    const quantityButtons = document.querySelectorAll('.quantity-btn');
    
    quantityButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            const productId = this.closest('.cart-item').getAttribute('data-product-id');
            const quantityElement = this.parentElement.querySelector('.quantity');
            let quantity = parseInt(quantityElement.textContent);
            
            if (action === 'increase') {
                quantity++;
            } else if (action === 'decrease' && quantity > 1) {
                quantity--;
            }
            
            if (action === 'increase' || (action === 'decrease' && quantity >= 1)) {
                updateCartQuantity(productId, quantity);
            }
        });
    });
}

// Update cart quantity
function updateCartQuantity(productId, quantity) {
    fetch('../api/update_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to update totals
            location.reload();
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while updating the cart.', 'error');
    });
}

// Initialize remove from cart buttons
function initRemoveFromCartButtons() {
    const removeButtons = document.querySelectorAll('.remove-btn');
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                removeFromCart(productId);
            }
        });
    });
}

// Remove product from cart
function removeFromCart(productId) {
    fetch('../api/remove_from_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove item from DOM
            const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
            if (cartItem) {
                cartItem.remove();
            }
            
            // Check if cart is empty
            const cartItems = document.querySelectorAll('.cart-item');
            if (cartItems.length === 0) {
                location.reload();
            } else {
                // Reload page to update totals
                location.reload();
            }
            
            updateCartCount();
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while removing the item.', 'error');
    });
}

// Update cart count in navigation
function updateCartCount() {
    // This would typically update a cart counter in the navigation
    // For now, we'll just show a notification
    console.log('Cart count updated');
}

// Initialize dashboard navigation
function initDashboardNavigation() {
    const navLinks = document.querySelectorAll('.dashboard-nav .nav-link');
    const sections = document.querySelectorAll('.dashboard-section');
    
    if (navLinks.length > 0) {
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all links and sections
                navLinks.forEach(l => l.classList.remove('active'));
                sections.forEach(s => s.classList.remove('active'));
                
                // Add active class to clicked link
                this.classList.add('active');
                
                // Show corresponding section
                const targetId = this.getAttribute('href').substring(1);
                document.getElementById(targetId).classList.add('active');
            });
        });
    }
}

// Initialize product specifications form
function initProductSpecifications() {
    const addSpecBtn = document.getElementById('add-specification');
    const specsContainer = document.getElementById('specifications-container');
    
    if (addSpecBtn && specsContainer) {
        addSpecBtn.addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.className = 'specification-row';
            newRow.innerHTML = `
                <input type="text" name="spec_key[]" placeholder="Key (e.g., Storage)" required>
                <input type="text" name="spec_value[]" placeholder="Value (e.g., 128GB)" required>
                <button type="button" class="remove-spec">Remove</button>
            `;
            specsContainer.appendChild(newRow);
        });
        
        // Remove specification fields
        specsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-spec')) {
                if (specsContainer.children.length > 1) {
                    e.target.parentElement.remove();
                }
            }
        });
    }
}

// Initialize filters
function initFilters() {
    const filterForm = document.querySelector('.filter-form');
    
    if (filterForm) {
        const searchInput = filterForm.querySelector('input[name="search"]');
        const categorySelect = filterForm.querySelector('select[name="category"]');
        
        // Auto-submit form when category changes
        if (categorySelect) {
            categorySelect.addEventListener('change', function() {
                filterForm.submit();
            });
        }
        
        // Debounce search input
        if (searchInput) {
            let timeout = null;
            searchInput.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    filterForm.submit();
                }, 500);
            });
        }
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Add styles
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.padding = '1rem 1.5rem';
    notification.style.borderRadius = '4px';
    notification.style.color = 'white';
    notification.style.zIndex = '1000';
    notification.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
    notification.style.transition = 'all 0.3s ease';
    
    // Set background color based on type
    switch(type) {
        case 'success':
            notification.style.backgroundColor = '#2ecc71';
            break;
        case 'error':
            notification.style.backgroundColor = '#e74c3c';
            break;
        case 'warning':
            notification.style.backgroundColor = '#f39c12';
            break;
        default:
            notification.style.backgroundColor = '#3498db';
    }
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Form validation
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = '#e74c3c';
        } else {
            input.style.borderColor = '';
        }
    });
    
    return isValid;
}

// Price formatting
function formatPrice(price) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price);
}

// Debounce function for performance
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func(...args);
    };
}

// Export functions for global use (if needed)
window.TechPioneer = {
    addToCart,
    updateCartQuantity,
    removeFromCart,
    showNotification,
    validateForm,
    formatPrice
};


// Additional JavaScript functions for TechPioneer

// Form validation enhancement
function enhanceFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    showFieldError(field, 'This field is required.');
                } else {
                    clearFieldError(field);
                }
                
                // Email validation
                if (field.type === 'email' && field.value.trim()) {
                    if (!isValidEmail(field.value)) {
                        isValid = false;
                        showFieldError(field, 'Please enter a valid email address.');
                    }
                }
                
                // Password validation
                if (field.type === 'password' && field.value.trim()) {
                    if (!isValidPassword(field.value)) {
                        isValid = false;
                        showFieldError(field, 'Password must be at least 8 characters with uppercase, lowercase, number, and special character.');
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fix the errors in the form.', 'error');
            }
        });
    });
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Password validation
function isValidPassword(password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    return passwordRegex.test(password);
}

// Show field error
function showFieldError(field, message) {
    clearFieldError(field);
    field.style.borderColor = '#e74c3c';
    
    const errorElement = document.createElement('div');
    errorElement.className = 'form-error';
    errorElement.textContent = message;
    field.parentNode.appendChild(errorElement);
}

// Clear field error
function clearFieldError(field) {
    field.style.borderColor = '';
    const existingError = field.parentNode.querySelector('.form-error');
    if (existingError) {
        existingError.remove();
    }
}

// Image lazy loading
function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img.lazy').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

// Add to wishlist functionality (future enhancement)
function initWishlist() {
    const wishlistButtons = document.querySelectorAll('.wishlist-btn');
    
    wishlistButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            toggleWishlist(productId, this);
        });
    });
}

function toggleWishlist(productId, button) {
    // This would typically make an API call to add/remove from wishlist
    button.classList.toggle('active');
    
    if (button.classList.contains('active')) {
        showNotification('Product added to wishlist!', 'success');
    } else {
        showNotification('Product removed from wishlist.', 'info');
    }
}

// Product search enhancement
function initSearch() {
    const searchInput = document.querySelector('#search-input');
    
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            const query = this.value.trim();
            
            if (query.length >= 2) {
                performSearch(query);
            }
        }, 300));
    }
}

function performSearch(query) {
    // This would typically make an API call for search results
    console.log('Searching for:', query);
    // For now, we'll just submit the form if it exists
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.submit();
    }
}

// Initialize all enhancements when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Existing initializations
    initAddToCartButtons();
    initCartQuantityButtons();
    initRemoveFromCartButtons();
    initDashboardNavigation();
    initProductSpecifications();
    initFilters();
    
    // New initializations
    enhanceFormValidation();
    initLazyLoading();
    initWishlist();
    initSearch();
    
    // Display any flash messages
    displayFlashMessages();
});

// Display flash messages from PHP
function displayFlashMessages() {
    const flashMessages = document.querySelectorAll('.flash-message');
    
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            message.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                message.remove();
            }, 500);
        }, 5000);
    });
}

// Export additional functions
window.TechPioneer = {
    ...window.TechPioneer,
    isValidEmail,
    isValidPassword,
    initLazyLoading,
    initWishlist,
    initSearch
};