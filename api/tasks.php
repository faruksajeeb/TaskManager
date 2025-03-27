<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

header("Content-Type: application/json");
require_once "../config/database.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$database = new Database();
$conn = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':  // Fetch tasks
        if (isset($_GET['id'])) {
            // Fetch a specific task by its ID
            $taskId = (int) $_GET['id'];
            $stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? AND id = ?");
            $stmt->execute([$user_id, $taskId]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($task) {
                echo json_encode(["task" => $task]);
            } else {
                echo json_encode(["error" => "Task not found"]);
            }
        } else {
            $stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY id DESC");
            $stmt->execute([$user_id]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':  // Add a new task
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['title']) && isset($data['due_date'])) {
            $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, due_date) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $data['title'], $data['description'], $data['due_date']]);
            echo json_encode(["message" => "Task added successfully"]);
        }
        break;

    case 'PUT':  // Update a task
        $data = json_decode(file_get_contents("php://input"), true);

        // Ensure that necessary data is provided
        if (isset($data['id'], $data['title'], $data['description'], $data['due_date'])) {

            // Prepare the SQL query to update the task
            $stmt = $conn->prepare("UPDATE tasks 
                                        SET title = ?, description = ?, due_date = ? 
                                        WHERE id = ? AND user_id = ?");

            // Execute the update query with the provided data
            $stmt->execute([
                $data['title'],
                $data['description'],
                $data['due_date'],
                $data['id'],
                $user_id
            ]);

            // Return a success message
            echo json_encode(["message" => "Task updated successfully"]);
        } elseif (isset($data['id'], $data['status']) && $data['status'] == 1) {
            // Mark task as completed
            $stmt = $conn->prepare("UPDATE tasks SET status = 1 WHERE id = ? AND user_id = ?");
            $stmt->execute([$data['id'], $user_id]);
            echo json_encode(["message" => "Task marked as completed"]);
        } else {
            // Handle missing fields
            echo json_encode(["error" => "Missing data"]);
        }
        break;
    case 'DELETE':  // Delete a task
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['id'])) {
            $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
            $stmt->execute([$data['id'], $user_id]);
            echo json_encode(["message" => "Task deleted successfully"]);
        }
        break;
    default:
        echo json_encode(["error" => "Invalid request"]);
}
?>