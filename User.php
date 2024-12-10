<?php
session_start(); // Start the session to manage user login status
include 'db.php'; // Include database connection file
class User {
    private $id;
    private $username;
    private $email;
    private $password_hash;

    // Constructor to initialize the User object
    public function __construct($id, $username, $email, $password_hash) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password_hash = $password_hash;
    }

    /**
     * Registers a new user in the database
     *
     * @param PDO $pdo Database connection
     * @param string $username Username of the user
     * @param string $email Email of the user
     * @param string $password Password for the user
     * @return bool Returns true if registration is successful
     */
    public static function register($pdo, $username, $email, $password) {
        // Hash the password before saving it
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        // Prepare SQL statement for insertion
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        
        // Execute the statement and return whether it was successful
        return $stmt->execute([$username, $email, $password_hash]);
    }

    /**
     * Logs in a user by checking credentials
     *
     * @param PDO $pdo Database connection
     * @param string $email User's email
     * @param string $password User's password
     * @return User|null Returns a User object if successful, null if credentials are incorrect
     */
    public static function login($pdo, $email, $password) {
        // Fetch user by email
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if the password matches the hashed password in the database
        if ($user && password_verify($password, $user['password_hash'])) {
            // Return a new User object with the user's details
            return new User($user['id'], $email, $email, $user['password_hash']);
        }

        // Return null if login failed
        return null;
    }
}
?>
