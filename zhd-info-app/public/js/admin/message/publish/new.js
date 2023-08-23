window.onbeforeunload = function(e) {
    if(inputCheck()) return;
    e.preventDefault();
    e.returnValue = "";
}

// 入力チェック
// 何か入力状態であれば、falseを返す
function inputCheck() {
    if($('input[name="title"]').val() != "") return false;
    if($('input[name="file"]').val() != "") return false;
    if($('input[name="category_id"]:checked').val() != null) return false;
    if($('input[name="emergency_flg"]:checked').val() != null) return false
    if($("input[class='dateDisabled']:checked").length > 0) return false;
    if($('input[name="start_datetime"]').val() != "") return false
    if($('input[name="end_datetime"]').val() != "") return false
    if($('input[name="target_roll[]"]:checked').val() != null) return false
    if($('input[name="brand[]"]:checked').val() != null) return false
    if($('input[name="organization[]"]:checked').val() != null) return false
    return true
}