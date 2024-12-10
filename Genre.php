<?php
class Genre {
    private $id;
    private $name;

    // Constructor to initialize the Genre object
    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * Fetches all genres from the database
     *
     * @param PDO $pdo Database connection
     * @return array List of all genres
     */
    public static function fetchGenres($pdo) {
        try {
            // Prepare and execute the query to fetch all genres
            $stmt = $pdo->query("SELECT * FROM genres");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Handle any database errors
            die("Error fetching genres: " . $e->getMessage());
        }
    }

    /**
     * Retrieves movies by genre ID
     *
     * @param PDO $pdo Database connection
     * @param int $genre_id Genre ID to fetch movies for
     * @return array List of movies that belong to the specified genre
     */
    public static function getMoviesByGenre($pdo, $genre_id) {
        try {
            // Prepare and execute the query to fetch movies by genre
            $stmt = $pdo->prepare("SELECT * FROM movies WHERE genre_id = ?");
            $stmt->execute([$genre_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Handle any database errors
            die("Error fetching movies for the genre: " . $e->getMessage());
        }
    }
}
?>