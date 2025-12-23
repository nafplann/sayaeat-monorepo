const Avatar = {
    init(elem) {

        let modal = Utils.loadModal('lg', 'none');
        let title = modal.find('.modal-title');
        let body = modal.find('.modal-body');
        let $image = null;
        let $file = null;
        let preview = $(elem.data('preview'));


        title.text('Change Avatar');

        Utils.ajax(Utils.baseUrl('/manage/avatar'), 'GET', 'text/html', null)
            .then(results => {
                body.html(results);

                $image = $('#cropper-img');
                $file = modal.find('input[type="file"]');
                $('#cropper-img').attr('src', elem.data('current'));

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

        modal.on('submit', '#avatar-form', function(e) {
            e.preventDefault();

            let form = $(this);
            let formData = new FormData(form[0]);
            let url = form.attr('action');
            let method = form.attr('method');

            $image.cropper('getCroppedCanvas').toBlob((blob) => {
                formData.append('key', elem.data('key'));
                formData.append('model', elem.data('model'));
                formData.append('id', elem.data('id'));
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

                        preview.attr('src', results.avatar);
                        elem.data('current', results.avatar);
                        Utils.notify('', results.message);
                        modal.modal('hide');
                    });
            });
        });
    },
}

export default Avatar;
