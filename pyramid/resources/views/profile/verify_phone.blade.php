<form action="{{ url('manage/profile/verify-phone') }}" method="POST" enctype="multipart/form-data">
    <p class="text-center">We have send a verification code to your mobile phone number. Please enter the code below to continue verification process.</p>
    <div class="form-group required">
        <label>Code</label>
        <input type="text" class="form-control" name="code">
        <i class="form-group__bar"></i>
    </div>
    <div class="text-right">
        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-link btn--submit">Save</button>
    </div>
</form>