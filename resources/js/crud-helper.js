/**
 * Axios CRUD Helper
 * Provides reusable axios functions for CRUD operations
 */

import axios from 'axios';

// Configure axios to include CSRF token
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

// Get CSRF token from meta tag
const token = document.querySelector('meta[name="csrf-token"]')?.content;
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
}

export const crudHelper = {
    /**
     * Create a new resource via AJAX
     */
    async create(url, data) {
        try {
            const response = await axios.post(url, data);
            return { success: true, data: response.data, status: response.status };
        } catch (error) {
            return { 
                success: false, 
                message: error.response?.data?.message || 'Error creating resource',
                errors: error.response?.data?.errors || {}
            };
        }
    },

    /**
     * Update a resource via AJAX
     */
    async update(url, data) {
        try {
            const response = await axios.put(url, data);
            return { success: true, data: response.data, status: response.status };
        } catch (error) {
            return { 
                success: false, 
                message: error.response?.data?.message || 'Error updating resource',
                errors: error.response?.data?.errors || {}
            };
        }
    },

    /**
     * Delete a resource via AJAX
     */
    async delete(url) {
        try {
            const response = await axios.delete(url);
            return { success: true, data: response.data, status: response.status };
        } catch (error) {
            return { 
                success: false, 
                message: error.response?.data?.message || 'Error deleting resource'
            };
        }
    },

    /**
     * Fetch a single resource
     */
    async fetch(url) {
        try {
            const response = await axios.get(url);
            return { success: true, data: response.data };
        } catch (error) {
            return { 
                success: false, 
                message: error.response?.data?.message || 'Error fetching resource'
            };
        }
    },

    /**
     * Display toast notification
     */
    showToast(message, type = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.style.position = 'fixed';
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '9999';
        alertDiv.style.minWidth = '300px';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => alertDiv.remove(), 5000);
    },

    /**
     * Display form errors
     */
    showFormErrors(form, errors) {
        // Clear previous errors
        form.querySelectorAll('.invalid-feedback').forEach(el => el.style.display = 'none');
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        // Show new errors
        Object.keys(errors).forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = input.parentElement.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.textContent = errors[field][0];
                    feedback.style.display = 'block';
                }
            }
        });
    }
};

export default crudHelper;
