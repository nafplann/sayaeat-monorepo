@extends('shared.auth.app')

@section('title', 'Register New Account')

@section('content')
    <section class="bg-transparent height-100vh d-flex align-items-center page-section-ptb login">
        <div class="container">
            <div class="row no-gutters justify-content-center">
                <div class="col-md-8 white-bg shadow-lg">
                    <div class="login-fancy pb-40 clearfix">
                        {{-- <figure class="app-logo mb-4 text-center">
                            <img src="{{ url('images/logo.png') }}" style="width: 250px;">
                        </figure>
                        <form id="login-form" method="POST" action="/login">
                            <div class="section-field mb-20">
                                <label class="mb-10" for="email">Email Address</label>
                                <input id="email" class="web form-control" type="email" placeholder="Enter your email address" name="email" required>
                            </div>
                            <div class="section-field mb-20">
                                <label class="mb-10" for="password">Password</label>
                                <input id="password" class="Password form-control" type="password" placeholder="Enter your password" name="password" required>
                            </div>
                            <div class="section-field">
                                <div class="custom-control custom-checkbox mb-30">
                                    <input type="checkbox" class="custom-control-input" id="remember-me" name="remember" value="true">
                                    <label class="custom-control-label" for="remember-me">Remember
                                        me</label>
                                </div>
                            </div>
                            <button class="button btn-block btn--submit" type="submit">
                                <span>Sign In</span>
                            </button>
                        </form>
                        <p class="mt-20 mb-0 text-center">Don't have an account? <a href="{{ url('register') }}"> Create one here</a>
                        </p> --}}
                        <a href="{{ url('/') }}">
                            <figure class="app-logo mb-4 text-center">
                                <img src="{{ url('images/logo.png') }}" style="width: 250px;">
                            </figure>
                        </a>
                        <form id="signup-form" action="{{ url('register') }}" method="POST">
                            <div id="register-form" class="register-form">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="section-field">
                                            <label>First Name</label>
                                            <div class="field-widget">
                                                <input type="text" class="form-control" placeholder="Enter First Name" name="first_name">
                                            </div>
                                        </div>
                                        <div class="section-field">
                                            <label>Last Name</label>
                                            <div class="field-widget">
                                                <input type="text" class="form-control" placeholder="Enter Last Name" name="last_name">
                                            </div>
                                        </div>
                                        <div class="section-field">
                                            <label>Home Address</label>
                                            <div class="field-widget">
                                                <input type="text" class="form-control" placeholder="Enter Home Address" name="address">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="section-field">
                                            <label>Email</label>
                                            <div class="field-widget">
                                                <input class="email form-control" type="email" placeholder="Enter Email Address" name="email" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="section-field">
                                            <label>Password</label>
                                            <div class="field-widget">
                                                <input class="Password form-control" type="password" placeholder="Password" name="password" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="section-field">
                                            <label>Password Confirmation</label>
                                            <div class="field-widget">
                                                <input class="Password form-control" type="password" placeholder="Re-type Your Password" name="password_confirmation" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column align-items-center">
                                    <div class="section-field">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="agreement">
                                            <label class="custom-control-label" for="agreement">I agree to the <a target="_blank" href="{{ url('page/terms-of-service') }}">Terms of Service</a> & <a target="_blank" href="{{ url('page/privacy-policy') }}">Privacy Policy</a>.</label>
                                        </div>
                                    </div>
                                    <div class="">
                                        <button href="#" class="button mt-20 btn--submit" disabled>
                                            <span>Register an account</span>
                                            <i class="fa fa-check"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <style>
        button:disabled {
            background: #e0e0e0;
            color: #353535;
            border: 2px solid #e0e0e0;
        }
    </style>    
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            Auth.register();
        });
    </script>
@endsection
