const Profile = {
    index() {
        $('#cropper-init').click(function(e) {
            e.preventDefault();
            Profile.cropper();
        });

        $(document).on('click', '#verify-phone', function(e) {
            e.preventDefault();
            Profile.verifyPhoneNumber($(this));
        });

        $(document).on('submit', '#profile-form', function(e) {
            e.preventDefault();
            Profile.updateProfile($(this));
        });

        $(document).on('submit', '#password-form', function(e) {
            e.preventDefault();
            Profile.updatePassword($(this));
        });
    },
    cropper() {
        let modal = Utils.loadModal('lg', 'none');
        let title = modal.find('.modal-title');
        let body = modal.find('.modal-body');
        let $image = null;
        let $file = null;

        title.text('Change Avatar');

        Utils.ajax(Utils.baseUrl('/manage/profile/avatar'), 'GET', 'text/html', null)
            .then(results => {
                body.html(results);

                $image = $('#cropper-img');
                $file = modal.find('input[type="file"]');

                // Start cropper
                $image.cropper({
                    aspectRatio: 1
                });

                $file.change(function () {
                    var oFReader = new FileReader();

                    oFReader.readAsDataURL(this.files[0]);

                    oFReader.onload = function (oFREvent) {

                        // Destroy the old cropper instance
                        $image.cropper('destroy');

                        // Replace url
                        $image.attr('src', this.result);

                        // Start cropper
                        $image.cropper({
                            aspectRatio: 1
                        });
                    };
                });
            });

        modal.on('click', '#rotate-left', function() {
            $image.cropper('rotate', -90);
        });

        modal.on('click', '#rotate-right', function() {
            $image.cropper('rotate', 90);
        });

        modal.on('submit', '#form-avatar', function(e) {
            e.preventDefault();

            let form = $(this);
            let formData = new FormData(form[0]);
            let url = form.attr('action');
            let method = form.attr('method');

            $image.cropper('getCroppedCanvas').toBlob((blob) => {
                formData.append('avatar', blob);

                Utils.disableSubmit(form);
                Utils.ajaxFile(url, method, 'text/html', formData)
                    .fail(xhr => {
                        Utils.formFailed(form, `Code ${xhr.status}: ${xhr.statusText}`);
                    })
                    .then(results => {
                        if (! results.status) {
                            Utils.formFailed(form, results.message);
                            return;
                        }

                        $('#current-avatar').attr('src', results.avatar);
                        Utils.notify('', results.message);
                        modal.modal('hide');
                    });
            });
        });
    },
    updateProfile(form) {
        let formData = new FormData(form[0]);
        let url = form.attr('action');
        let method = form.attr('method');

        Utils.disableSubmit(form);
        Utils.ajaxFile(url, method, 'json', formData)
            .then(results => {
                if (! results.status) {
                    Utils.formFailed(form, results.message);
                    return;
                }
                Utils.notify('', results.message);
                Utils.enableSubmit(form);
            });
    },
    updatePassword(form) {
        let formData = new FormData(form[0]);
        let url = form.attr('action');
        let method = form.attr('method');

        Utils.disableSubmit(form);
        Utils.ajaxFile(url, method, 'json', formData)
            .then(results => {
                if (! results.status) {
                    Utils.formFailed(form, results.message);
                    return;
                }
                Utils.notify('', results.message);
                Utils.enableSubmit(form);
            });
    },
    verifyPhoneNumber(elem) {
        Utils.disable(elem);
        Utils.ajaxFile(Utils.baseUrl('/manage/profile/verify-phone-number'), 'GET', 'json', {})
            .then(results => {
                Utils.enableSubmit(elem);

                if (! results.status) {
                    Utils.notify('', results.message);
                    return;
                }
                Profile.verifyModal();
            });
    },
    verifyModal() {
        let modal = Utils.loadModal('md');
        let title = modal.find('.modal-title');
        let body = modal.find('.modal-body');

        title.text('Enter Verification Code');

        Utils.ajax(Utils.baseUrl('/manage/profile/verify-phone'), 'GET', 'text/html', null)
            .then(results => {
                body.html(results);
            });

        modal.on('submit', 'form', function(e) {
            e.preventDefault();

            let form = $(this);
            let formData = new FormData(form[0]);
            let url = form.attr('action');
            let method = form.attr('method');

            Utils.disableSubmit(form);
            Utils.ajaxFile(url, method, 'text/html', formData)
                .fail(xhr => {
                    Utils.formFailed(form, `Code ${xhr.status}: ${xhr.statusText}`);
                })
                .then(results => {
                    if (! results.status) {
                        Utils.formFailed(form, results.message);
                        return;
                    }

                    Utils.notify('', results.message);
                    window.location.reload();
                });
        });
    },
}

export default Profile;
