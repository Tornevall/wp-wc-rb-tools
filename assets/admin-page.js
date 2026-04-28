(function () {
    'use strict';

    // Hide WooCommerce's global "Save changes" button only on our tester section.
    var params = new URLSearchParams(window.location.search || '');
    if (
        params.get('tab') === 'tornevall_resurs_toolbox' &&
        params.get('section') === 'order_status_tester'
    ) {
        var wcSaveButton = document.querySelector('.woocommerce-save-button');
        if (wcSaveButton && wcSaveButton.closest('p.submit')) {
            wcSaveButton.closest('p.submit').style.display = 'none';
        }
    }

    var btn = document.getElementById('check-version-btn');
    if (!btn) {
        return;
    }

    var config = window.tornevallResursToolboxAdmin || {};
    var messages = config.messages || {};
    var resultDiv = document.getElementById('version-check-result');
    var spinner = document.getElementById('version-check-spinner');
    var wordpressPluginUrl = btn.getAttribute('data-wordpress-plugin-url') || '';
    var pluginsManagerUrl = btn.getAttribute('data-plugins-manager-url') || '';
    var ajaxUrl = config.ajaxUrl || window.ajaxurl || '';

    function buildActionButton(url, label, isExternal) {
        if (!url) {
            return '';
        }

        var attrs = ' class="button button-secondary"';
        if (isExternal) {
            attrs += ' target="_blank" rel="noopener noreferrer"';
        }

        return '<a href="' + url + '"' + attrs + '>' + label + '</a>';
    }

    function buildActionButtons(data) {
        var buttons = [];
        var releaseUrl = 'https://bitbucket.org/resursbankplugins/resursbank-woocommerce/src/' + encodeURIComponent(data.latest || '') + '/';

        buttons.push(buildActionButton(releaseUrl, messages.viewRelease, true));

        if (wordpressPluginUrl) {
            buttons.push(buildActionButton(wordpressPluginUrl, messages.viewOnWordPress, true));
        }

        if (pluginsManagerUrl) {
            buttons.push(buildActionButton(pluginsManagerUrl, messages.upgradeInPlugins, false));
        }

        buttons = buttons.filter(function (button) {
            return button !== '';
        });

        if (!buttons.length) {
            return '';
        }

        return '<p style="display:flex; gap:8px; flex-wrap:wrap; margin-top:12px;">' + buttons.join('') + '</p>';
    }

    function setLoading(isLoading) {
        if (spinner) {
            spinner.classList.toggle('is-active', isLoading);
        }

        btn.disabled = isLoading;

        if (resultDiv && isLoading) {
            resultDiv.style.display = 'none';
        }
    }

    function renderHtml(html) {
        if (!resultDiv) {
            return;
        }

        resultDiv.innerHTML = html;
        resultDiv.style.display = 'block';
    }

    function extractError(response) {
        if (!response || !response.data) {
            return '';
        }

        return response.data.error || response.data.message || '';
    }

    function renderResponse(response) {
        var html = '';

        if (response && response.success) {
            var data = response.data || {};
            html += '<div class="notice" style="padding: 10px; border-left: 4px solid #0073aa;">';
            html += '<p><strong>' + messages.latestVersion + '</strong> ' + (data.latest || '') + '</p>';

            if (data.is_outdated) {
                html += '<p style="color: #d63638;"><strong>⚠️ ' + messages.updateAvailable + '</strong></p>';
                html += '<p>' + messages.yourVersion + ' ' + (data.installed || '') + '</p>';
                html += buildActionButtons(data);
            } else if (data.is_dev_version) {
                html += '<p style="color: #2f7c31;"><strong>📦 ' + messages.devDetected + '</strong></p>';
                html += '<p>' + messages.runningVersion + ' <strong>' + (data.installed || '') + '</strong>, ' + messages.newerThanStable + ' (' + (data.latest || '') + ').</p>';
                html += '<p>' + messages.thisCouldBe + '</p>';
                html += '<ul style="margin-left: 20px;">';
                html += '<li>' + messages.devPreRelease + '</li>';
                html += '<li>' + messages.hotfixNotTagged + '</li>';
                html += '<li>' + messages.unreleasedUpdate + '</li>';
                html += '</ul>';
                html += '<p>' + messages.verifyInstall + '</p>';
            } else {
                html += '<p style="color: #1d8f1f;"><strong>✓ ' + messages.latestStable + '</strong></p>';
            }

            html += '</div>';
        } else {
            html += '<div class="notice notice-error" style="padding: 10px; border-left: 4px solid #dc3545;">';
            html += '<p><strong>' + messages.errorChecking + '</strong> ' + extractError(response) + '</p>';
            html += '</div>';
        }

        renderHtml(html);
    }

    function renderError() {
        var html = '<div class="notice notice-error" style="padding: 10px; border-left: 4px solid #dc3545;">';
        html += '<p><strong>' + messages.errorLabel + '</strong> ' + messages.failedConnect + '</p>';
        html += '</div>';
        renderHtml(html);
    }

    function sendRequest() {
        var payload = new URLSearchParams();
        payload.append('action', 'tornevall_resurs_toolbox_check_version');
        payload.append('nonce', btn.getAttribute('data-nonce') || '');
        payload.append('installed_version', btn.getAttribute('data-installed-version') || '');

        setLoading(true);

        fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: payload.toString()
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (response) {
                renderResponse(response);
            })
            .catch(function () {
                renderError();
            })
            .finally(function () {
                setLoading(false);
            });
    }

    btn.addEventListener('click', function (event) {
        event.preventDefault();
        sendRequest();
    });
})();

