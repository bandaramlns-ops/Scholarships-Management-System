document.querySelectorAll('[data-confirm-form]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        if (!window.confirm(form.getAttribute('data-confirm-form'))) {
            event.preventDefault();
        }
    });
});
