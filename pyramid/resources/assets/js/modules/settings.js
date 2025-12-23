const Settings = {
    index() {
        $('#language').change(function() {
            let id = $(this).data('id');
            let lang = $(this).val();
            window.location.href = Utils.baseUrl(`/manage/settings/${lang}`);
        });

        $('#settings-form').on('submit', function(e) {
            e.preventDefault();

            let form = $(this);
            let formData = new FormData(form[0]);
            let url = form.attr('action');
            let method = form.attr('method');

            formData.append('google_service_account', form.find('[name="google_service_account"]').text());

            Utils.disableSubmit(form);
            Utils.ajaxFile(url, method, 'json', formData)
                .fail(xhr => {
                    Utils.formFailed(form, `Code ${xhr.status}: ${xhr.statusText}`);
                })
                .then(results => {
                    if (! results.status) {
                        Utils.formFailed(form, results.message);
                        return;
                    }
                    Utils.notify('', results.message);
                    Utils.enableSubmit(form);
                });
        });
    }
}

export default Settings;
