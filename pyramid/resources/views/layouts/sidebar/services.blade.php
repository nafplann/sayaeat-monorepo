@use('App\Enums\PermissionsEnum')

@canany([
    PermissionsEnum::BROWSE_SHOPPING_ORDERS->value,
    PermissionsEnum::BROWSE_KIRIM_AJA_ORDERS->value,
    PermissionsEnum::BROWSE_MAKAN_AJA_ORDERS->value,
    PermissionsEnum::BROWSE_MARKET_AJA_ORDERS->value,
])
    <li class="categories">Services</li>

    @can(PermissionsEnum::BROWSE_SHOPPING_ORDERS->value)
        <li class="{{ request()->is('manage/shopping-orders*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/shopping-orders') }}"><i class="zmdi zmdi-shopping-cart"></i> Belanja-Aja</a>
        </li>
    @endcan

    @can(PermissionsEnum::BROWSE_KIRIM_AJA_ORDERS->value)
        <li class="{{ request()->is('manage/kirim-aja*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/kirim-aja') }}"><i class="zmdi zmdi-archive"></i> Kirim-Aja</a>
        </li>
    @endcan

    @can(PermissionsEnum::BROWSE_MAKAN_AJA_ORDERS->value)
        <li class="{{ request()->is('manage/makan-aja*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/makan-aja') }}"><i class="zmdi zmdi-local-dining"></i> Makan-Aja</a>
        </li>
    @endcan

    @can(PermissionsEnum::BROWSE_MARKET_AJA_ORDERS->value)
        <li class="{{ request()->is('manage/market-aja*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/market-aja') }}"><i class="zmdi zmdi-local-mall"></i> Market-Aja</a>
        </li>
    @endcan

@endcanany
