@use('App\Enums\PermissionsEnum')

<aside class="sidebar sidebar--hidden">
    <div class="scrollbar-inner">

        <div class="user">
            <a href="/manage/profile/show/{{ Auth::user()->id }}" class="user__info">
                <img class="user__img" src="{{ asset('images/user_empty.png') }}" alt="">
                <div>
                    <div class="user__name">{{ Auth::user()->name }}</div>
                    <div class="user__email">{{ Auth::user()->email }}</div>
                </div>
            </a>
        </div>

        <ul class="navigation">
            @role('owner')
                @include('layouts.sidebar.owner')
            @else
                @include('layouts.sidebar.general')
                @include('layouts.sidebar.manage')
                @include('layouts.sidebar.services')
                @include('layouts.sidebar.administration')
            @endrole
        </ul>
    </div>
</aside>
