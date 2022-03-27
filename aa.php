<?php
function getMypages($u_id)
{
    debug('自分の商品情報を取得します。');
    debug('ユーザーID：' . $u_id);
    // 例外処理
    try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT * FROM famous WHERE user_id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        //　クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
            // クエリ結果のデータを全レコード返却
            return $stmt->fetchAll();
        } else {
            return false;
        }
    } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
    }
}
?>
<?php
// テスト
if (function_exists('getMypages')) {
    echo "ある";
} else {
    echo "ない";
}
?>