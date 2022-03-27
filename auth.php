<?php

//================================
// ログイン認証・自動ログアウト
//================================
// ログインしている場合
if (!empty($_SESSION['login_date'])) {
    debug('ログイン済みユーザーです。');

    // 現在日時が最終ログイン日時＋有効期限を超えていた場合
    if (($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
        debug('ログイン有効期限オーバーです。');

        // セッションを削除（ログアウトする）
        session_destroy();
        // ログインページへ
        header("Location:login.php");
    } else {
        debug('ログイン有効期限以内です。');
        //最終ログイン日時を現在日時に更新
        $_SESSION['login_date'] = time();

        //現在実行中のスクリプトファイル名がlogin.phpの場合
        //$_SERVER['PHP_SELF']はドメインからのパスを返すため、login.phpが返ってくるので、
        //さらにbasename関数を使うことでファイル名だけを取り出せる無限の繰り返しを止めることができる
        if (basename($_SERVER['PHP_SELF']) === 'login.php') {
            debug('マイページへ遷移します。');
            header("Location:mypage.php"); //マイページへ
        }
    }
} else {
    debug('未ログインユーザーです。');
    if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
        header("Location:login.php"); //ログインページへ
    }
}
