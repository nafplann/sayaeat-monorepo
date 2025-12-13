@use('App\Enums\PermissionsEnum')

@canany([
    PermissionsEnum::BROWSE_CUSTOMERS->value,
    PermissionsEnum::BROWSE_DRIVERS->value,
    PermissionsEnum::BROWSE_COUPONS->value,
    PermissionsEnum::BROWSE_MERCHANTS->value,
    PermissionsEnum::BROWSE_MENUS->value,
    PermissionsEnum::BROWSE_ORDERS->value,
    PermissionsEnum::BROWSE_PROMOTIONS->value,
    PermissionsEnum::READ_ONGOING_ORDERS->value,
    PermissionsEnum::BROWSE_STORES->value,
    PermissionsEnum::BROWSE_PRODUCTS->value,
    PermissionsEnum::BROWSE_PRODUCT_DISCOUNTS->value,
])
    <li class="categories">Manage</li>

    @can(PermissionsEnum::BROWSE_CUSTOMERS->value)
        <li class="{{ request()->is('manage/customers*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/customers') }}"><i class="zmdi zmdi-accounts-list"></i> Customers</a>
        </li>
    @endcan

    @can(PermissionsEnum::BROWSE_DRIVERS->value)
        <li class="{{ request()->is('manage/drivers*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/drivers') }}"><i class="zmdi zmdi-bike"></i> Drivers</a>
        </li>
    @endcan

    @can(PermissionsEnum::BROWSE_COUPONS->value)
        <li class="{{ request()->is('manage/coupons*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/coupons') }}"><i class="zmdi zmdi-local-offer"></i> Coupons</a>
        </li>
    @endcan

    @can(PermissionsEnum::BROWSE_MERCHANTS->value)
        <li class="{{ request()->is('manage/merchants*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/merchants') }}"><i class="zmdi zmdi-local-store"></i> Merchants</a>
        </li>
    @endcan

    @canany([PermissionsEnum::BROWSE_MENUS->value, PermissionsEnum::BROWSE_ADDON_CATEGORIES->value, PermissionsEnum::BROWSE_MENU_CATEGORIES->value])
        <li class="navigation__sub {{ request()->is('manage/menus*') || request()->is('manage/menu-addon-categories*') || request()->is('manage/menu-categories*') ? 'navigation__sub--active navigation__sub--toggled' : ''}}">
            <a href=""><i class="zmdi zmdi-collection-text"></i> Menus</a>
            <ul>
                <li class="{{ request()->is('manage/menu-addon-categories*') ? 'navigation__active' : '' }}">
                    <a href="{{ url('manage/menu-addon-categories') }}">Addons</a>
                </li>
                <li class="{{ request()->is('manage/menu-categories*') ? 'navigation__active' : '' }}">
                    <a href="{{ url('manage/menu-categories') }}">Categories</a>
                </li>
                <li class="{{ request()->is('manage/menus*') ? 'navigation__active' : '' }}">
                    <a href="{{ url('manage/menus') }}">Menu List</a>
                </li>
            </ul>
        </li>
    @endcan

    @can(PermissionsEnum::BROWSE_ORDERS->value)
        <li class="{{ request()->is('manage/orders*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/orders') }}"><i class="zmdi zmdi-shopping-basket"></i> Orders</a>
        </li>
    @endcan

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

    @can(PermissionsEnum::BROWSE_PROMOTIONS->value)
        <li class="{{ request()->is('manage/promotions*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/promotions') }}"><i class="zmdi  zmdi-local-activity"></i> Promotions</a>
        </li>
    @endcan

    @can(PermissionsEnum::READ_ONGOING_ORDERS->value)
    <li class="{{ request()->is('manage/ongoing-orders*') ? 'navigation__active' : '' }}">
        <a href="{{ url('manage/ongoing-orders') }}"><i class="zmdi zmdi-tv-list"></i> Ongoing Orders</a>
    </li>
    @endcan
@endcanany
