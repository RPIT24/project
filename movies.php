<?php
session_start(); // Start the session to manage user login status
include 'db.php'; // Include database connection file

// Check if the user is logged in, if not, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$searchQuery = isset($_GET['search']) ? $_GET['search'] : ''; // Get search query if available
$genreFilter = isset($_GET['genre']) ? $_GET['genre'] : ''; // Get genre filter if available
$movies = []; // Initialize an empty array to store the movie results

// Fetch genres for the dropdown menu
$genreStmt = $pdo->query("SELECT id, name FROM Genres");
$genres = $genreStmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all genres from the database

try {
    // Start building the SQL query to fetch movies based on the search query and genre filter
    $sql = "SELECT m.id, m.title, m.description, g.name AS genre,
                   AVG(r.rating) AS avg_rating
            FROM Movies m 
            JOIN Genres g ON m.genre_id = g.id
            LEFT JOIN Reviews r ON m.id = r.movie_id
            WHERE m.title LIKE :searchQuery"; // Filter by movie title using search query

    // Add the genre filter condition to the query if a genre is selected
    if ($genreFilter) {
        $sql .= " AND g.id = :genreFilter"; // Filter by selected genre
    }

    // Group by movie id to calculate average rating
    $sql .= " GROUP BY m.id";

    // Prepare the statement
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':searchQuery', '%' . $searchQuery . '%'); // Bind the search query parameter
    
    // Bind the genre filter if it exists
    if ($genreFilter) {
        $stmt->bindValue(':genreFilter', $genreFilter, PDO::PARAM_INT);
    }

    // Execute the query and fetch the results
    $stmt->execute();
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC); // Store the movie results in the $movies array

} catch (PDOException $e) {
    die("Error: " . $e->getMessage()); // Handle any errors during query execution
}

// Add Review
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['movie_id'])) {
    // Check if the form was submitted with a movie ID
    $movie_id = $_POST['movie_id'];
    $user_id = $_SESSION['user_id']; // Get the user ID from the session
    $rating = $_POST['rating']; // Get the rating from the form
    $review_text = $_POST['review']; // Get the review from the form

    try {
        // Insert the review and rating into the database
        $sql = "INSERT INTO Reviews (user_id, movie_id, review_text, rating) 
                VALUES (:user_id, :movie_id, :review_text, :rating)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT); // Bind user ID
        $stmt->bindValue(':movie_id', $movie_id, PDO::PARAM_INT); // Bind movie ID
        $stmt->bindValue(':review_text', $review_text, PDO::PARAM_STR); // Bind review text
        $stmt->bindValue(':rating', $rating, PDO::PARAM_INT); // Bind rating
        $stmt->execute(); // Execute the insert query

        // Optional: Update the movie's average rating
        $updateRatingSql = "UPDATE Movies SET average_rating = 
                            (SELECT AVG(rating) FROM Reviews WHERE movie_id = ?) 
                            WHERE id = ?";
        $updateStmt = $pdo->prepare($updateRatingSql);
        $updateStmt->execute([$movie_id, $movie_id]);

    } catch (PDOException $e) {
        die("Error: " . $e->getMessage()); // Handle any errors during insertion
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlickFusion: Where Movies Meet Reviews</title>

    <style>
        body {
            font-family: Arial, sans-serif; 
            background-color: #f4f4f9; 
            margin: 0; 
            padding: 0; 
            display: flex; 
            min-height: 100vh; 
            background-image: url('https://png.pngtree.com/thumb_back/fh260/background/20191106/pngtree-blue-film-film-curled-film-movie-film-and-television-works-image_321322.jpg');
        }

        .navbar {
            background-color: transparent; 
            width: 200px; 
            height: 100%; 
            padding-top: 20px; 
            position: fixed; 
            top: 0; 
            left: 0; 
            z-index: 1000; 
        }
        .navbar a {
            color: white; 
            padding: 10px 15px; 
            text-decoration: none; 
            display: block; 
        }

        .navbar a:hover {
            background-color: #ddd; 
            color: black; 
        }

        .container {
            flex: 1; 
            margin-left: 220px; 
            padding: 20px; 
            background-color: white; 
            border-radius: 8px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
            overflow-y: auto; 
        }

        h2 {
            text-align: center; 
            color: #4CAF50; 
            font-size: 36px; 
            font-family: 'Roboto', sans-serif; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            background: linear-gradient(to right, #FF6347, #4CAF50); 
            -webkit-background-clip: text; 
            color: transparent; 
            padding: 20px; 
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); 
            margin-bottom: 30px; 
        }

        ul {
            display: inline-flex; 
            flex-wrap: wrap; 
            list-style-type: none; 
            padding: 0; 
            justify-content: space-between; 
        }

        li {
            width: 30%; 
            margin-bottom: 20px; 
            padding: 15px; 
            background-color: #fff; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
            transition: transform 0.3s ease; 
        }

        li:hover {
            transform: scale(1.05); 
            cursor: pointer; 
        }

        li h3 {
            margin: 10px 0; 
            font-size: 20px; 
            color: #4CAF50; 
        }

        li p {
            font-size: 14px; 
            color: #555; 
        }

        li p.genre {
            font-weight: bold; 
            color: #333; 
        }

        input[type="text"], select, textarea {
            padding: 10px; 
            font-size: 16px; 
            margin-bottom: 20px; 
            width: 80%; 
        }

        button {
            padding: 10px 15px; 
            background-color: #4CAF50; 
            color: white; 
            border-radius: 6px; 
            text-align: center; 
            cursor: pointer; 
        }

    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <a href="#">Home</a>
        <a href="#">Movies</a>
        <a href="#">Genres</a>
        <a href="#">Reviews</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="container">
        <h2>FlickFusion</h2>

        <!-- Search and Genre Filter Form -->
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search for a movie..." value="<?= htmlspecialchars($searchQuery) ?>" style="width: 45%;">
            
            <select name="genre" style="width: 45%;">
                <option value="">Select Genre</option>
                <?php foreach ($genres as $genre): ?>
                    <option value="<?= $genre['id'] ?>" <?= $genre['id'] == $genreFilter ? 'selected' : '' ?>>
                        <?= htmlspecialchars($genre['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit">Search</button>
        </form>

        <!-- Movie List -->
        <ul>
            <?php if (count($movies) > 0): ?>
                <?php foreach ($movies as $movie): ?>
                    <li>
                        <h3><?= htmlspecialchars($movie['title']) ?></h3>
                        <p><?= htmlspecialchars($movie['description']) ?></p>
                        <p class="genre">Genre: <?= htmlspecialchars($movie['genre']) ?></p>
                        <p><strong>Average Rating:</strong> <?= htmlspecialchars($movie['avg_rating']) ?: 'No ratings yet' ?></p>

                        <!-- Review and Rating Form -->
                        <form method="POST" action="movies.php">
                            <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">
                            <label for="rating">Rating (1-5):</label>
                            <input type="number" name="rating" min="1" max="5" required>
                            <br>
                            <label for="review">Review:</label>
                            <textarea name="review" rows="4" required></textarea>
                            <br>
                            <button type="submit">Submit Review</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No movies found matching your search criteria.</p>
            <?php endif; ?>
        </ul>
    </div>
</body>
</html>
