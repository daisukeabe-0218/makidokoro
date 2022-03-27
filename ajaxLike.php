<?php
//共通変数・関数ファイルをよみこみ
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　Ajax　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//==================================
// Ajax処理
//==================================

// postがあり、ユーザーIDがあり、ログインしている場合
if (isset($_POST['productId']) && isset($_SESSION['user_id']) && isLogin()) {
    debug('POST送信があります。');
    $p_id = $_POST('productId');
    debug('名所ID：' . $p_id);
    //例外処理
    try {
        // DBへ接続
        $dbh = dbConnect();
        //レコード検索
        $sql = 'SELECT * FROM likes WHERE place_id = :p_id AND user_id = :u_id';
        $data = array(':u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
        //　クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $resultCount = $stmt->rowCount();
        debug($resultCount);
        // レコードが1件でもある場合
        if (!empty($resultCount)) {
            // レコードを削除する
            $sql = 'DELETE FROM likes WHERE place_id = :p_id AND user_id = :u_id';
            $data = array(':u_id' => $_SESSION['user_id'], 'p_id' => $p_id);
            // クエリ実行
            $stmt = queryPost($dbh, $sql, $data);
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
    }
}
debug('Ajax処理終了  <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
