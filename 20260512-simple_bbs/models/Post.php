<?php
// models/Post.php

class Post
{
    private $conn;
    private $table_name = "posts";

    // プロパティ（テーブルのカラムに対応）
    public $id;
    public $nickname;
    public $message;
    public $created_at;

    // コンストラクタでDB接続を受け取る
    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * 投稿を取得する（検索キーワード対応）
     */
    public function getAll($keyword = '')
    {
        $query = "SELECT id, nickname, message, created_at FROM " . $this->table_name;

        // キーワードがある場合、WHERE句を追加
        if ($keyword !== '') {
            $query .= " WHERE nickname LIKE :keyword OR message LIKE :keyword";
        }

        $query .= " ORDER BY created_at DESC";

        try {
            $stmt = $this->conn->prepare($query);

            if ($keyword !== '') {
                $searchParam = "%" . $keyword . "%";
                $stmt->bindParam(':keyword', $searchParam);
            }

            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * 新規投稿を保存する
     */

    public $image_path; // プロパティ追加

    public function create()
    {
        // 挿入クエリに image_path を追加
        $query = "INSERT INTO " . $this->table_name . " (nickname, message, image_path) VALUES (:nickname, :message, :image_path)";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nickname', $this->nickname);
            $stmt->bindParam(':message', $this->message);
            $stmt->bindParam(':image_path', $this->image_path);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * 特定の投稿を削除する
     */
    public function delete($id)
    {
        // SQLインジェクション対策（静的プレースホルダ）
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            return false;
        }
        return false;
    }

    /**
     * ページング用の投稿取得（image_pathを追加）
     */
    public function getPaged($keyword, $limit, $offset)
    {
        // 【重要】SELECT句に image_path を追加します
        $query = "SELECT id, nickname, message, created_at, image_path 
              FROM " . $this->table_name;

        if ($keyword !== '') {
            $query .= " WHERE nickname LIKE :keyword OR message LIKE :keyword";
        }

        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->conn->prepare($query);
            if ($keyword !== '') {
                $searchParam = "%" . $keyword . "%";
                $stmt->bindValue(':keyword', $searchParam);
            }
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * 総投稿数を取得（検索条件反映）
     */
    public function countAll($keyword = '')
    {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        if ($keyword !== '') {
            $query .= " WHERE nickname LIKE :keyword OR message LIKE :keyword";
        }
        $stmt = $this->conn->prepare($query);
        if ($keyword !== '') {
            $searchParam = "%" . $keyword . "%";
            $stmt->bindValue(':keyword', $searchParam);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}