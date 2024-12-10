<?php
// Include database connection
include 'db.php';
session_start();

$movie_id = isset($_GET['movie_id']) ? (int) $_GET['movie_id'] : 0;

if ($movie_id <= 0) {
    echo "Invalid movie ID.";
    exit;
}

try {
    // Fetch movie details
    $stmt = $pdo->prepare("SELECT m.title, m.description, m.average_rating, g.name AS genre 
                           FROM movies m 
                           JOIN genres g ON m.genre_id = g.id 
                           WHERE m.id = ?");
    $stmt->execute([$movie_id]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$movie) {
        echo "Movie not found.";
        exit;
    }

    // Fetch reviews for the movie
    $stmt = $pdo->prepare("SELECT u.username, r.rating, r.review_text 
                           FROM reviews r
                           JOIN users u ON r.user_id = u.id
                           WHERE r.movie_id = ?");
    $stmt->execute([$movie_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<h2><?= htmlspecialchars($movie['title']) ?></h2>
<p><?= htmlspecialchars($movie['description']) ?></p>
<p><strong>Genre:</strong> <?= htmlspecialchars($movie['genre']) ?></p>
<p><strong>Average Rating:</strong> <?= number_format($movie['average_rating'], 1) ?> / 5</p>

<h3>Reviews</h3>
<ul>
    <?php foreach ($reviews as $review): ?>
        <li>
            <strong><?= htmlspecialchars($review['username']) ?>:</strong>
            <p>Rating: <?= htmlspecialchars($review['rating']) ?>/5</p>
            <p><?= htmlspecialchars($review['review_text']) ?></p>
        </li>
    <?php endforeach; ?>
</ul>

<a href="submit_review.php?movie_id=<?= $movie_id ?>">Submit Your Review</a>
