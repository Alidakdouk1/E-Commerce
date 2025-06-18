// Main JavaScript for the site
document.addEventListener('DOMContentLoaded', function() {
    // Update cart count on page load
    updateCartCount();
    
    // Add to cart buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            addToCart(productId);
        });
    });
});

// Add item to cart
function addToCart(productId, quantity = 1) {
    // Check if user is logged in
    fetch('backend/check_login.php')
        .then(response => response.json())
        .then(data => {
            if (data.logged_in) {
                // User is logged in, add to database cart
                addToDbCart(productId, quantity);
            } else {
                // User is not logged in, redirect to login page
                window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname);
            }
        })
        .catch(error => {
            console.error('Error checking login status:', error);
        });
}

// Add to database cart
function addToDbCart(productId, quantity) {
    fetch('backend/cart_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showToast('Product added to cart successfully!', 'success');
            
            // Update cart count
            updateCartCount();
        } else {
            // Show error message
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error adding to cart:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

// Update cart count
function updateCartCount() {
    fetch('backend/get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cartCountElement = document.getElementById('cartCount');
                if (cartCountElement) {
                    cartCountElement.textContent = data.count;
                }
            }
        })
        .catch(error => {
            console.error('Error updating cart count:', error);
        });
}

// Show toast notification
function showToast(message, type = 'info') {
    // Check if toast container exists
    let toastContainer = document.querySelector('.toast-container');
    
    // If not, create it
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toastId = 'toast-' + Date.now();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'primary'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.setAttribute('id', toastId);
    
    // Toast content
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    // Add toast to container
    toastContainer.appendChild(toast);
    
    // Initialize and show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}
