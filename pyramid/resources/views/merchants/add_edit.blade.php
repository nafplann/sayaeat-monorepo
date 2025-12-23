@extends('layouts.app')

@use(App\Enums\InputType)

@php
    $isEditing = isset($data);
@endphp

@section('title', $isEditing ? "Edit $module" : "Create $module")

@section('content')
    <div class="card">
        <form
            class="base-form"
            action="{{ $isEditing ? "$baseUrl/{$data->id}" : $baseUrl }}"
            method="POST"
            enctype="multipart/form-data"
            data-is-editing={{ $isEditing }}>
            {{ $isEditing ? method_field('PUT') : '' }}
            <div class="tab-container">
                <ul class="nav nav-tabs nav-fill" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#home-2" role="tab">Merchant Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#profile-2" role="tab">Operating Hours</a>
                    </li>
                </ul>

                <div class="tab-content px-4 pt-0">
                    <div class="tab-pane active show fade pt-4" id="home-2" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group required">
                                    <label>{{ __('app.slug') }}</label>
                                    <input type="text" class="form-control" placeholder=""
                                           name="{{ $isEditing ? '' : 'slug' }}"
                                           value="{{ $isEditing ? $data->slug : '' }}"
                                        {{ $isEditing ? 'disabled' : '' }}>
                                    <small class="form-text text-muted">
                                        {{ env('HAPI_URL') . '/merchant/' }}<span id="slug-sample">{{ $isEditing ? $data->slug : '' }}</span>
                                    </small>
                                </div>
                                <div class="form-group required">
                                    <label>Name</label>
                                    <input type="text" class="form-control" placeholder="" name="name"
                                           value="{{ $isEditing ? $data->name : '' }}">
                                    <i class="form-group__bar"></i>
                                </div>
                                <div class="form-group ">
                                    <label>Description</label>
                                    <input type="text" class="form-control" placeholder="" name="description"
                                           value="{{ $isEditing ? $data->description : '' }}">
                                    <i class="form-group__bar"></i>
                                </div>
                                <div class="form-group required">
                                    <label>Category</label>
                                    <select class="select form-control" name="category">
                                        @foreach(\App\Enums\MerchantCategory::cases() as $category)
                                            <option
                                                {{ $isEditing && $data->category === $category->value ? 'selected' : '' }} value="{{ $category->value }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Address</label>
                                    <input type="text" class="form-control" placeholder="" name="address"
                                           value="{{ $isEditing ? $data->address : '' }}">
                                    <i class="form-group__bar"></i>
                                </div>
                                <div class="form-group required">
                                    <label>Phone Number</label>
                                    <input type="text" class="form-control" placeholder="" name="phone_number"
                                           value="{{ $isEditing ? $data->phone_number : '' }}">
                                    <i class="form-group__bar"></i>
                                </div>
                                <div class="form-group ">
                                    <label>Whatsapp 1</label>
                                    <input type="text" class="form-control" placeholder=""
                                           name="primary_whatsapp_number"
                                           value="{{ $isEditing ? $data->primary_whatsapp_number : '' }}">
                                    <i class="form-group__bar"></i>
                                </div>
                                <div class="form-group ">
                                    <label>Whatsapp 2</label>
                                    <input type="text" class="form-control" placeholder=""
                                           name="secondary_whatsapp_number"
                                           value="{{ $isEditing ? $data->secondary_whatsapp_number : '' }}">
                                    <i class="form-group__bar"></i>
                                </div>
                                <div class="form-group ">
                                    <label>Bank Name</label>
                                    <input type="text" class="form-control" placeholder="" name="bank_name"
                                           value="{{ $isEditing ? $data->bank_name : '' }}">
                                    <i class="form-group__bar"></i>
                                </div>
                                <div class="form-group ">
                                    <label>Bank Account Holder</label>
                                    <input type="text" class="form-control" placeholder="" name="bank_account_holder"
                                           value="{{ $isEditing ? $data->bank_account_holder : '' }}">
                                    <i class="form-group__bar"></i>
                                </div>
                                <div class="form-group ">
                                    <label>Bank Account Number</label>
                                    <input type="text" class="form-control" placeholder="" name="bank_account_number"
                                           value="{{ $isEditing ? $data->bank_account_number : '' }}">
                                    <i class="form-group__bar"></i>
                                </div>
                                <div class="form-group ">
                                    <label>QRIS Link</label>
                                    <input type="text" class="form-control" placeholder="" name="qris_link"
                                           value="{{ $isEditing ? $data->qris_link : '' }}">
                                    <i class="form-group__bar"></i>
                                </div>
                                <div class="form-group required">
                                    <label>Status</label>
                                    <select class="select form-control" name="status">
                                        @foreach(\App\Enums\MerchantStatus::cases() as $item)
                                            <option
                                                {{ $isEditing && $data->status === $item->value ? 'selected' : '' }} value="{{ $item->value }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group required">
                                    <label>Logo</label>
                                    <div class="app-image_picker" data-name="logo_path"
                                         data-placeholder-image="{{ $isEditing ? $data->logo_path : '' }}"></div>
                                </div>
                                <div class="form-group required">
                                    <label>{{ __('app.banner_image') }}</label>
                                    <div class="app-image_picker" data-name="banner_image"
                                         data-placeholder-image="{{ $isEditing ? $data->banner_image : '' }}"></div>
                                </div>
                                <div class="form-group">
                                    <label>Location</label>
                                    <div id="map" style="width: 100%; height: 360px;"></div>
                                    <button class="btn-primary btn-block" type="button" id="show-my-location">Use my
                                        current location
                                    </button>
                                    <input type="hidden" name="latitude"
                                           value="{{ $isEditing ? $data->latitude : '' }}">
                                    <input type="hidden" name="longitude"
                                           value="{{ $isEditing ? $data->longitude : '' }}">
                                </div>
                                @if (!auth()->user()->isOwner())
                                    <div class="form-group ">
                                        <label>Owner</label>
                                        <select class="select form-control" name="owner_id">
                                            <option disabled selected>Select Owner</option>
                                            @foreach(\App\Models\User::all() as $user)
                                                <option
                                                    {{ $isEditing && $user->id === $data->owner_id ? 'selected' : '' }} value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <input type="hidden" name="owner_id" value="{{ auth()->user()->id }}">
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="profile-2" role="tabpanel">
                        <div class="listview listview--bordered">
                            @for($i = 1; $i <= 7; $i++)
                                <div class="listview__item px-0">
                                    <div class="listview__content">
                                        <div class="row">
                                            <div class="col flex align-content-center d-flex">
                                                <p class="font-weight-bold mb-0"
                                                   style="font-size: 16px">{{ \App\Enums\DayNameEnum::from($i)->name }}</p>
                                            </div>
                                            <div class="col day-switch-wrapper">
                                                Tutup
                                                <div class="toggle-switch mx-3">
                                                    @php $opening = \App\Models\Merchant::openingAtDay($i, $data->operating_hours ?? []) @endphp
                                                    <input type="checkbox" class="toggle-switch__checkbox days"
                                                           name="operating_hours[{{ $i }}]" {{ $opening['isOpen'] ? 'checked' : '' }}>
                                                    <i class="toggle-switch__helper"></i>
                                                </div>
                                                Buka
                                            </div>
                                        </div>
                                        <div class="time-range mt-3" {{ $opening['isOpen'] ? '' : 'hidden' }}>
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-group mb-0">
                                                        <input type="text" class="form-control input-mask start"
                                                               placeholder="Opening Hour (00:00)" data-mask="00:00"
                                                               name="operating_hours[{{ $i }}][]"
                                                               value="{{ $opening['hours'][0] ?? ''}}">
                                                        <i class="form-group__bar"></i>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="form-group mb-0">
                                                        <input type="text" class="form-control input-mask end"
                                                               placeholder="Closing Hour (23:00)" data-mask="00:00"
                                                               name="operating_hours[{{ $i }}][]"
                                                               value="{{ $opening['hours'][1] ?? ''}}">
                                                        <i class="form-group__bar"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-4 pb-4">
                <button type="submit"
                        class="btn btn-outline-primary btn--submit">{{ $isEditing ? 'Update' : 'Submit' }}</button>
                <a href="{{ $baseUrl }}" class="btn btn-outline-dark btn--back">Go Back</a>
            </div>
        </form>
    </div>
