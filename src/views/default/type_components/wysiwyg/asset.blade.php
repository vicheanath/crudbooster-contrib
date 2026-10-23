@push('head')
    {{-- Summernote Lite: the same editor and API, without the Bootstrap 3 dependency of the classic build. --}}
    <link rel="stylesheet" type="text/css" href="{{asset('vendor/crudbooster/assets/summernote-lite/summernote-lite.min.css')}}">
@endpush
@push('bottom')
    <script type="text/javascript" src="{{asset('vendor/crudbooster/assets/summernote-lite/summernote-lite.min.js')}}"></script>
@endpush
