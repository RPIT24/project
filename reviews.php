<?php
session_start(); // Start the session to manage user login status
include 'db.php'; // Include database connection file

class Review {
    private $movie_id;
    private $user_id;
    private $rating;
    private $review_text;

    public function __construct($movie_id, $user_id, $rating, $review_text) {
        $this->movie_id = $movie_id;
        $this->user_id = $user_id;
        $this->rating = $rating;
        $this->review_text = $review_text;
    }

    public static function submitReview($pdo, $movie_id, $user_id, $rating, $review_text) {
        // Validate rating range to ensure it's between 1 and 5
        if ($rating < 1 || $rating > 5) {
            throw new InvalidArgumentException('Rating must be between 1 and 5.');
        }

        // Prevent SQL injection by using prepared statements
        $stmt = $pdo->prepare("INSERT INTO reviews (movie_id, user_id, rating, review_text) 
                               VALUES (?)");
        return $stmt->execute([$movie_id, $user_id, $rating, $review_text]);
    }

    public static function getMovieReviews($pdo, $movie_id) {
        $stmt = $pdo->prepare("SELECT u.username, r.rating, r.review_text 
                               FROM reviews r
                               JOIN users u ON r.user_id = u.id
                               WHERE r.movie_id = ?");
        $stmt->execute([$movie_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
