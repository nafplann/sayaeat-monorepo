@extends('auth.app')

@section('title', 'Sign In')

@section('content')
    <div class="wrapper">
        <div class="login">
            <!-- Login -->
            <div class="login__block active shadow" id="l-login">
                <div class="login__block__header">
                    <i class="zmdi zmdi-account-circle"></i>
                    Hi there! Please Sign in


                    {{-- <div class="actions actions--inverse login__block__actions">
                        <div class="dropdown">
                            <i data-toggle="dropdown" class="zmdi zmdi-more-vert actions__item"></i>

                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" data-ma-action="login-switch" data-ma-target="#l-register" href="">Create an account</a>
                                <a class="dropdown-item" data-ma-action="login-switch" data-ma-target="#l-forget-password" href="">Forgot password?</a>
                            </div>
                        </div>
                    </div> --}}
                </div>

                <div class="login__block__body">
                    <form id="login-form" method="POST" action="{{ route('auth.login-request') }}">
                        <div class="form-group form-group--float form-group--centered">
                            <input type="email" class="form-control" name="email">
                            <label>Email</label>
                            <i class="form-group__bar"></i>
                        </div>

                        <div class="form-group form-group--float form-group--centered">
                            <input type="password" class="form-control" name="password">
                            <label>Password</label>
                            <i class="form-group__bar"></i>
                        </div>

                        <div class="form-group mb-2">
                            {!! htmlFormSnippet() !!}
                        </div>

                        @csrf
                        <button class="btn btn--icon login__block__btn btn--submit"><i
                                class="zmdi zmdi-long-arrow-right"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .login__block__header > img {
            width: initial;
            height: initial;
            border-radius: initial;
            box-shadow: none;
            max-width: 200px;
            margin: auto;
        }

        .btn--submit .spinner-border-sm {
            margin-bottom: 4px;
        }

        #login-form .form-control {
            color: #495057;
        }

        #login-form label {
            color: #868e96;
        }
    </style>
@endsection

@section('scripts')
    {!! htmlScriptTagJsApi() !!}
    <script>
        $(document).ready(function () {
            Auth.login();
        });
    </script>
@endsection
