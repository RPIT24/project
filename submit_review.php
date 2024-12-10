<?php
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to submit a review.";
    exit;
}

// Ensure movie_id is provided in the URL and valid
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || $_GET['id'] <= 0) {
    echo "Error: Invalid movie ID.";
    exit;
}

$movie_id = (int) $_GET['id']; // Safely retrieve movie_id from URL

// Check if the form has been submitted (POST method)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $rating = floatval($_POST['rating']);  // Ensuring the rating is a float
    $review_text = isset($_POST['review_text']) ? trim($_POST['review_text']) : '';  // Handle empty review_text

    // Basic validation
    if ($rating < 1 || $rating > 5) {
        echo "Error: Rating must be between 1 and 5.";
        exit;
    }

    if (empty($review_text)) {
        echo "Error: Review text cannot be empty.";
        exit;
    }

    try {
        // Insert the review into the Reviews table
        $stmt = $pdo->prepare("INSERT INTO Reviews (movie_id, user_id, rating, review_text) VALUES (?, ?, ?, ?)");
        $stmt->execute([$movie_id, $_SESSION['user_id'], $rating, $review_text]);

        // Update the movie's average rating
        $stmt = $pdo->prepare("UPDATE Movies SET average_rating = 
                               (SELECT AVG(rating) FROM Reviews WHERE movie_id = ?) WHERE id = ?");
        $stmt->execute([$movie_id, $movie_id]);

        echo "Review submitted successfully!";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!-- Review Form HTML -->
<form method="POST" action="submit_review.php?id=<?php echo $movie_id; ?>"> <!-- Pass movie_id in the action URL -->
    <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>"> <!-- Set movie_id dynamically -->
    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>"> <!-- Set user_id dynamically -->
    
    <label for="rating">Rating:</label>
    <input type="number" name="rating" min="1" max="5" step="0.1" required>
    
    <label for="review_text">Review:</label>
    <textarea name="review_text" placeholder="Write your review..." required></textarea>
    
    <button type="submit">Submit Review</button>
</form>
