@use('App\Enums\PermissionsEnum')

@canany([
    PermissionsEnum::READ_DASHBOARD->value,
])
    <li class="categories">General</li>

    @canany([PermissionsEnum::READ_DASHBOARD->value])
        <li class="{{ request()->is('manage/dashboard') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/dashboard') }}"><i class="zmdi zmdi-home"></i> Dashboard</a>
        </li>
    @endcanany

@endcanany

@canany([
    PermissionsEnum::BROWSE_MERCHANTS->value,
    PermissionsEnum::BROWSE_MENUS->value,
    PermissionsEnum::BROWSE_ORDERS->value,
])
    <li class="categories">MAKAN-AJA</li>

    @can(PermissionsEnum::BROWSE_MERCHANTS->value)
        <li class="{{ request()->is('manage/merchants*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/merchants') }}"><i class="zmdi zmdi-local-store"></i> {{ __('app.merchants') }}</a>
        </li>
    @endcan

    @canany([PermissionsEnum::BROWSE_MENUS->value, PermissionsEnum::BROWSE_ADDON_CATEGORIES->value, PermissionsEnum::BROWSE_MENU_CATEGORIES->value])
        <li class="navigation__sub {{ request()->is('manage/menus*') || request()->is('manage/menu-addon-categories*') || request()->is('manage/menu-categories*') ? 'navigation__sub--active navigation__sub--toggled' : ''}}">
            <a href=""><i class="zmdi zmdi-collection-text"></i> {{ __('app.menus') }}</a>
            <ul>
                <li class="{{ request()->is('manage/menu-addon-categories*') ? 'navigation__active' : '' }}">
                    <a href="{{ url('manage/menu-addon-categories') }}">{{ __('app.addons') }}</a>
                </li>
                <li class="{{ request()->is('manage/menu-categories*') ? 'navigation__active' : '' }}">
                    <a href="{{ url('manage/menu-categories') }}">{{ __('app.categories') }}</a>
                </li>
                <li class="{{ request()->is('manage/menus*') ? 'navigation__active' : '' }}">
                    <a href="{{ url('manage/menus') }}">{{ __('app.menu_list') }}</a>
                </li>
            </ul>
        </li>
    @endcan

    @can(PermissionsEnum::BROWSE_ORDERS->value)
        <li class="{{ request()->is('manage/orders*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/orders') }}"><i class="zmdi zmdi-shopping-basket"></i> {{ __('app.orders') }}</a>
        </li>
    @endcan
@endcanany

@canany([
    PermissionsEnum::BROWSE_STORES->value,
    PermissionsEnum::BROWSE_PRODUCTS->value,
    PermissionsEnum::BROWSE_STORE_ORDERS->value,
    PermissionsEnum::BROWSE_PRODUCT_DISCOUNTS->value,
])
    <li class="categories">MARKET-AJA</li>

    @can(PermissionsEnum::BROWSE_STORES->value)
        <li class="{{ request()->is('manage/stores*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/stores') }}"><i class="zmdi zmdi-local-store"></i> {{ __('app.stores') }}</a>
        </li>
    @endcan

    @canany([PermissionsEnum::BROWSE_PRODUCTS->value, PermissionsEnum::BROWSE_PRODUCT_CATEGORIES->value, PermissionsEnum::BROWSE_PRODUCT_DISCOUNTS->value])
        <li class="navigation__sub {{ request()->is('manage/products*') || request()->is('manage/product-categories*') ? 'navigation__sub--active navigation__sub--toggled' : ''}}">
            <a href=""><i class="zmdi zmdi-collection-text"></i> {{ __('app.products') }}</a>
            <ul>
                <li class="{{ request()->is('manage/product-categories*') ? 'navigation__active' : '' }}">
                    <a href="{{ url('manage/product-categories') }}">{{ __('app.categories') }}</a>
                </li>
                <li class="{{ request()->is('manage/products*') ? 'navigation__active' : '' }}">
                    <a href="{{ url('manage/products') }}">{{ __('app.product_list') }}</a>
                </li>
                <li class="{{ request()->is('manage/product-discounts*') ? 'navigation__active' : '' }}">
                    <a href="{{ url('manage/product-discounts') }}">{{ __('app.discounts') }}</a>
                </li>
            </ul>
        </li>
    @endcan

    @can(PermissionsEnum::BROWSE_STORE_ORDERS->value)
        <li class="{{ request()->is('manage/store-orders*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/store-orders') }}"><i class="zmdi zmdi-local-mall"></i> {{ __('app.orders') }}</a>
        </li>
    @endcan
@endcanany
