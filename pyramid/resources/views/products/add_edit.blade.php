@extends('base.add_edit')

@section('scripts')
    <script type="text/javascript"
            src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>
    <script>
        $(document).ready(function () {
            Base.addEdit();

            let isEditing = $('.base-form').data('is-editing') === 1;
            let store = $('[name="store_id"]');
            let categories = $('[name="categories[]"]');
            let selectedCategories = '{{ isset($data) ? $data->categories->pluck('id')->join(',') : "" }}'.split(',');

            store.on('change', function () {
                let id = $(this).val();

                Utils.ajax(Utils.baseUrl(`manage/product-categories/by-merchant/${id}`), 'GET')
                    .then((results) => {
                        categories.find('option').remove();
                        results.forEach((item) => {
                            let isSelected = isEditing && selectedCategories.includes(item.id);
                            categories.append(`<option ${isSelected ? 'selected' : ''} value="${item.id}">${item.name}</option>`);
                        });
                    });
            });

            if (isEditing) {
                setTimeout(() => {
                    store.change();
                }, 500)
            }
        });
    </script>
@endsection


