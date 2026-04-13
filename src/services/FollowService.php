<?php
class FollowService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function followUser($followerId, $followeeId) {
        if ($followerId == $followeeId) {
            throw new Exception('Cannot follow yourself');
        }

        $check = $this->pdo->prepare("SELECT id FROM follows WHERE follower_id = ? AND followee_id = ?");
        $check->execute([$followerId, $followeeId]);
        if ($check->fetch()) {
            throw new Exception('Already following');
        }

        $insert = $this->pdo->prepare("INSERT INTO follows (follower_id, followee_id) VALUES (?, ?)");
        return $insert->execute([$followerId, $followeeId]);
    }

    public function unfollowUser($followerId, $followeeId) {
        $delete = $this->pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND followee_id = ?");
        return $delete->execute([$followerId, $followeeId]);
    }

    public function isFollowing($followerId, $followeeId) {
        $check = $this->pdo->prepare("SELECT id FROM follows WHERE follower_id = ? AND followee_id = ?");
        $check->execute([$followerId, $followeeId]);
        return (bool)$check->fetch();
    }

    public function getFollowers($userId) {
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.username, u.avatar_url, u.reliability_rating
            FROM follows f
            JOIN users u ON f.follower_id = u.id
            WHERE f.followee_id = ? LIMIT 50
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getFollowerCount($userId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM follows WHERE followee_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch()['cnt'];
    }
}
?>