/* ------------------------------------------------------------------ *
 * Order Status Tester – AJAX quick-select dropdown                    *
 * ------------------------------------------------------------------ */
(function () {
    'use strict';

    var config            = window.tornevallResursToolboxAdmin || {};
    var ost               = config.orderStatusTester || {};
    var ajaxUrl           = config.ajaxUrl || window.ajaxurl || '';

    var quickSelect       = document.getElementById('tornevall-resurs-order-quick-select');
    var orderIdInput      = document.getElementById('tornevall-resurs-order-id');
    var spinner           = document.getElementById('tornevall-resurs-orders-spinner');

    if (!quickSelect || !orderIdInput) {
        return;
    }

    if (ost.enabled === false) {
        quickSelect.innerHTML = '';
        var disabledOpt = document.createElement('option');
        disabledOpt.value = '';
        disabledOpt.textContent = ost.disabledText || 'Order Status Tester is disabled in Production';
        quickSelect.appendChild(disabledOpt);
        quickSelect.disabled = true;
        setSpinner(false);
        return;
    }

    function setSpinner(active) {
        if (!spinner) { return; }
        spinner.classList.toggle('is-active', active);
    }

    function loadRecentOrders() {
        setSpinner(true);
        quickSelect.disabled = true;

        var payload = new URLSearchParams();
        payload.append('action', 'tornevall_resurs_order_status_recent_orders');
        payload.append('nonce', ost.nonce || '');

        fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: payload.toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (response) {
                quickSelect.innerHTML = '';

                var placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = ost.selectPlaceholder || '— Select recent order —';
                quickSelect.appendChild(placeholder);

                if (!response || !response.success || !Array.isArray(response.data) || response.data.length === 0) {
                    placeholder.textContent = ost.errorText || 'Could not load orders';
                    return;
                }

                response.data.forEach(function (order) {
                    var opt = document.createElement('option');
                    opt.value = String(order.id);
                    opt.textContent = '#' + order.id
                        + '  ' + order.customer
                        + '  [' + order.status + ']'
                        + '  ' + order.date;
                    opt.dataset.status = order.status_key || '';
                    quickSelect.appendChild(opt);
                });
            })
            .catch(function () {
                quickSelect.innerHTML = '';
                var err = document.createElement('option');
                err.value = '';
                err.textContent = ost.errorText || 'Could not load orders';
                quickSelect.appendChild(err);
            })
            .finally(function () {
                setSpinner(false);
                quickSelect.disabled = false;
            });
    }

    // Expose refresh hook for other admin-page modules.
    window.tornevallResursToolboxRefreshRecentOrders = loadRecentOrders;

    // Selecting from dropdown → populate text field (takes priority)
    quickSelect.addEventListener('change', function () {
        if (this.value !== '') {
            orderIdInput.value = this.value;
            orderIdInput.dispatchEvent(new Event('input'));
        }
    });

    // Typing manually → clear dropdown selection (no conflict)
    orderIdInput.addEventListener('input', function () {
        var current = quickSelect.value;
        if (current !== '' && current !== this.value) {
            quickSelect.value = '';
        }
    });

    // Fetch on page load
    loadRecentOrders();
})();

/* ------------------------------------------------------------------ *
 * Order Status Tester – AJAX status update (no reload)               *
 * ------------------------------------------------------------------ */
