<?php
// ===================================
// api/models/User.php
// ===================================
class User {
    private $conn;
    private $table_name = "users";

    // User properties
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $initials;
    public $color;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new user
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET first_name=:first_name, 
                    last_name=:last_name, 
                    email=:email, 
                    password=:password,
                    initials=:initials,
                    color=:color";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        // Generate initials
        $this->initials = strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
        
        // Generate random color
        $colors = ['#eb5a46', '#f2d600', '#61bd4f', '#0079bf', '#c377e0', '#ff78cb', '#00c2e0', '#51e898'];
        $this->color = $colors[array_rand($colors)];

        // Hash password
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        // Bind values
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":initials", $this->initials);
        $stmt->bindParam(":color", $this->color);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Check if email exists
    public function emailExists() {
        $query = "SELECT id, first_name, last_name, password, initials, color
                FROM " . $this->table_name . "
                WHERE email = ?
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        $num = $stmt->rowCount();

        if($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->password = $row['password'];
            $this->initials = $row['initials'];
            $this->color = $row['color'];

            return true;
        }

        return false;
    }

    // Update user
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET first_name = :first_name,
                    last_name = :last_name,
                    email = :email
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Update initials
        $this->initials = strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));

        // Bind values
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }
}