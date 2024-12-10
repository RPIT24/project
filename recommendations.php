<?php
// Include database connection
include 'db.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to see recommendations.";
    exit;
}

$user_id = $_SESSION['user_id']; // Get logged-in user ID from session

try {
    // Fetch movie recommendations based on shared reviews
    $stmt = $pdo->prepare("SELECT DISTINCT m.id, m.title 
                           FROM reviews r1
                           JOIN reviews r2 ON r1.movie_id = r2.movie_id
                           JOIN movies m ON m.id = r2.movie_id
                           WHERE r1.user_id = ? AND r2.user_id != ?");

    $stmt->execute([$user_id, $user_id]);

    // Fetch the recommendations
    $recommendations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($recommendations) {
        echo "<h2>Recommended Movies for You</h2>";
        echo "<ul>";
        foreach ($recommendations as $movie) {
            echo "<li>" . htmlspecialchars($movie['title']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "No recommendations available based on your reviews.";
    }
} catch (PDOException $e) {
    echo "An error occurred while fetching recommendations. Please try again later.";
    error_log("Database error: " . $e->getMessage());
}
?>