@endsection

@section('styles')
    <style>
        .day-switch-wrapper {
            align-items: center;
            justify-content: flex-end;
            display: flex;
        }

        .listview:not(.listview--inverse).listview--bordered .listview__item + .listview__item {
            border: none;
        }

        .start, .end {
            text-align: center;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('js/vendor/jquery.slugger.js') }}"></script>
    <script type="text/javascript"
            src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>
    <!-- prettier-ignore -->
    <script>(g => {
            var h, a, k, p = "The Google Maps JavaScript API", c = "google", l = "importLibrary", q = "__ib__",
                m = document, b = window;
            b = b[c] || (b[c] = {});
            var d = b.maps || (b.maps = {}), r = new Set, e = new URLSearchParams,
                u = () => h || (h = new Promise(async (f, n) => {
                    await (a = m.createElement("script"));
                    e.set("libraries", [...r] + "");
                    for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]);
                    e.set("callback", c + ".maps." + q);
                    a.src = `https://maps.${c}apis.com/maps/api/js?` + e;
                    d[q] = f;
                    a.onerror = () => h = n(Error(p + " could not load."));
                    a.nonce = m.querySelector("script[nonce]")?.nonce || "";
                    m.head.append(a)
                }));
            d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n))
        })
        ({key: "AIzaSyAvVwinFdSBpMosAAO4WR9YWm6R_Orpxho", v: "weekly"});</script>

    <script>
        let isEditing = $('.base-form').data('is-editing') === 1;
        let latitude = $('[name="latitude"]');
        let longitude = $('[name="longitude"]');

        let defaultCoordinate = {
            lat: -0.8651945,
            lng: 134.0662722,
        };

        if (isEditing) {
            defaultCoordinate = {lat: parseFloat(latitude.val()), lng: parseFloat(longitude.val())};
        } else {
            longitude.val(defaultCoordinate.lng);
            latitude.val(defaultCoordinate.lat);
        }

        function displayCurrentLocation(map, marker) {
            if (!navigator.geolocation) return;

            navigator.geolocation.getCurrentPosition((position) => {
                defaultCoordinate = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                };
                marker.position = defaultCoordinate;
                map.setCenter(defaultCoordinate);

                latitude.val(position.coords.latitude);
                longitude.val(position.coords.longitude);
            });
        }

        async function loadMap() {
            // Request needed libraries.
            const {Map} = await google.maps.importLibrary("maps");
            const {AdvancedMarkerElement} = await google.maps.importLibrary("marker");

            // The map
            const map = new Map(document.getElementById("map"), {
                zoom: 18,
                center: defaultCoordinate,
                mapId: "merchant_location_map",
                streetViewControl: false,
                mapTypeControl: false
            });

            // The marker
            const marker = new AdvancedMarkerElement({
                map: map,
                position: defaultCoordinate,
                gmpDraggable: true
            });

            if (!isEditing && navigator.geolocation) {
                displayCurrentLocation(map, marker);
            }

            marker.addListener("dragend", (event) => {
                latitude.val(marker.position.lat);
                longitude.val(marker.position.lng);

                defaultCoordinate = {
                    lat: marker.position.lat,
                    lng: marker.position.lng,
                }
            });

            $('#show-my-location').click(function () {
                displayCurrentLocation(map, marker);
            });
        }


        $(document).ready(function () {
            Base.addEdit();
            loadMap();

            $('.days').change(function () {
                const isChecked = $(this).is(':checked');
                $(this).closest('.listview__content').find('.time-range').attr('hidden', !isChecked);

                if (!isChecked) {
                    $(this).closest('.listview__content').find('.start').val('');
                    $(this).closest('.listview__content').find('.end').val('');
                }
            });

            $('.timepicker.start').change(function () {
                console.log($(this).val());
            });

            $('[name=slug]').on('change keyup', function () {
                let value = $(this).val();
                let validValue = value.replaceAll(' ', '-').toLowerCase()

                $(this).val(validValue);
                $('#slug-sample').text(validValue);
            });

            $('input[name="name"]').slugger({
                bindToEvent: 'keyup', // The event to bind to.
                target: '[name=slug]',
                separator: '-',
                convertToLowerCase: true,
                isUrlFriendly: true,
                afterConvert: function (self) {
                    $('[name=slug]').trigger('change')
                },
            });
        });
    </script>
@endsection
