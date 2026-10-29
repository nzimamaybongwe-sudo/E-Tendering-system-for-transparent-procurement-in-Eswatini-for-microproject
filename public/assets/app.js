document.addEventListener('DOMContentLoaded', function () {
    const clearCredentialFields = (form, clearIdentifier = false) => {
        if (clearIdentifier) {
            const identifier = form.querySelector('input[name="email"]');
            if (identifier) identifier.value = '';
        }

        form.querySelectorAll('input[type="password"]').forEach((input) => {
            input.value = '';
        });
    };

    const forms = [
        { selector: '#loginForm', action: 'login' },
        { selector: '#registerForm', action: 'register' },
        { selector: '#publishTenderForm', action: 'publish_tender' }
    ];

    forms.forEach(({ selector, action }) => {
        const form = document.querySelector(selector);
        if (!form) return;

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const feedback = form.querySelector('.js-form-feedback');
            const formData = new FormData(form);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                formData.append('csrf_token', csrfToken);
            }

            // Convert underscore to hyphen for API routing (e.g., publish_tender -> api-publish-tender)
            const routeAction = action.replace(/_/g, '-');

            try {
                const response = await fetch('index.php?route=api-' + routeAction, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                    },
                });

                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (parseError) {
                    throw new Error(`Invalid server response: ${text || response.statusText}`);
                }

                if (!feedback) return;

                if (!response.ok) {
                    const message = result?.message || `Server error ${response.status}`;
                    feedback.innerHTML = `<div class="alert alert-danger">${message}</div>`;
                    return;
                }

                if (result.success) {
                    feedback.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                    if (result.redirect) {
                        if (selector === '#loginForm') {
                            clearCredentialFields(form, true);
                        }
                        window.location.href = result.redirect;
                    }
                } else {
                    if (selector === '#loginForm') {
                        clearCredentialFields(form);
                    }
                    feedback.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
                }
            } catch (error) {
                if (selector === '#loginForm') {
                    clearCredentialFields(form);
                }
                if (form.querySelector('.js-form-feedback')) {
                    const detail = error?.message ? ` ${error.message}` : '';
                    form.querySelector('.js-form-feedback').innerHTML = `<div class="alert alert-danger">Unable to connect to the server.${detail}</div>`;
                }
            }
        });
    });

    const loginForm = document.querySelector('#loginForm');
    if (loginForm) {
        window.addEventListener('pagehide', function () {
            clearCredentialFields(loginForm, true);
        });

        window.addEventListener('pageshow', function () {
            window.setTimeout(function () {
                clearCredentialFields(loginForm, true);
            }, 0);
        });
    }
});
