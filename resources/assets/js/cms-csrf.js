/*
 * This file is part of Bootstrap CMS.
 *
 * The framework verifies a CSRF token on every non GET request, and it drops
 * that token into an unencrypted cookie so that javascript can read it. This
 * file reads the cookie and echoes the token back in the X-CSRF-TOKEN header
 * on every same origin ajax request, which is what the comment endpoints need
 * because they are called without submitting a form.
 */
function cmsCsrfToken() {
    var match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);
    return match ? decodeURIComponent(match[1]) : "";
}

$(document).ajaxSetup({
    beforeSend: function (xhr, settings) {
        if (!settings.crossDomain && settings.type !== "GET") {
            xhr.setRequestHeader("X-CSRF-TOKEN", cmsCsrfToken());
        }
    }
});
