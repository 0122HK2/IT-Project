<?php
// index.php

require_once 'config/database.php';
require_once 'controllers/PostController.php';

// シンプルなルーティング
$controller = new PostController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 削除ボタンが押された場合（hiddenでactionを送る想定）
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $controller->delete();
    } else {
        $controller->create();
    }
} else {
    $controller->index();
}