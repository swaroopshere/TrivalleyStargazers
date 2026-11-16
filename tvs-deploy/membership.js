(function () {
    'use strict';
    function initialize() {
        var form = document.forms.application;
        if (!form) return;
        var buttons = [document.getElementById('submit1'), document.getElementById('submit2')];
        var labels = buttons.map(function (button) { return button.value; });
        var status = document.getElementById('applicationStatus');
        var submitting = false;
        var timer;
        document.getElementById('preset').value = 'Preset';
        function reset() {
            submitting = false;
            clearTimeout(timer);
            form.removeAttribute('aria-busy');
            buttons.forEach(function (button, index) {
                button.disabled = false;
                button.value = labels[index];
            });
            status.textContent = '';
        }
        form.addEventListener('submit', function (event) {
            if (submitting) {
                event.preventDefault();
                return;
            }
            var flag = document.getElementById('new');
            if (event.submitter) flag.value = event.submitter.id === 'submit2' ? 'No' : 'Yes';
            if (flag.value === 'unset') flag.value = 'Yes';
            submitting = true;
            form.setAttribute('aria-busy', 'true');
            buttons.forEach(function (button) {
                button.disabled = true;
                button.value = 'Submitting...';
            });
            status.textContent = 'Submitting your application. Please wait; you will continue to the payment page.';
            timer = setTimeout(function () {
                status.textContent = 'Still waiting for confirmation. Please do not submit again. If this does not complete, contact secretary@trivalleystargazers.org to check whether it was received.';
            }, 15000);
        });
        window.addEventListener('pageshow', reset);
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize);
    else initialize();
}());