(function () {
    'use strict';

    var config = window.tornevallResursToolboxAdmin || {};
    var ost = config.orderStatusTester || {};
    var ajaxUrl = config.ajaxUrl || window.ajaxurl || '';

    var form = document.getElementById('tornevall-resurs-form-tester');
    var liveResult = document.getElementById('tornevall-resurs-order-status-live-result');
    var updateButton = document.getElementById('tornevall-resurs-update-status-btn');
    var updateSpinner = document.getElementById('tornevall-resurs-update-status-spinner');
    var progressBox = document.getElementById('tornevall-resurs-order-status-progress');
    var progressText = document.getElementById('tornevall-resurs-order-status-progress-text');

    if (!form || !liveResult || !updateButton || ost.enabled === false) {
        return;
    }

    var submitButton = updateButton;
    var submitButtonText = submitButton ? submitButton.textContent : '';

    function escapeHtml(value) {
        var div = document.createElement('div');
        div.textContent = String(value || '');
        return div.innerHTML;
    }

    function setSubmitting(isSubmitting) {
        if (!submitButton) {
            return;
        }

        submitButton.disabled = isSubmitting;
        if (updateSpinner) {
            updateSpinner.classList.toggle('is-active', isSubmitting);
        }
        if (isSubmitting) {
            submitButton.textContent = ost.updatingText || 'Updating...';
        } else {
            submitButton.textContent = submitButtonText;
        }
    }

    function setProgress(message, isVisible) {
        if (!progressBox || !progressText) {
            return;
        }

        progressText.textContent = String(message || '');
        progressBox.style.display = isVisible ? 'block' : 'none';
    }

    function renderError(message) {
        liveResult.innerHTML =
            '<div class="notice notice-error is-dismissible"><p>' + escapeHtml(message) + '</p></div>';
    }

    function renderSuccess(data) {
        var details = Array.isArray(data.details) ? data.details : [];
        var detailsHtml = details.map(function (line) {
            return '<li>' + escapeHtml(line) + '</li>';
        }).join('');

        liveResult.innerHTML =
            '<div class="notice notice-success is-dismissible"><p>' + escapeHtml(data.success_message || '') + '</p></div>' +
            '<div class="notice notice-info"><p><strong>' + escapeHtml(data.details_title || '') + '</strong></p>' +
            '<ul style="margin: 0 0 0 16px; list-style: disc;">' + detailsHtml + '</ul></div>';
    }

    function submitStatusUpdate() {
        if (submitButton && submitButton.disabled) {
            return;
        }

        var orderIdInput = document.getElementById('tornevall-resurs-order-id');
        var statusSelect = document.getElementById('tornevall-resurs-status');

        if (orderIdInput && String(orderIdInput.value || '').trim() === '') {
            renderError(ost.invalidOrderIdText || 'Invalid order ID');
            return;
        }

        if (statusSelect && String(statusSelect.value || '').trim() === '') {
            renderError(ost.selectStatusText || 'Please select a status');
            return;
        }

        var formData = new FormData(form);
        var payload = new URLSearchParams();
        formData.forEach(function (value, key) {
            payload.append(key, value);
        });

        payload.set('action', 'tornevall_resurs_order_status_update');
        payload.set('nonce', ost.nonce || '');

        setProgress(ost.updatingText || 'Updating status...', true);
        setSubmitting(true);

        fetch(ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: payload.toString()
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (response) {
                if (!response || !response.success || !response.data) {
                    var errorMessage = response && response.data && response.data.message
                        ? response.data.message
                        : (ost.updateErrorText || 'Could not update order status');
                    renderError(errorMessage);
                    setProgress(errorMessage, true);
                    return;
                }

                renderSuccess(response.data);
                setProgress(response.data.success_message || '', true);
                form.reset();

                if (typeof window.tornevallResursToolboxRefreshRecentOrders === 'function') {
                    window.tornevallResursToolboxRefreshRecentOrders();
                }
            })
            .catch(function () {
                var defaultError = ost.updateErrorText || 'Could not update order status';
                renderError(defaultError);
                setProgress(defaultError, true);
            })
            .finally(function () {
                setSubmitting(false);
            });
    }

    window.tornevallResursToolboxRunOrderStatusUpdate = submitStatusUpdate;

    updateButton.addEventListener('click', function (event) {
        event.preventDefault();
        submitStatusUpdate();
    });

    // Extra guard: delegated click binding in case other scripts replace the button node.
    document.addEventListener('click', function (event) {
        var target = event.target;
        if (!target || !target.closest) {
            return;
        }

        var button = target.closest('#tornevall-resurs-update-status-btn');
        if (!button) {
            return;
        }

        event.preventDefault();
        submitStatusUpdate();
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        submitStatusUpdate();
    });
})();

