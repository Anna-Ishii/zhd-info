@extends('layouts.parent')

@push('css')
    <link href="{{ asset('/css/detail.css') }}?date={{ date('Ymd') }}" rel="stylesheet">
@endpush

@section('title', '業務連絡')
    @section('previous_page')
        <a href="{{{ session('current_url', route('message.index')) }}}">業務連絡</a>
    @endsection

    @section('content')
        <header class="header header--detail">
            <section class="header__inner flex">
                <div class="header__titleBox flex">
                    <section class="header__title">
                        <h1 class="txtBold txtBlue">{{ $message->title }}</h1>
                        <time datetime="{{ $message->formatted_start_datetime }}" class="mr8 txtBold">{{ $message->formatted_start_datetime }}</time>
                    </section>
                </div>
                <ul class="header__menu flex">
                    <li>
                        <button type="button" class="btnPrint">
                            <img src="{{ asset('img/icon_print.svg') }}" alt="印刷する">
                        </button>
                    </li>
                </ul>
            </section>
        </header>

    {{-- <main class="main message">
        <div class="main__supplement mb26">
            <p>{{ $message->detail }}</p>
            <p class="test"></p>
        </div>
        <div class="main__inner">
            <div class="main__fileView">
                <!-- ?file=の後を表示したいPDFのパスに変更してください -->
                <iframe src="/js/pdfjs/web/viewer.html?file={{ asset($message->content_url) }}"></iframe>
            </div>
        </div>
    </main> --}}

    <main class="main manual">
        <input id="manual_id" value="{{$message->id}}" hidden>
        <div class="main__inner">
            <div class="main__supplement main__box--single thumb_parents flex">
                {{-- PDF --}}
                <div class="main__supplement__detail">

                    @if(isset($message->main_file))
                        <div class="pdf-container all" data-url="{{ asset($message->main_file['file_url']) }}"></div>
                    @else
                        <div class="pdf-container all" data-url="{{ asset($message->content_url) }}"></div>
                    @endif

                </div>
            </div>
        </div>
    </main>

    @include('common.footer')

    <!-- pdfjs -->
    <script src="{{ asset('/js/oldjslibrary/pdfjs-2.10.377-dist/build/pdf.js') }}"></script>
    <script src="{{ asset('/js/detail.js') }}?date={{ date('Ymd') }}" defer></script>
@endsection
