@extends('shared.auth.app')

@section('title', 'Recover Lost Password')

@section('content')
    <section class="bg-transparent height-100vh d-flex align-items-center page-section-ptb login">
        <div class="container">
            <div class="row no-gutters justify-content-center">
                <div class="col-lg-5 col-md-6 white-bg shadow-lg">
                    <div class="login-fancy pb-40 clearfix">
                        <figure class="app-logo mb-4 text-center">
                            <img src="{{ url('images/logo.png') }}" style="width: 250px;">
                        </figure>
                        <form id="reset-form" method="POST" action="/reset">
                            <div class="section-field mb-20">
                                <input id="email" class="web form-control" type="email" placeholder="Enter your registered email address" name="email" value="{{ $email }}" readonly>
                            </div>
                            <div class="section-field mb-20">
                                <input class="web form-control" type="password" placeholder="Enter New Password" name="password" required>
                            </div>
                            <div class="section-field mb-20">
                                <input class="web form-control" type="password" placeholder="Confirm New Password" name="password_confirmation" required>
                            </div>
                            <button class="button btn-block btn--submit" type="submit" disabled>
                                <input type="hidden" name="token" value="{{ $token }}">
                                <input type="hidden" name="email" value="{{ $email }}">
                                <span>Request Password Reset</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <style>
        .form-container, 
        .overlay-container,
        .overlay {
            transition: none;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            Auth.reset();
        });
    </script>
@endsection
