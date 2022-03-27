<?php
//ログを取る
ini_set('log_errors', 'on');
//ログファイル
ini_set('error_log', 'php.log');

// エラーメッセージを定数に設定
define('MSG01', '入力必須です');
define('MSG02', 'Emailの形式ではありません');
define('MSG03', 'パスワード（再入力）が違います');
define('MSG04', '半角英数字以外が含まれています');
define('MSG05', 'パスワードは6文字以上で入力してください');
define('MSG06', 'パスワードは255文字以下で入力してください');
define('MSG07', 'エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG08', 'そのEmailは既に登録されています');

/*================================================
関数まとめ 
・$strは、POSTされた変数が入り、バリデーションを行う
・$keyは、err_msg変数の表示場所になっている。
=================================================*/
// エラーメッセージ格納用配列$err_msg変数定義
$err_msg = array();
// 未入力チェック
function validRequired($str, $key)
{
    if (empty($str)) {
        global $err_msg;
        $err_msg[$key] = MSG01;
    }
}
// email形式チェック
function validEmail($str, $key)
{
    if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
        global $err_msg;
        $err_msg[$key] = MSG02;
    }
}
// email重複チェック
function validEmailDup($email)
{
    global $err_msg;
    //例外処理
    try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT count(*) FROM users WHERE email = :email';
        $data = array(':email' => $email);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        // クエリ結果の値を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        var_dump($result);
        // array(1) { ["count(*)"]=> string(1) "1" }重複

        //array_shiftは配列の先頭を取り出す関数。$result変数並列の先頭$stmtを取り出せば、DB作成、SQL文作成を同時にできる。
        if (!empty(array_shift($result))) {
            $err_msg['email'] = MSG08;
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
        $err_msg['common'] = MSG07;
    }
}
//同値チェック（パスワード）
function VAlidMatch($str1, $str2, $key)
{
    if ($str1 !== $str2) {
        global $err_msg;
        $err_msg['$key'] = MSG03;
    }
}
//最小文字数チェック（パスワード）
function VAlidMinLen($str, $key, $min = 6)
{
    if (mb_strlen($str) < $min) {
        global $err_msg;
        $err_msg['$key'] = MSG05;
    }
}
//最大文字数チェック（パスワード）
function ValidMaxLen($str, $key, $max = 255)
{
    if (mb_strlen($str) >= $max) {
        global $err_msg;
        $err_msg['$key'] = MSG06;
    }
}
//バリデーション関数（半角チェック）
function validHalf($str, $key)
{
    if (!preg_match("/^[a-zA-Z0-9]+$/", $key)) {
        global $err_msg;
        $err_msg[$key] = MSG04;
    }
}
//DB接続関数
function dbConnect()
{
    //DBへの接続準備
    $dsn = 'mysql:dbname=ishimakidokoro;host=localhost;charset=utf8';
    $user = 'root';
    $password = 'root';
    $options = array(
        // SQL実行失敗時にはエラーコードのみ設定
        PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
        // デフォルトフェッチモードを連想配列形式に設定
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
        // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );
    // PDOオブジェクト生成（DBへ接続）
    $dbh = new PDO($dsn, $user, $password, $options);
    return $dbh;
}
//SQL実行関数
function queryPost($dbh, $sql, $data)
{
    //クエリー作成
    $stmt = $dbh->prepare($sql);
    //プレースホルダに値をセットし、SQL文を実行
    $stmt->execute($data);
    return $stmt;
}
/*ここまでで関数まとめ終了 function.phpを別に作りrequireで読み込ませた方が見やすい
======================================================================*/

/*====================================================
POSTされた値を変数定義し、実際に関数を呼び出し処理を行う。
====================================================*/
//POSTされたら上から処理が開始する
if (!empty($_POST)) {
    // フォームでPOSTされたユーザー情報を変数に代入定義
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $pass_re = $_POST['pass_re'];

    //未入力チェック
    validRequired($email, 'email');
    validRequired($pass, 'pass');
    validRequired($pass_re, 'pass_re');

    if (empty($err_msg)) {

        //emailの形式チェック
        validEmail($email, 'email');
        //emailの最大文字数チェック
        validMaxLen($email, 'email');
        //email重複チェック
        validEmailDup($email);

        //パスワードの半角英数字チェック
        validHalf($pass, 'pass');
        //パスワードの最大文字数チェック
        validMaxLen($pass, 'pass');
        //パスワードの最小文字数チェック
        validMinLen($pass, 'pass');

        //パスワード（再入力）の最大文字数チェック
        validMaxLen($pass_re, 'pass_re');
        //パスワード（再入力）の最小文字数チェック
        validMinLen($pass_re, 'pass_re');

        if (empty($err_msg)) {

            //パスワードとパスワード再入力が合っているかチェック
            validMatch($pass, $pass_re, 'pass_re');
            if (empty($err_msg)) {

                //例外処理
                try {
                    // DBへ接続
                    $dbh = dbConnect();
                    // SQL文作成
                    $sql = 'INSERT INTO users (email,password,login_time,create_date) VALUES(:email,:pass,:login_time,:create_date)';
                    $data = array(
                        ':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT),
                        ':login_time' => date('Y-m-d H:i:s'),
                        ':create_date' => date('Y-m-d H:i:s')
                    );
                    // クエリ実行
                    queryPost($dbh, $sql, $data);

                    header("Location:mypage.html"); //マイページへ

                } catch (Exception $e) {
                    error_log('エラー発生:' . $e->getMessage());
                    $err_msg['common'] = MSG07;
                }
            }
        }
    }
}
?>
<!-- HTML -->


<!-- head -->
<?php
$siteTitle = 'まきどころ';
require('head.php');
?>

<body class="page-signup page-1colum">

    <!-- header -->
    <?php
    $siteTitle = 'HOME';
    require('header.php');
    ?>


    <!-- メインコンテンツ -->
    <main id="contents" class="site-width">

        <!-- Main -->
        <div id="main">

            <div class="form-container">

                <form action="mypage.php" method="post" class="form" autocomplete="off">
                    <h2 class="title">ユーザー登録</h2>
                    <div class="area-msg">
                    </div>
                    <!-- class属性”err＂はエラー時に、背景を赤っぽくするために記述(cssに記述ずみ) -->
                    <label>
                        Email
                        <!-- キーはname属性で空で無い場合、＄＿POSTに格納しvalue値をemailと定義する。$_POSTに格納された”email”の値は、上のバリデーションチェックに回される(passとpass_reも同じ）-->
                        <input type="text" name="email" value="<?php if (!empty($_POST['email'])) echo $_POST['email']; ?>">
                    </label>
                    <label>
                        パスワード <span style="font-size:12px">※半角英数字６文字以上、255文字以下で入力</span>
                        <input type="text" name="pass" 　value="<?php if (!empty($_POST['pass'])) echo $_POST['pass']; ?>">
                    </label>
                    <label>
                        パスワード（再入力）
                        <input type="text" name="pass_re" value="<?php if (!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>">
                    </label>
                    <div class=" btn-container">
                        <input type="submit" class="btn btn-mid" value="登録する！">
                    </div>
                </form>
            </div>

        </div>

    </main>
    <?php
    require('footer.php');
    ?>