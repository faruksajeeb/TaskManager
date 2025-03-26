<?php
require_once __DIR__ . '/../config/database.php';

class Task
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($user_id, $title, $description, $due_date)
    {
        $stmt = $this->conn->prepare("INSERT INTO tasks (user_id, title, description, due_date) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$user_id, $title, $description, $due_date]);
    }

    public function getAll($user_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $title, $description, $due_date)
    {
        $stmt = $this->conn->prepare("UPDATE tasks SET title=?, description=?, due_date=? WHERE id=?");
        return $stmt->execute([$title, $description, $due_date, $id]);
    }

    public function markCompleted($id)
    {
        $stmt = $this->conn->prepare("UPDATE tasks SET completed=1 WHERE id=?");
        return $stmt->execute([$id]);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM tasks WHERE id=?");
        return $stmt->execute([$id]);
    }
}
?>