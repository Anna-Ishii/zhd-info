<!-- モーダル：単体PDFファイルモーダル -->
@foreach($message_list as $message)
    <div class="modal fade" id="singleFileModal{{ $message->id }}" tabindex="-1" role="dialog" aria-labelledby="singleFileModalLabel{{ $message->id }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="singleFileModalLabel{{ $message->id }}">添付ファイル　全{{ $message->single_file_count }}件</h4>
                </div>
                <div class="modal-body modal-body-scrollable" id="singleFiles" style="max-height: 300px; overflow-y: auto;">
                    @foreach($message->single_files as $file)
                        <p><a href="{{ asset($file['file_url']) }}" target="_blank">{{ $file['file_name'] }}</a></p>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endforeach