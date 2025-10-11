@use('App\Enums\PermissionsEnum')

@canany([
    PermissionsEnum::READ_DASHBOARD->value,
    PermissionsEnum::READ_BELANJA_AJA_DASHBOARD->value,
])
    <li class="categories">General</li>

    @canany([PermissionsEnum::READ_DASHBOARD->value, PermissionsEnum::READ_BELANJA_AJA_DASHBOARD->value])
        <li class="{{ request()->is('manage/dashboard') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/dashboard') }}"><i class="zmdi zmdi-home"></i> Dashboard</a>
        </li>
    @endcanany

@endcanany
