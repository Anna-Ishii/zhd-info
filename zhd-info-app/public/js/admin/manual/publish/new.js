window.onbeforeunload = function(e) {
    if(inputCheck()) return;
    e.preventDefault();
    e.returnValue = "";
}

// 入力チェック
// 何か入力状態であれば、falseを返す
function inputCheck() {
    if($('input[name="title"]').val() != "") return false;
    if($('textarea[name="description"]').val() != "") return false;
    if($('input[name="file"]').val() != "") return false;
    if($('input[name="category_id"]:checked').val() != null) return false;
    if($("input[class='dateDisabled']:checked").length > 0) return false;
    if(!(_manual_flow_titleCheck($('input[name^="manual_flow"][name$="[title]"]')))) return false
    if(!(_manual_flow_fileCheck($('input[name^="manual_flow"][name$="[file]"]')))) return false
    if(!(_manual_flow_detailCheck($('textarea[name^="manual_flow"][name$="[detail]"]')))) return false
    if($('input[name="start_datetime"]').val() != "") return false
    if($('input[name="end_datetime"]').val() != "") return false
    if($('input[name="brand[]"]:checked').val() != null) return false
    return true
}

// 値があったらfalseを変えす
function _manual_flow_titleCheck(objects) {
    if(objects == null) return true

    let rtn = true;
    objects.each((i,v) => {
        if(v.value != "") {
            rtn = false;
            return false; //ループを抜ける
        }
    });
    return rtn;
}

// fileがあったらfalseを変えす
function _manual_flow_fileCheck(objects) {
    if(objects == null) return true

    let rtn = true;
    objects.each((i,v) => {
        if(v.files.length > 0) {
            rtn = false;
            return false; //ループを抜ける
        }

    });
    return rtn;
}

function _manual_flow_detailCheck(objects) {
    if(objects == null) return true

    let rtn = true;
    objects.each((i,v) => {
        if(v.value != "") {
            rtn = false;
            return false; //ループを抜ける
        }
    });
    console.log(rtn);
    return rtn;
}