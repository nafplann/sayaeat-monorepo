const Auth = {
    login() {
        $('.btn--submit').attr('disabled', false);

        $(document).on('submit', '#login-form', function (e) {
            e.preventDefault();

            let form = $(this);
            let formData = new FormData(form[0]);
            let url = form.attr('action');
            let method = form.attr('method');
            let box = $('.login');

            // Disable submit button
            Utils.disableSubmit(form);

            // Execute ajax request
            Utils.ajaxFile(url, method, 'json', formData)
                .fail((jqXHR, textStatus) => {
                    Utils.enableSubmit(form);
                    box.addClass('animated shake');

                    setTimeout(() => {
                        box.removeClass('animated shake');
                    }, 1000);

                    grecaptcha.reset();
                })

                .done((data, textStatus) => {
                    if (!data.status) {
                        box.addClass('animated shake');

                        setTimeout(() => {
                            box.removeClass('animated shake');
                        }, 1000);

                        grecaptcha.reset();
                        Utils.enableSubmit(form);
                        return;
                    }

                    Utils.alertWithMessage(`Welcome back, ${data.user.name}`, '', 'success');

                    setTimeout(() => {
                        window.location = data.redirectTo;
                    }, 1500);
                });
        });
    },
    register() {
        let submit = $('.btn--submit');

        $('#agreement').change(function () {
            if ($(this).is(':checked')) {
                submit.attr('disabled', false);
            } else {
                submit.attr('disabled', true);
            }
        });

        $(document).on('submit', '#signup-form', function (e) {
            e.preventDefault();

            let form = $(this);
            let formData = new FormData(form[0]);
            let url = form.attr('action');
            let method = form.attr('method');
            let box = $('#auth-box');

            // Disable submit button
            Utils.disableSubmit(form);

            // Execute ajax request
            Utils.ajaxFile(url, method, 'json', formData)

                .fail((jqXHR, textStatus) => {
                    Utils.enableSubmit(form);
                    Utils.alertWithMessage(`Oops ${jqXHR.status}`, 'Internal server error!', 'error');
                })

                .done((data, textStatus) => {
                    if (!data.status) {
                        Utils.alertWithMessage(`Oops`, data.message, 'error');
                        Utils.enableSubmit(form);
                        return;
                    }

                    Utils.alertWithMessage(`One more step`, data.message, 'success');
                    setTimeout(() => {
                        window.location = '/';
                    }, 1000);
                });
        });
    },
    forgot() {
        $(document).on('submit', '#forgot-form', function (e) {
            e.preventDefault();

            let form = $(this);
            let formData = new FormData(form[0]);
            let url = form.attr('action');
            let method = form.attr('method');
            let box = $('.container > .row');

            // Disable submit button
            Utils.disableSubmit(form);

            // Execute ajax request
            Utils.ajaxFile(url, method, 'json', formData)

                .fail((jqXHR, textStatus) => {
                    Utils.enableSubmit(form);
                    Utils.alertWithMessage(`Oops ${jqXHR.status}`, 'Internal server error!', 'error');
                })

                .done((data, textStatus) => {
                    if (!data.status) {
                        Utils.enableSubmit(form);
                        Utils.alertWithMessage(`Oops`, data.message, 'error');
                        return;
                    }

                    Utils.alertWithMessage('Success', data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 5000);
                });
        });
    },
    reset() {
        $('.btn--submit').attr('disabled', false);

        $(document).on('submit', '#reset-form', function (e) {
            e.preventDefault();

            let form = $(this);
            let formData = new FormData(form[0]);
            let url = form.attr('action');
            let method = form.attr('method');
            let box = $('#auth-box');

            // Disable submit button
            Utils.disableSubmit(form);

            // Execute ajax request
            Utils.ajaxFile(url, method, 'json', formData)

                .fail((jqXHR, textStatus) => {
                    Utils.enableSubmit(form);
                    Utils.alertWithMessage(`Oops ${jqXHR.status}`, 'Internal server error!', 'error');
                })

                .done((data, textStatus) => {
                    if (!data.status) {
                        Utils.enableSubmit(form);
                        Utils.alertWithMessage(`Oops`, data.message, 'error');
                        return;
                    }

                    Utils.alertWithMessage('Success', data.message, 'success');
                    setTimeout(() => {
                        window.location = '/';
                    }, 4000);
                });
        });
    },
};

export default Auth;
