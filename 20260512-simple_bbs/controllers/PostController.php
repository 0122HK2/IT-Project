<?php
// controllers/PostController.php

require_once 'models/Post.php';

class PostController
{
    private $db;
    private $post;

    public function __construct()
    {
        // 1. データベース接続の準備
        $database = new Database();
        $this->db = $database->getConnection();

        // 2. Postモデルをインスタンス化する
        $this->post = new Post($this->db);
    }

    /**
     * 一覧表示機能
     */

    public function index()
    {
        $keyword = isset($_GET['q']) ? trim($_GET['q']) : '';

        // ページング設定
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5; // デフォルト5件
        if (!in_array($limit, [5, 10, 15]))
            $limit = 5; // 指定以外は5件に固定

        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($page < 1)
            $page = 1;

        $offset = ($page - 1) * $limit;

        // データ取得
        $totalPosts = $this->post->countAll($keyword);
        $totalPages = ceil($totalPosts / $limit);
        $stmt = $this->post->getPaged($keyword, $limit, $offset);

        $posts = [];
        if ($stmt) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $posts[] = $row;
            }
        }

        include_once 'views/index.php';
    }


    /**
     * 投稿保存機能
     */
    public function create()
    {
        $nickname = isset($_POST['nickname']) ? trim($_POST['nickname']) : '';
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        $image_path = null;

        // 画像アップロードの処理
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            // ファイル名をユニークにする（上書き防止）
            $file_name = uniqid() . '.' . $file_ext;
            $target_path = $upload_dir . $file_name;

            // 画像形式の簡易チェック
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array(strtolower($file_ext), $allowed_types)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    $image_path = $file_name;
                }
            }
        }

        if ($nickname === '' || $message === '') {
            header("Location: index.php");
            exit;
        }

        $this->post->nickname = $nickname;
        $this->post->message = $message;
        $this->post->image_path = $image_path;

        if ($this->post->create()) {
            setcookie('saved_nickname', $nickname, time() + (60 * 60 * 24 * 30), '/');
            header("Location: index.php");
            exit;
        }
    }

    /**
     * 削除処理
     */
    public function delete()
    {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if ($id > 0) {
            if ($this->post->delete($id)) {
                // 削除後、トップページへリダイレクト（PRGパターンに準ずる）
                header("Location: index.php");
                exit;
            }
        }
    }
}