<?php
// シンプルな疎通確認用エンドポイント
http_response_code(200);
header('Content-Type: text/plain; charset=utf-8');
echo "ok\n";
