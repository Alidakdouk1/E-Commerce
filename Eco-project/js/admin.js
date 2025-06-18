// Admin Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar on mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.querySelector('.admin-sidebar').classList.toggle('show');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.admin-sidebar');
        const toggle = document.getElementById('sidebarToggle');
        
        if (sidebar && sidebar.classList.contains('show') && 
            !sidebar.contains(event.target) && 
            toggle && !toggle.contains(event.target)) {
            sidebar.classList.remove('show');
        }
    });
    
    // Initialize any DataTables
    if ($.fn.DataTable) {
        $('.datatable').DataTable();
    }
    
    // Initialize tooltips
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Product image preview
    const productImageInput = document.getElementById('product_image');
    const imagePreview = document.getElementById('image_preview');
    
    if (productImageInput && imagePreview) {
        productImageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });
});
