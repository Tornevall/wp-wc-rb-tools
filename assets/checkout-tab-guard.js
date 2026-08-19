(function () {
    'use strict';

    var config = window.tornevallResursCheckoutTabGuard || {};
    var endpoint = String(config.endpoint || '');
    var fieldName = String(config.fieldName || 'tornevall_checkout_tab_id');
    var headerName = String(config.headerName || 'X-Tornevall-Checkout-Tab');
    var message = String(config.message || 'Checkout is already open in another tab. Close the other checkout tab before continuing.');
    var heartbeatMs = Number(config.heartbeatMs || 20000);
    var blockedRetryMs = Number(config.blockedRetryMs || 10000);
    var storageKey = 'tornevall_resurs_checkout_tab_guard_event';
    var channelName = 'tornevall_resurs_checkout_tab_guard';
    var heartbeatTimer = null;
    var retryTimer = null;
    var owner = false;
    var released = false;
    var channel = null;

    if (!endpoint || !config.nonce) {
        return;
    }

    function createTabId() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID().toLowerCase();
        }

        var values = new Uint32Array(4);
        if (window.crypto && typeof window.crypto.getRandomValues === 'function') {
            window.crypto.getRandomValues(values);
            return Array.prototype.map.call(values, function (value) {
                return value.toString(16).padStart(8, '0');
            }).join('-');
        }

        return String(Date.now()) + '-' + Math.random().toString(36).slice(2) + '-' + Math.random().toString(36).slice(2);
    }

    function getTabId() {
        var existing = '';

        try {
            existing = window.sessionStorage.getItem('tornevall_resurs_checkout_tab_id') || '';
        } catch (error) {
            existing = '';
        }

        if (/^[a-z0-9-]{16,64}$/.test(existing)) {
            return existing;
        }

        var generated = createTabId().replace(/[^a-z0-9-]/g, '').slice(0, 64);
        try {
            window.sessionStorage.setItem('tornevall_resurs_checkout_tab_id', generated);
        } catch (error) {
            // sessionStorage may be unavailable in hardened/private browser contexts.
        }

        return generated;
    }

    var tabId = getTabId();

    function buildPayload(operation) {
        var payload = new URLSearchParams();
        payload.append('nonce', String(config.nonce));
        payload.append('operation', operation);
        payload.append('tab_id', tabId);
        return payload;
    }

    function requestLease(operation, keepalive) {
        return window.fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            keepalive: Boolean(keepalive),
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: buildPayload(operation).toString()
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            if (!response || response.success !== true || !response.data) {
                throw new Error('Checkout tab guard lease request failed.');
            }

            return response.data;
        });
    }

    function appendClassicField() {
        var forms = document.querySelectorAll('form.checkout');
        Array.prototype.forEach.call(forms, function (form) {
            var input = form.querySelector('input[name="' + fieldName + '"]');
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.name = fieldName;
                form.appendChild(input);
            }
            input.value = tabId;
        });
    }

    function findCheckoutContainer() {
        return document.querySelector('.wp-block-woocommerce-checkout')
            || document.querySelector('form.checkout')
            || document.querySelector('.woocommerce')
            || document.querySelector('main')
            || document.body;
    }

    function renderWarning() {
        var warning = document.getElementById('tornevall-checkout-tab-warning');
        if (owner) {
            if (warning) {
                warning.remove();
            }
            return;
        }

        if (warning) {
            warning.textContent = message;
            return;
        }

        warning = document.createElement('div');
        warning.id = 'tornevall-checkout-tab-warning';
        warning.className = 'woocommerce-error tornevall-checkout-tab-warning';
        warning.setAttribute('role', 'alert');
        warning.textContent = message;

        var container = findCheckoutContainer();
        if (container.firstChild) {
            container.insertBefore(warning, container.firstChild);
        } else {
            container.appendChild(warning);
        }
    }

    function getPlaceOrderButtons() {
        return document.querySelectorAll(
            '#place_order, ' +
            '.wc-block-components-checkout-place-order-button, ' +
            'button[type="submit"][class*="checkout"]'
        );
    }

    function updateSubmitState() {
        Array.prototype.forEach.call(getPlaceOrderButtons(), function (button) {
            if (!owner) {
                if (!button.disabled) {
                    button.setAttribute('data-tornevall-tab-guard-disabled', '1');
                    button.disabled = true;
                }
                button.setAttribute('aria-disabled', 'true');
                return;
            }

            if (button.getAttribute('data-tornevall-tab-guard-disabled') === '1') {
                button.disabled = false;
                button.removeAttribute('data-tornevall-tab-guard-disabled');
                button.removeAttribute('aria-disabled');
            }
        });
    }

    function updateUi() {
        appendClassicField();
        renderWarning();
        updateSubmitState();
        document.body.classList.toggle('tornevall-checkout-tab-blocked', !owner);
    }

    function stopHeartbeat() {
        if (heartbeatTimer !== null) {
            window.clearInterval(heartbeatTimer);
            heartbeatTimer = null;
        }
    }

    function stopRetry() {
        if (retryTimer !== null) {
            window.clearInterval(retryTimer);
            retryTimer = null;
        }
    }

    function becomeOwner() {
        owner = true;
        stopRetry();
        updateUi();

        if (heartbeatTimer === null) {
            heartbeatTimer = window.setInterval(function () {
                requestLease('heartbeat', false).then(function (data) {
                    if (!data.owner) {
                        becomeBlocked();
                    }
                }).catch(function () {
                    // A temporary network failure must not silently transfer ownership.
                    // Server-side checkout validation remains authoritative.
                });
            }, heartbeatMs);
        }
    }

    function attemptClaim() {
        if (released) {
            return;
        }

        requestLease('claim', false).then(function (data) {
            if (data.owner) {
                becomeOwner();
            } else {
                becomeBlocked();
            }
        }).catch(function () {
            becomeBlocked();
        });
    }

    function becomeBlocked() {
        owner = false;
        stopHeartbeat();
        updateUi();

        if (retryTimer === null) {
            retryTimer = window.setInterval(attemptClaim, blockedRetryMs);
        }
    }

    function broadcastRelease() {
        var event = JSON.stringify({
            type: 'released',
            tabId: tabId,
            timestamp: Date.now()
        });

        if (channel) {
            channel.postMessage({ type: 'released', tabId: tabId });
        }

        try {
            window.localStorage.setItem(storageKey, event);
            window.localStorage.removeItem(storageKey);
        } catch (error) {
            // localStorage is only a fast cross-tab signal; the server lease still expires.
        }
    }

    function releaseLease() {
        if (released) {
            return;
        }

        released = true;
        stopHeartbeat();
        stopRetry();

        if (owner) {
            var payload = buildPayload('release');
            if (navigator.sendBeacon) {
                var blob = new Blob([payload.toString()], {
                    type: 'application/x-www-form-urlencoded; charset=UTF-8'
                });
                navigator.sendBeacon(endpoint, blob);
            } else {
                requestLease('release', true).catch(function () {
                    // The TTL handles browsers that cannot deliver the release request.
                });
            }
            broadcastRelease();
        }
    }

    function installCrossTabSignals() {
        if ('BroadcastChannel' in window) {
            channel = new BroadcastChannel(channelName);
            channel.addEventListener('message', function (event) {
                if (event.data && event.data.type === 'released' && event.data.tabId !== tabId && !owner) {
                    attemptClaim();
                }
            });
        }

        window.addEventListener('storage', function (event) {
            if (event.key !== storageKey || !event.newValue || owner) {
                return;
            }

            try {
                var data = JSON.parse(event.newValue);
                if (data.type === 'released' && data.tabId !== tabId) {
                    attemptClaim();
                }
            } catch (error) {
                // Ignore malformed cross-tab events.
            }
        });
    }

    function isStoreApiCheckoutUrl(value) {
        var url = String(value || '');
        return /\/wc\/store\/v[0-9]+\/checkout(?:[/?#]|$)/.test(url)
            || /rest_route=%2Fwc%2Fstore%2Fv[0-9]+%2Fcheckout/i.test(url)
            || /rest_route=\/wc\/store\/v[0-9]+\/checkout/i.test(url);
    }

    function installApiFetchMiddleware() {
        if (!window.wp || !window.wp.apiFetch || typeof window.wp.apiFetch.use !== 'function') {
            return;
        }

        window.wp.apiFetch.use(function (options, next) {
            var target = options.path || options.url || '';
            if (isStoreApiCheckoutUrl(target)) {
                options.headers = Object.assign({}, options.headers || {});
                options.headers[headerName] = tabId;
            }
            return next(options);
        });
    }

    function installFetchFallback() {
        if (typeof window.fetch !== 'function' || window.fetch.__tornevallCheckoutTabGuard) {
            return;
        }

        var originalFetch = window.fetch;
        var guardedFetch = function (input, init) {
            var target = typeof input === 'string' ? input : (input && input.url ? input.url : '');
            if (!isStoreApiCheckoutUrl(target)) {
                return originalFetch.call(window, input, init);
            }

            var options = Object.assign({}, init || {});
            var sourceHeaders = options.headers || (input instanceof Request ? input.headers : undefined);
            var headers = new Headers(sourceHeaders || {});
            headers.set(headerName, tabId);

            if (input instanceof Request) {
                input = new Request(input, { headers: headers });
            } else {
                options.headers = headers;
            }

            return originalFetch.call(window, input, options);
        };

        guardedFetch.__tornevallCheckoutTabGuard = true;
        window.fetch = guardedFetch;
    }

    document.addEventListener('submit', function (event) {
        if (owner) {
            return;
        }

        var form = event.target;
        if (form && form.matches && form.matches('form.checkout')) {
            event.preventDefault();
            event.stopImmediatePropagation();
            updateUi();
        }
    }, true);

    document.addEventListener('click', function (event) {
        if (owner) {
            return;
        }

        var target = event.target && event.target.closest
            ? event.target.closest('#place_order, .wc-block-components-checkout-place-order-button')
            : null;

        if (target) {
            event.preventDefault();
            event.stopImmediatePropagation();
            updateUi();
        }
    }, true);

    var observer = new MutationObserver(function () {
        appendClassicField();
        if (!owner) {
            renderWarning();
            updateSubmitState();
        }
    });

    installCrossTabSignals();
    installFetchFallback();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            installApiFetchMiddleware();
            appendClassicField();
            updateUi();
            observer.observe(document.body, { childList: true, subtree: true });
            attemptClaim();
        });
    } else {
        installApiFetchMiddleware();
        appendClassicField();
        updateUi();
        observer.observe(document.body, { childList: true, subtree: true });
        attemptClaim();
    }

    window.addEventListener('pagehide', releaseLease);
}());
