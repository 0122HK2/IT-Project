<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>シンプル掲示板</title>
    <style>
        :root {
            --primary-color: #4a90e2;
            --bg-color: #f0f2f5;
            --card-bg: #ffffff;
            --text-color: #333;
            --muted-text: #666;
        }

        body {
            font-family: 'Helvetica Neue', Arial, 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container {
            max-width: 700px;
            width: 100%;
        }

        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            font-weight: 800;
        }

        /* フォームエリア */
        .form-area {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
        }

        .form-area h2 {
            margin-top: 0;
            font-size: 1.2rem;
            border-left: 4px solid var(--primary-color);
            padding-left: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1rem;
            margin-top: 5px;
        }

        button {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: opacity 0.2s;
        }

        button:hover {
            opacity: 0.8;
        }

        /* 検索・表示設定エリア */
        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 10px;
        }

        .search-area input {
            width: 200px;
            padding: 8px;
        }

        /* 投稿カード */
        .post {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            position: relative;
        }

        .post-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .nickname {
            font-weight: bold;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .date {
            font-size: 0.8rem;
            color: var(--muted-text);
        }

        .message {
            white-space: pre-wrap;
            line-height: 1.6;
        }

        .post-image img {
            width: 100%;
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-top: 15px;
        }

        .delete-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            color: #ff4d4d;
            cursor: pointer;
            font-size: 0.8rem;
            padding: 0;
        }

        /* ページネーション */
        .pagination {
            text-align: center;
            margin-top: 30px;
        }

        .pagination a {
            display: inline-block;
            padding: 8px 16px;
            background: white;
            border-radius: 5px;
            text-decoration: none;
            color: var(--text-color);
            margin: 0 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .pagination a.active {
            background: var(--primary-color);
            color: white;
        }
    </style>
</head>

<body>
    <h1>シンプル掲示板</h1>

    <?php
    // Cookieに保存されたニックネームがあるか確認
    $saved_nickname = isset($_COOKIE['saved_nickname']) ? $_COOKIE['saved_nickname'] : '';
    ?>

    <div class="form-area">
        <h2>新規投稿</h2>
        <form action="index.php" method="post" enctype="multipart/form-data">
            <div>
                <label for="nickname">ニックネーム：</label><br>
                <input type="text" id="nickname" accept="image/*" name="nickname"
                    value="<?php echo htmlspecialchars($saved_nickname, ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
            <div>
                <label for="message">本文：</label><br>
                <textarea id="message" name="message" rows="4" cols="40" required></textarea>
            </div>
            <div>
                <label for="image">画像：</label><br>
                <input type="file" id="image" name="image" accept="image/*">
            </div>
            <button type="submit">投稿する</button>
        </form>
    </div>

    <hr>

    <div class="search-area" style="margin-bottom: 20px;">
        <form action="index.php" method="get">
            <input type="text" name="q"
                value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                placeholder="キーワードを入力">
            <button type="submit">検索</button>
            <?php if (isset($_GET['q']) && $_GET['q'] !== ''): ?>
                <a href="index.php">クリア</a>
            <?php endif; ?>
        </form>
    </div>

    <hr>

    <h2>投稿一覧</h2>
    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <span class="nickname"><?php echo htmlspecialchars($post['nickname'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="date">(<?php echo htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8'); ?>)</span>

                <form action="index.php" method="post" style="display: inline;" onsubmit="return confirm('本当に削除しますか？');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                    <button type="submit" style="color: red; border: none; background: none; cursor: pointer;">[削除]</button>
                </form>

                <div class="message"><?php echo htmlspecialchars($post['message'], ENT_QUOTES, 'UTF-8'); ?></div>

                <?php if (!empty($post['image_path'])): ?>
                    <div class="post-image" style="margin-top: 10px;">
                        <img src="uploads/<?php echo htmlspecialchars($post['image_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="投稿画像"
                            style="max-width: 300px; height: auto; border-radius: 5px; border: 1px solid #eee;">
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>投稿はまだありません。</p>
    <?php endif; ?>

    <div class="display-settings">
        <form action="index.php" method="get" style="display: inline;">
            <input type="hidden" name="q" value="<?php echo htmlspecialchars($keyword ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            表示件数：
            <select name="limit" onchange="this.form.submit()">
                <option value="5" <?php if ($limit == 5)
                    echo 'selected'; ?>>5件</option>
                <option value="10" <?php if ($limit == 10)
                    echo 'selected'; ?>>10件</option>
                <option value="15" <?php if ($limit == 15)
                    echo 'selected'; ?>>15件</option>
            </select>
        </form>
    </div>

    <div class="pagination" style="margin-top: 20px;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="index.php?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&q=<?php echo urlencode($keyword); ?>"
                style="margin-right: 10px; <?php if ($i == $page)
                    echo 'font-weight: bold; text-decoration: none; color: black;'; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>

</body>

</html>