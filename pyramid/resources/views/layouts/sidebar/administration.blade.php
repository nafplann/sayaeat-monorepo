@use('App\Enums\PermissionsEnum')

@canany([PermissionsEnum::BROWSE_AUDIT_LOGS->value, PermissionsEnum::READ_MESSAGE_TEMPLATE->value, PermissionsEnum::READ_USERS->value, PermissionsEnum::READ_ROLES->value, PermissionsEnum::READ_SETTINGS->value])
    <li class="categories">Administration</li>

    @can(PermissionsEnum::BROWSE_AUDIT_LOGS->value)
        <li class="{{ request()->is('manage/audit-logs*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/audit-logs') }}"><i class="zmdi zmdi-eye"></i> Audit Logs</a>
        </li>
    @endcan

    @can(PermissionsEnum::READ_MESSAGE_TEMPLATE->value)
        <li class="{{ request()->is('manage/message-template*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/message-template') }}"><i class="zmdi zmdi-whatsapp"></i> Template</a>
        </li>
    @endcan

    @can(PermissionsEnum::READ_ROLES->value)
        <li class="{{ request()->is('manage/roles*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/roles') }}"><i class="zmdi zmdi-key"></i> Roles</a>
        </li>
    @endcan

    @can(PermissionsEnum::READ_USERS->value)
        <li class="{{ request()->is('manage/users*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/users') }}"><i class="zmdi zmdi-accounts"></i> Users</a>
        </li>
    @endcan

    @can(PermissionsEnum::READ_SETTINGS->value)
        <li class="{{ request()->is('manage/settings*') ? 'navigation__active' : '' }}">
            <a href="{{ url('manage/settings') }}"><i class="zmdi zmdi-settings"></i> Settings</a>
        </li>
    @endcan
    {{--    <li class="{{ request()->is('manage/settings*') ? 'navigation__active' : '' }}">--}}
    {{--        <a href="{{ url('manage/settings') }}"><i class="zmdi zmdi-settings"></i> Settings</a>--}}
    {{--    </li>--}}
@endcanany
