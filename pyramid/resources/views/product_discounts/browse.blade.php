@extends('base.browse')

@section('scripts')
    <script>
        $(document).ready(function() {
            Base.index({
                allowDelete: true
            });
        });
    </script>
@endsection
