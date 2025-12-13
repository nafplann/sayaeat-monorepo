
<header class="header">
    @if (Auth::user())
        <div class="navigation-trigger" data-ma-action="aside-open" data-ma-target=".sidebar">
            <div class="navigation-trigger__inner">
                <i class="navigation-trigger__line"></i>
                <i class="navigation-trigger__line"></i>
                <i class="navigation-trigger__line"></i>
            </div>
        </div>
    @endif

    <div class="header__logo hidden-sm-down">
        <h1>
            <a href="/">
                {{-- <img src="{{ url('images/logo.png') }}" alt="{{ config('app.name') }} Logo"> --}}
                {{-- <img src="https://myboommedia.com/qd.png" alt="{{ config('app.name') }} Logo"> --}}
                <strong>{{ config('app.name') }}</strong>
            </a>
        </h1>
    </div>

    @if (Auth::user())
        <ul class="top-nav">
            <li>
                <a href="{{ route('auth.logout') }}" data-toggle="tooltip" title="Logout">
                    <i class="zmdi zmdi-sign-in"></i>
                </a>
            </li>
        </ul>
    @endif
</header>
