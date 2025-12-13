@extends('base.add_edit')

@section('scripts')
    <script type="text/javascript"
            src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>
    <script>
        $(document).ready(function () {
            Base.addEdit();

            let isEditing = $('.base-form').data('is-editing') === 1;
            let merchant = $('[name="merchant_id"]');
            let categories = $('[name="category_id"]');
            let selectedCategory = '{{ isset($data) ? $data->category_id : "" }}';

            merchant.on('change', function () {
                let id = $(this).val();

                Utils.ajax(Utils.baseUrl(`manage/menu-categories/by-merchant/${id}`), 'GET')
                    .then((results) => {
                        categories.find('option').remove();
                        results.forEach((item) => {
                            let isSelected = isEditing && selectedCategory === item.id;
                            categories.append(`<option ${isSelected ? 'selected' : ''} value="${item.id}">${item.name}</option>`);
                        });
                    });
            });

            if (isEditing) {
                setTimeout(() => {
                    merchant.change();
                }, 500)
            }
        });
    </script>
@endsection


