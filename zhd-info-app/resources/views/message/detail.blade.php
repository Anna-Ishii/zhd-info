@extends('layouts.parent')

@push('css')
    <!-- detail.css -->
    <script>
        // IEの判定
        var isIE = /*@cc_on!@*/false || !!document.documentMode;

        if (!isIE) {
            // IEでない場合
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = "{{ asset('/css/detail.css') }}?date={{ date('Ymd') }}";
            document.head.appendChild(link);
        } else {
            // IEの場合
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = "{{ asset('/css/iecsslibrary/detail.css') }}?date={{ date('Ymd') }}";
            document.head.appendChild(link);
        }
    </script>

    <link href="{{ asset('/css/phase3/business-contact.css') }}?date={{ date('Ymd') }}" rel="stylesheet">
@endpush

@section('backUrl', route('message.index', ['search_period' => 'all']))
@section('title', '業務連絡')
    @section('previous_page')
        <a href="{{{ session('current_url', route('message.index')) }}}">業務連絡</a>
    @endsection

    @section('content')

    <main>
    <div class="business-contact business-contact__detail">
        <input id="manual_id" value="{{$message->id}}" hidden>
        <div class="business-contact__detail__head">
            <h2 class="business-contact__detail__head__ttl">{{ $message->title }}</h2>
            <button class="business-contact__detail__head__btn btn-print-message">
                <img src="{{ asset('img/icon_print.svg') }}" alt="印刷する">
                印刷
            </button>
        </div>
        <div class="business-contact__detail__content">
            <div class="main__supplement main__box--single thumb_parents flex">
                <div class="pdf-container" style="width:100%; height:80vh;">
                    <iframe id="pdfFrame" src="{{ asset($message->content_url . "#toolbar=0&navpanes=0") }}"
                        width="100%"
                        height="100%"
                        style="border:none;">
                    </iframe>
                </div>
            </div>
        </div>
        <div class="business-contact__recent">
        <h2 class="business-contact__recent__ttl">新着業務連絡</h2>
            <div class="swiper business-contact__recent__swiper">
                <div class="swiper-wrapper business-contact__recent__list">
                    @foreach($latest_messages as $latest_message)
                    <div class="swiper-slide">
                        <div href="#" class="business-contact__recent__item business-contact__item">
                            <div class="item__info">
                                <p class="item__ttl">{{ $latest_message->title }}</p>
                                <a href="{{ route('message.detail', $latest_message->id) }}" class="item__link">内容を確認する<img src="{{ asset('img/arrow_right.svg') }}" alt=""></a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- Add navigation arrows -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</div>
    </main>

    @include('common.footer')

    <!-- pdfjs -->
    <script>
        // IEの判定
        var isIE = /*@cc_on!@*/false || !!document.documentMode;

        if (!isIE) {
            // IEでない場合
            var script = document.createElement('script');
            script.src = "{{ asset('/js/oldjslibrary/pdfjs-2.10.377-dist/build/pdf.js') }}";
            document.body.appendChild(script);
        } else {
            // IEの場合
            var script = document.createElement('script');
            script.src = "{{ asset('/js/iejslibrary/pdfjs-2.3.200-dist/build/pdf.js') }}";
            script.onload = function() {
                pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('/js/iejslibrary/pdfjs-2.3.200-dist/build/pdf.worker.js') }}";
            };
            document.body.appendChild(script);
        }
    </script>
    <script src="{{ asset('/js/detail.js') }}?date={{ date('Ymd') }}" defer></script>
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="{{ asset('/js/businessContactSwiper.js')}}?date={{ date('Ymd') }}"></script>
@endsection
