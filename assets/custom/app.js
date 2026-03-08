/**
 * app.js — Custom JavaScript for IT Portal Helpdesk
 */

document.addEventListener('DOMContentLoaded', function () {

    // Auto-dismiss flash messages after 5 seconds
    document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
        setTimeout(function () {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    // Confirm before status change
    document.querySelectorAll('form[action*="update-status"]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!confirm('Yakin ingin mengubah status ticket?')) {
                e.preventDefault();
            }
        });
    });

    // Copy button feedback
    document.querySelectorAll('[onclick*="clipboard"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var original = this.textContent;
            this.textContent = 'Copied!';
            var self = this;
            setTimeout(function () {
                self.textContent = original;
            }, 2000);
        });
    });

});
