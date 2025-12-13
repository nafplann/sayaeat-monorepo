const Media = {
    index() {
        Media.loadGallery();

        $(document).on('click', '.media-delete', function() {
            let id = $(this).data('id');
            Media.delete(id);
        });
    },
    create() {
        $('#name').keyup(function() {
            let value = $(this).val();
            $('#slug').val(Utils.slugify(value));
        });

        $('#create-media-form').on('submit', function(e) {
            e.preventDefault();

            let form = $(this);
            let formData = new FormData(form[0]);
            let url = form.attr('action');
            let method = form.attr('method');

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
                    Utils.alert('Success', results.message, 'success');
                    Utils.enableSubmit(form);
                    form[0].reset();
                });
        });
    },
    delete(id) {
        Swal({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            type: 'warning',
            showCancelButton: true,
            showLoaderOnConfirm: true,
            preConfirm: (login) => {
                return fetch(`/media/delete/${id}`, {
                    method: 'delete',
                    credentials: "same-origin",
                    headers: {
                        'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(response.statusText)
                    }
                    return response.json()
                })
                .catch(error => {
                    Swal.showValidationError(
                        `Request failed: ${error}`
                    )
                })
            },
            allowOutsideClick: false
        }).then((result) => {
            Swal(
                result.value.status ? 'Deleted!' : 'Failed',
                result.value.message,
                result.value.status ? 'success' : 'error'
            );

            if (result.value.status) {
                $(`.media-item[data-id="${id}"]`).remove();
                Media.reloadGallery();
            }
        });
    },
    loadGallery() {
        Media.imagePicker();
        Media.videoPicker();
    },
    reloadGallery() {
        $('#image-gallery').data('lightGallery').destroy(true);
        $('#video-gallery').data('lightGallery').destroy(true);
        Media.loadGallery();
    },
    imagePicker() {
        $('#image-gallery').lightGallery({
            thumbnail: true,
            selector: '.gallery .media-view'
        });
    },
    videoPicker() {
        $('#video-gallery').lightGallery({
            // thumbnail: true,
            selector: '.gallery .media-view',
            // videojs: true
        });
    }
}

export default Media;
