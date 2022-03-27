<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　商品詳細ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// 商品IDのGETパラメータを取得
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
// DBから商品データを取得
$viewData = getProductOne($p_id);
// パラメータに不正な値が入っているかチェック
if (empty($viewData)) {
    error_log('エラー発生:指定ページに不正な値が入りました');
    header("Location:index.php"); //トップページへ
}
debug('取得したDBデータ：' . print_r($viewData, true));

// post送信されていた場合
if (!empty($_POST['submit'])) {
    debug('POST送信があります。');

    //ログイン認証
    require('auth.php');

    //例外処理
    try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'INSERT INTO bord (sale_user,buy_user,product_id, create_date) VALUES (:s_uid, :b_uid, :p_id, :date)';
        $data = array(':s_uid' => $viewData['user_id'], ':b_uid' => $_SESSION['user_id'], ':p_id' => $p_id, ':date' => date('Y-m-d H:i:s'));
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        // クエリ成功の場合
        if ($stmt) {
            $_SESSION['msg_success'] = SUC05;
            debug('連絡掲示板へ遷移します。');
            header("Location:msg.php?m_id=" . $dbh->lastInsertID()); //連絡掲示板へ
        }
    } catch (Exception $e) {
        error_log('エラー発生:' . $e->getMessage());
        $err_msg['common'] = MSG07;
    }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '商品詳細';
require('head.php');
?>

<body class="page-productDetail page-1colum">
    <style>
        .badge {
            padding: 5px 10px;
            color: white;
            background: #7acee6;
            margin-right: 10px;
            font-size: 16px;
            vertical-align: middle;
            position: relative;
            top: -4px;
        }

        #main .title {
            font-size: 28px;
            padding: 10px 0;
        }

        .product-img-container {
            overflow: hidden;
        }

        .product-img-container img {
            width: 100%;
        }

        .product-img-container .img-main {
            width: 750px;
            float: left;
            padding-right: 15px;
            box-sizing: border-box;
        }

        .product-img-container .img-sub {
            width: 230px;
            float: left;
            background: #f6f5f4;
            padding: 15px;
            box-sizing: border-box;
        }

        .product-img-container .img-sub:hover {
            cursor: pointer;
        }

        .product-img-container .img-sub img {
            margin-bottom: 15px;
        }

        .product-img-container .img-sub img:last-child {
            margin-bottom: 0;
        }

        .product-detail {
            background: #f6f5f4;
            padding: 15px;
            margin-top: 15px;
            min-height: 150px;
        }

        .product-buy {
            overflow: hidden;
            margin-top: 15px;
            margin-bottom: 50px;
            height: 50px;
            line-height: 50px;
        }

        .product-buy .item-left {
            float: left;
        }

        .product-buy .item-right {
            float: right;
        }

        .product-buy .price {
            font-size: 32px;
            margin-right: 30px;
        }

        .product-buy .btn {
            border: none;
            font-size: 18px;
            padding: 10px 30px;
        }

        .product-buy .btn:hover {
            cursor: pointer;
        }

        /*お気に入りアイコン*/
        .icn-like {
            float: right;
            color: #ddd;
        }

        .icn-like:hover {
            cursor: pointer;
        }

        .icn-like.active {
            float: right;
            color: #fe8a8b;
        }
    </style>

    <!-- ヘッダー -->
    <?php
    require('header.php');
    ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

        <!-- Main -->
        <section id="main">

            <div class="title">
                <span class="badge"><?php echo sanitize($viewData['category']); ?></span>
                <?php echo sanitize($viewData['name']); ?>
                <i class="fa fa-heart icn-like js-click-like <?php if (isLike($_SESSION['user_id'], $viewData['id'])) {
                                                                    echo 'active';
                                                                } ?>" aria-hidden="true" data-productid="<?php echo sanitize($viewData['id']); ?>"></i>
            </div>
            <div class="product-img-container">
                <div class="img-main">
                    <img src="<?php echo showImg(sanitize($viewData['pic1'])); ?>" alt="メイン画像：<?php echo sanitize($viewData['name']); ?>" id="js-switch-img-main">
                </div>
                <div class="img-sub">
                    <img src="<?php echo showImg(sanitize($viewData['pic1'])); ?>" alt="画像1：<?php echo sanitize($viewData['name']); ?>" class="js-switch-img-sub">
                    <img src="<?php echo showImg(sanitize($viewData['pic2'])); ?>" alt="画像2：<?php echo sanitize($viewData['name']); ?>" class="js-switch-img-sub">
                    <img src="<?php echo showImg(sanitize($viewData['pic3'])); ?>" alt="画像3：<?php echo sanitize($viewData['name']); ?>" class="js-switch-img-sub">
                </div>
            </div>
            <div class="product-detail">
                <p><?php echo sanitize($viewData['comment']); ?></p>
            </div>
            <div class="product-buy">
                <div class="item-left">
                    <a href="index.php<?php echo appendGetParam(array('p_id')); ?>">&lt; 名所一覧に戻る</a>
                </div>
                <form action="" method="post">
                    <!-- formタグを追加し、ボタンをinputに変更し、style追加 -->
                    <div class="item-right">
                        <input type="submit" value="いいね!" name="submit" class="btn btn-primary" style="margin-top:0;">
                    </div>
                </form>
                <!-- puraisu -->
            </div>

        </section>

    </div>

    <!-- footer -->
    <?php
    require('footer.php');
    ?>