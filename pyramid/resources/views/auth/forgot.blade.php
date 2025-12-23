@extends('shared.auth.app')

@section('title', 'Forgot Password')

@section('content')
    <section class="bg-transparent height-100vh d-flex align-items-center page-section-ptb login">
        <div class="container">
            <div class="row no-gutters justify-content-center">
                <div class="col-lg-5 col-md-6 white-bg shadow-lg">
                    <div class="login-fancy pb-40 clearfix">
                        <figure class="app-logo mb-4 text-center">
                            <img src="{{ url('images/logo.png') }}" style="width: 250px;">
                        </figure>
                        <form id="forgot-form" method="POST" action="/forgot">
                            <div class="section-field mb-20">
                                <input id="email" class="web form-control" type="email" placeholder="Enter your registered email address" name="email" required>
                            </div>
                            <div class="d-flex justify-content-center mb-3">
                                {!! ReCaptcha::htmlFormSnippet() !!}
                            </div>
                            <button class="button btn-block btn--submit" type="submit" disabled>
                                <span>Request Password Reset</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            Auth.forgot();
        });

        function captcha_is_valid() {
            $('.btn--submit').attr('disabled', false);
        }
    </script>
@endsection
