@props([
    'label', // 表示ラベル (例: 'カテゴリ', '状態')
    'name', // フォームのname属性 (例: 'category', 'status')
    'options', // 選択肢のコレクションまたは配列
    'valueKey' => 'id', // 値として使うキー名 (デフォルトは 'id')
    'nameKey' => 'name', // 表示名として使うキー名 (デフォルトは 'name')
])

@push('scripts')
    @once
        <script src="{{ asset('/js/admin/common/selectCheckbox.js') }}?date={{ date('Ymd') }}" defer></script>
    @endonce
@endpush

@php
    $selectedValues = request()->input($name, []);
    $selectedNames = [];

    foreach ($options as $option) {
        $isObject = is_object($option);
        $optionValue = $isObject ? $option->{$valueKey} : $option[$valueKey];

        if (in_array($optionValue, $selectedValues)) {
            $selectedNames[] =
                $isObject && method_exists($option, $nameKey)
                    ? $option->{$nameKey}()
                    : ($isObject
                        ? $option->{$nameKey}
                        : $option[$nameKey]);
        }
    }
    $triggerText = !empty($selectedNames) ? implode(', ', $selectedNames) : '指定なし';
@endphp

<div class="field">
    <div class="label">{{ $label }}</div>
    <div class="control">
        <div class="custom-select-checkbox__wrap">
            <div class="custom-select-checkbox">
                <span class="custom-select-checkbox__trigger">{{ $triggerText }}</span>
                <div class="custom-options">
                    <label class="custom-option custom-checkbox custom-checkbox-square">
                        <input type="checkbox" name="{{ $name }}[]" value="全て選択/選択解除">
                        <span class="checkmark"></span>
                        全て選択/選択解除
                    </label>
                    @foreach ($options as $option)
                        @php
                            $isObject = is_object($option);
                            $optionValue = $isObject ? $option->{$valueKey} : $option[$valueKey];
                            $optionName =
                                $isObject && method_exists($option, $nameKey)
                                    ? $option->{$nameKey}()
                                    : ($isObject
                                        ? $option->{$nameKey}
                                        : $option[$nameKey]);
                        @endphp
                        <label class="custom-option custom-checkbox custom-checkbox-square">
                            <input type="checkbox" name="{{ $name }}[]" value="{{ $optionValue }}"
                                {{ in_array($optionValue, $selectedValues) ? 'checked' : '' }}>
                            <span class="checkmark"></span>
                            {{ $optionName }}
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
