<!-- モーダル -->
@foreach ($messages as $message)
<div class="modal" id="singleFileModal{{ $message->id }}" data-modal-target="singleFileModal{{ $message->id }}" style="display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
        <div class="modal-dialog" style=" position: relative; margin: auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 400px; background-color: #fff; top: 50%; transform: translateY(-50%);">
            <div class="modal-content">
                <div class="modal-header" style="padding: 10px; display: flex; justify-content: space-between; align-items: center;">
                    <h4 class="modal-title">添付ファイル　全{{ $message->single_file_count }}件</h4>
                    <span class="close" data-modal-target="singleFileModal{{ $message->id }}" style="font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
                </div>
                <div class="modal-body modal-body-scrollable" style="padding: 10px; max-height: 300px; overflow-y: auto;">
                    @foreach ($message->single_files as $file)
                        <p><a href="{{ asset($file['file_url']) }}" target="_blank">{{ $file['file_name'] }}</a></p>
                    @endforeach
                </div>
                <div class="modal-footer" style="padding: 10px;">
                    <button class="closeBtn" data-modal-target="singleFileModal{{ $message->id }}">閉じる</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
