(function () {
    'use strict';

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

