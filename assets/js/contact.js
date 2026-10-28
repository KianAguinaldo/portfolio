/**
 * Contact Form AJAX Handler & Toast Notifications
 * Kian Lee Raian M. Aguinaldo - IT Graduate Portfolio
 */

document.addEventListener('DOMContentLoaded', () => {
    const contactForm = document.getElementById('contact-form');
    const submitBtn = document.getElementById('contact-submit-btn');

    // Create Toast Container if not present
    let toast = document.getElementById('contact-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'contact-toast';
        toast.className = 'toast';
        toast.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: rgba(12, 17, 30, 0.95);
            backdrop-filter: blur(14px);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: 16px 22px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6);
            z-index: 9999;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            max-width: 420px;
            font-size: 0.92rem;
            color: var(--text-primary);
        `;
        document.body.appendChild(toast);
    }

    function showToast(message, isSuccess = true) {
        toast.innerHTML = `
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="${isSuccess ? '#5AB9EA' : '#ef4444'}" stroke-width="2">
                ${isSuccess
                    ? '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>'
                    : '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>'}
            </svg>
            <span>${message}</span>
        `;
        toast.style.borderColor = isSuccess ? 'var(--color-cerulean)' : '#ef4444';
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';

        setTimeout(() => {
            toast.style.transform = 'translateY(100px)';
            toast.style.opacity = '0';
        }, 5000);
    }

    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Read form values
            const formData = new FormData(contactForm);
            const data = {
                name: formData.get('name'),
                email: formData.get('email'),
                subject: formData.get('subject'),
                message: formData.get('message')
            };

            // Basic front-end sanity check
            if (!data.name || !data.email || !data.subject || !data.message) {
                showToast('Please fill out all required fields.', false);
                return;
            }

            // Button loading state
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg width="18" height="18" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="none" class="animate-spin" style="animation: spin 1s linear infinite;">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path>
                </svg>
                <span>Saving to Database...</span>
            `;

            try {
                const response = await fetch('api/contact.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    showToast(result.message, true);
                    contactForm.reset();
                } else {
                    showToast(result.message || 'Failed to send message. Please try again.', false);
                }
            } catch (error) {
                showToast('Network error or server unavailable. Please try again.', false);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    }
});

// Spin keyframes injected dynamically for loader
const spinStyle = document.createElement('style');
spinStyle.textContent = `
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(spinStyle);
