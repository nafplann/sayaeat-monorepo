@extends('layouts.app')

@section('title', $user->first_name . '\'s Profile')

@section('content')
    <div class="card new-contact">
        <div class="card-body no-padding">
            <div class="tab-container">
                <ul class="nav nav-tabs nav-fill" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#home-2" role="tab">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#profile-2" role="tab">Password</a>
                    </li>
                </ul>

                <div class="tab-content p-4">
                    <div class="tab-pane active fade show" id="home-2" role="tabpanel">
                        <form id="profile-form" action="{{ url('manage/profile/update/' . $user->id) }}" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <input type="text" class="form-control" placeholder="Enter First Name" name="first_name" value="{{ $user->first_name }}">
                                        <i class="form-group__bar"></i>
                                    </div>
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <input type="text" class="form-control" placeholder="Enter Last Name" name="last_name" value="{{ $user->last_name }}">
                                        <i class="form-group__bar"></i>
                                    </div>
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" class="form-control" placeholder="Enter Email Address" name="email" value="{{ $user->email }}" readonly>
                                        <i class="form-group__bar"></i>
                                    </div>
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" class="form-control" placeholder="Enter Phone" name="phone" value="{{ $user->phone }}">
                                        <i class="form-group__bar"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 text-center">
                                <button type="submit" class="btn btn-primary btn--submit">Update Profile</button>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="profile-2" role="tabpanel">
                        <form id="password-form" action="{{ url('manage/profile/update-password/' . $user->id) }}" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label>Old Password</label>
                                        <input type="password" class="form-control" placeholder="Enter Old Password" name="old_password">
                                        <i class="form-group__bar"></i>
                                    </div>
                                    <div class="form-group">
                                        <label>New Password</label>
                                        <input type="password" class="form-control" placeholder="Enter New Password" name="new_password">
                                        <i class="form-group__bar"></i>
                                    </div>
                                    <div class="form-group">
                                        <label>Password Confirmation</label>
                                        <input type="password" class="form-control" placeholder="Enter Password Again" name="new_password_confirmation">
                                        <i class="form-group__bar"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 text-center">
                                <button type="submit" class="btn btn-primary btn--submit">Update Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        #cropper-img {
            max-width: 100%;
            max-height: 450px;
            min-height: 450px;
        }

        .cropper-bg {
            min-width: 100%;
            min-height: 350px;
        }
    </style>
@endsection

@section('scripts')
    <script>Profile.index();</script>
@endsection
