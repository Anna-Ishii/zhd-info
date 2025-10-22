@props([
    'name', // フォームのname属性 (例: 'brand', 'label')
    'label', // 表示ラベル (例: '業態', 'ラベル')
    'options', // 選択肢のコレクションまたは配列
    'valueKey' => 'id', // 値として使うキー名 (デフォルトは 'id')
    'nameKey' => 'name', // 表示名として使うキー名 (デフォルトは 'name')
    'base64Value' => false, // 値をbase64エンコードするかどうか (デフォルトは false)
])

@push('scripts')
    @once
        <script src="{{ asset('/js/admin/common/selectbox.js') }}?date={{ date('Ymd') }}" defer></script>
    @endonce
@endpush

@php
    $selectedValue = request()->input($name, '');
    $selectedLabel = '指定なし';

    // 渡されたオプションの中から、現在選択されている値に一致するものを探し、表示ラベルを取得
    foreach ($options as $option) {
        $isObject = is_object($option);
        $optionValue = $isObject ? $option->{$valueKey} : $option[$valueKey];
        $optionName = $isObject ? $option->{$nameKey} : $option[$nameKey];

        // 比較用の値を、必要に応じてbase64エンコード
        $comparableValue = $base64Value ? base64_encode($optionValue) : $optionValue;

        if ($selectedValue == $comparableValue) {
            $selectedLabel = $optionName;
            break;
        }
    }
@endphp

<div class="field">
    <div class="label">{{ $label }}</div>
    <div class="control">
        <div class="custom-select__wrap">
            <div class="custom-select">
                <span class="custom-select__trigger">{{ $selectedLabel }}</span>
                <div class="custom-options">
                    <span class="custom-option" data-value="">指定なし</span>

                    @foreach ($options as $option)
                        @php
                            $isObject = is_object($option);
                            $optionValue = $isObject ? $option->{$valueKey} : $option[$valueKey];
                            $optionName = $isObject ? $option->{$nameKey} : $option[$nameKey];

                            $finalValue = $base64Value ? base64_encode($optionValue) : $optionValue;
                        @endphp
                        <span class="custom-option" data-value="{{ $finalValue }}">
                            {{ $optionName }}
                        </span>
                    @endforeach
                </div>
            </div>
            <input type="hidden" name="{{ $name }}" value="{{ $selectedValue }}">
        </div>
    </div>
</div>
