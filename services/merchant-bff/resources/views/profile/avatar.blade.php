<form id="form-avatar" method="POST" action="/manage/profile/avatar" enctype="multipart/form-data">
    <!-- Wrap the image or canvas element with a block element (container) -->
    <div class="form-group">
        <label>Select Image</label>
        <input type="file" accept="image/*" class="form-control" name="thefile">
        <input type="hidden" name="result">
        <i class="form-group__bar"></i>
        {{ csrf_field() }}
    </div>
    
    <div>
        <img id="cropper-img" src="{{ Auth::user()->avatar() }}">
    </div>
    
    <div class="text-center">
        <div class="btn-group">
            <button type="button" id="rotate-left" class="btn btn-light btn--icon-text">
                <i class="zmdi zmdi-rotate-left"></i> Rotate
            </button>
            
            <button type="button" id="rotate-right" class="btn btn-light btn--icon-text">
                <i class="zmdi zmdi-rotate-right"></i> Rotate
            </button>
            
            <button type="button" class="btn btn-danger btn--icon-text" data-dismiss="modal">
                <i class="zmdi zmdi-close"></i> Cancel
            </button>
            
            <button type="submit" class="btn btn-success btn--icon-text btn--submit">
                <i class="zmdi zmdi-save"></i> Save
            </button>
        </div>
    </div>
</form>