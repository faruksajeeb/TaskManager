# Task Manager with Social Media Integration

## Overview

This is a simple Task Management System built with PHP and MySQL using Object-Oriented Programming (OOP). Users can log in, manage tasks (CRUD operations), and share completed tasks on Facebook.

## Features

- **User Authentication** (Login with session management)
- **Task Management** (Create, Read, Update, Delete tasks)
- **Social Media Integration**:
  - Post completed tasks to **Facebook** (via Facebook Graph API)
- **AJAX-Based CRUD Operations** (No page reload)
- **Bootstrap UI** (Responsive design)
- **REST API** (Manage tasks via API)

## Installation

### 1. Clone the repository

```sh
 git clone https://github.com/faruksajeeb/task-manager.git
 cd task-manager
```

### 2. Configure Database

1. Create a database in MySQL.
2. Import the `task_manager.sql` file located in the project root.
3. Update the database credentials in `config/database.php`:

### 🔹 Database Configuration (`config/database.php`)

```php
<?php
$host = 'localhost';
$dbname = 'task_manager';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

4. Use the following credentials to log in to the application:
   - **Username:** `admin`
   - **Password:** `123456`

### 3. Start the Server

For PHP's built-in server, run:

```sh
 php -S localhost:8000
```

Then, open [http://localhost:8000](http://localhost:8000) in your browser.

---

# Task Manager API

This API allows users to manage tasks with Create, Read, Update, and Delete (CRUD) operations.

## Base URL

http://yourdomain.com/api/tasks.php

---

### 1️⃣ Fetch All Tasks

**Method:** `GET`  
**URL:** `/api/tasks.php`  
**Example Request:**

```http
GET /api/tasks.php HTTP/1.1
Host: yourdomain.com
```

**Example Response:**

```json
[
  {
    "id": 1,
    "title": "Complete project one",
    "description": "Finish API documentation",
    "due_date": "2025-03-01",
    "user_id": 1,
    "status": 0
  },
  {
    "id": 2,
    "title": "Complete project two",
    "description": "Finish API documentation two",
    "due_date": "2025-03-02",
    "user_id": 1,
    "status": 0
  }
]
```

---

### 2️⃣ Fetch a Specific Task

**Method:** `GET`  
**URL:** `/api/tasks.php?id={task_id}`  
**Example Request:**

```http
GET /api/tasks.php?id=1 HTTP/1.1
Host: yourdomain.com
```

**Example Response:**

```json
{
  "task": {
    "id": 1,
    "title": "Complete project",
    "description": "Finish API documentation",
    "due_date": "2025-04-01",
    "status": 0
  }
}
```

---

### 3️⃣ Create a Task

**Method:** `POST`  
**URL:** `/api/tasks.php`  
**Headers:**

```http
Content-Type: application/json
```

**Request Body:**

```json
{
  "title": "New Task",
  "description": "Task details",
  "due_date": "2025-04-10"
}
```

**Response:**

```json
{
  "message": "Task added successfully"
}
```

---

### 4️⃣ Update a Task

**Method:** `PUT`  
**URL:** `/api/tasks.php`  
**Headers:**

```http
Content-Type: application/json
```

**Request Body:**

```json
{
  "id": 1,
  "title": "Updated Task",
  "description": "Updated details",
  "due_date": "2025-04-15"
}
```

**Response:**

```json
{
  "message": "Task updated successfully"
}
```

---

### 5️⃣ Mark Task as Completed

**Method:** `PUT`  
**URL:** `/api/tasks.php`  
**Headers:**

```http
Content-Type: application/json
```

**Request Body:**

```json
{
  "id": 1,
  "status": 1
}
```

**Response:**

```json
{
  "message": "Task marked as completed"
}
```

---

### 6️⃣ Delete a Task

**Method:** `DELETE`  
**URL:** `/api/tasks.php`  
**Headers:**

```http
Content-Type: application/json
```

**Request Body:**

```json
{
  "id": 1
}
```

**Response:**

```json
{
  "message": "Task deleted successfully"
}
```

---

## Error Responses

| Error Type      | HTTP Status       | Response Example                               |
| --------------- | ----------------- | ---------------------------------------------- |
| Invalid Task ID | `400 Bad Request` | `{"error": "Invalid task ID"}`                 |
| Task Not Found  | `404 Not Found`   | `{"error": "Task not found"}`                  |
| Missing Fields  | `400 Bad Request` | `{"error": "Title and Due Date are required"}` |

---

## Additional Notes

- Ensure `user_id` is properly authenticated before making requests.
- The `due_date` should be in `YYYY-MM-DD` format.
- The `status` field is `0` for pending and `1` for completed.

---

## 📝 Task Manager API Source Code

### 🔹 API Handler (api/task.php)

```php
<?php
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
```

### 📝 Task Manager view file Source Code

## Facebook Integration

### 1. Create a Facebook App

1. Go to [Facebook Developer Console](https://developers.facebook.com/).
2. Create a new app and enable **Facebook Login**.
3. Add the **Facebook Graph API** and request the required permissions (`publish_pages`, `pages_manage_posts`).
4. Get the `FB App ID` and `FB Page ID`.
5. Add this to `config/facebook.php`:

```php
 define('FB_APP_ID', 'your_facebook_app_id');
 define('FB_APP_SECRET', 'your_facebook_app_secret');
 define('FB_PAGE_ID', 'your_facebook_page_id');
 define('SITE_URL', 'your_site_url');
```

### 2. Enable JavaScript SDK

1. Go to **Facebook App Settings** > **Login Settings**.
2. Enable **Log in with JavaScript SDK**.

### 3. Post Task to Facebook

Tasks are posted via:

```javascript
$(document).on("click", ".postToFacebook", function () {
  let taskId = $(this).data("id");

  getTaskById(taskId)
    .then((task) => {
      console.log(task);
      postToFacebook(task);
    })
    .catch((error) => {
      console.error(error); // Handle error
    });
});

// Fetch Task by ID
function getTaskById(taskId) {
  return new Promise((resolve, reject) => {
    $.ajax({
      url: `api/tasks.php?id=${taskId}`, // Fetch task by ID
      method: "GET",
      dataType: "json",
      success: function (response) {
        if (response.task) {
          resolve(response.task); // Resolve with the task data
        } else {
          reject("Task not found");
        }
      },
      error: function (xhr, status, error) {
        reject("Error fetching task: " + error);
      },
    });
  });
}

function shareToFacebook(task) {
  FB.ui(
    {
      method: "share",
      href: "<?= SITE_URL ?>/api/task.php?id=" + task.id, // Replace with your task URL
      quote: `Task Completed: ${task.title}\n Details: ${task.description}\n Due Date: ${task.due_date}`,
    },
    function (response) {
      if (response && !response.error_message) {
        alert("Task successfully shared on Facebook!");
      } else {
        alert("Failed to share task.");
      }
    }
  );
}

function postToFacebook(task, pageAccessToken) {
  let message = `Task Completed: ${task.title}\n Details: ${task.description}\n Due Date: ${task.due_date}`;
  loginToFacebook(function (pageAccessToken) {
    $.ajax({
      url: `https://graph.facebook.com/<?= FB_GRAPH_VERSION ?>/<?= FB_PAGE_ID ?>/feed`,
      type: "POST",
      data: {
        message: message,
        access_token: pageAccessToken,
      },
      success: function (response) {
        alert("Task successfully posted on Facebook Page!");
        console.log(response);
      },
      error: function (error) {
        alert("Failed to post task.");
        console.log(error);
      },
    });
  });
}

function loginToFacebook(callback) {
  FB.getLoginStatus(function (response) {
    if (response.status === "connected") {
      console.log("Logged in!");
      getPageAccessToken(response.authResponse.accessToken, callback);
    } else {
      FB.login(
        function (response) {
          if (response.authResponse) {
            console.log("Login successful!");
            getPageAccessToken(response.authResponse.accessToken, callback);
          } else {
            console.log("User canceled login.");
          }
        },
        {
          scope: "pages_manage_posts,pages_read_engagement",
        }
      );
    }
  });
}

function getPageAccessToken(userAccessToken, callback) {
  FB.api(
    "/me/accounts",
    "GET",
    {
      access_token: userAccessToken,
    },
    function (response) {
      if (response.data && response.data.length > 0) {
        let pageAccessToken = response.data[0].access_token; // First page access token
        callback(pageAccessToken);
      } else {
        alert("No pages found. Make sure you are an admin.");
      }
    }
  );
}
```

---

## API Endpoints

| Method | Endpoint         | Description       |
| ------ | ---------------- | ----------------- |
| GET    | `/api/tasks.php` | Fetch all tasks   |
| POST   | `/api/tasks.php` | Create a new task |
| PUT    | `/api/tasks.php` | Update a task     |
| DELETE | `/api/tasks.php` | Delete a task     |

---

## 📝 Task Manager - Web Interface

### 🔹 Login HTML File (`index.php`)

```html
<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once "classes/User.php";
$user = new User();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if ($user->login($username, $password)) {
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid credentials!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="container mt-5 pt-5">
        <div class="row mt-5 pt-5">
            <div
                class="col-lg-4 col-md-6 col-sm-12 offset-lg-4 offset-md-3 offset-sm-0 mt-5 pt-5 rounded p-4 login-form">

                <h1 class="text-center py-3">Login</h1>
                <?php if (isset($error))
                    echo "<p class='text-danger'>$error</p>";
                ?>
                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon1">@</span>
                            <input type="text" name="username" placeholder="Username" required
                                class="form-control form-control-lg" id="username" aria-describedby="basic-addon1">
                            <div class="invalid-feedback">
                                Please enter your username.
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon2">🔒</span>
                            <input type="password" name="password" placeholder="Password" required
                                class="form-control form-control-lg" id="password" aria-describedby="basic-addon2">
                            <span class="input-group-text password-toggle" id="password-toggle">
                                <i class="fa fa-eye"></i>
                            </span>
                            <div class="invalid-feedback">
                                Please enter your password.
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg form-control">Login</button>
                </form>
                <div class="text-center mt-3">
                    Don't have an account? <a href="register.php">Register</a>
                </div>
            </div>
        </div>

    </div>
    <script>
    (function() {
        'use strict'

        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation')

        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('password-toggle');

        passwordToggle.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.innerHTML = '<i class="fa fa-eye-slash"></i>';
            } else {
                passwordInput.type = 'password';
                this.innerHTML = '<i class="fa fa-eye"></i>';
            }
        });
    })()
    </script>
</body>

</html>
```

### 🔹 Dashboard HTML File (`classes/User.php`)

```php
<?php
require_once __DIR__ . '/../config/database.php';

class User
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function login($username, $password)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            return true;
        }
        return false;
    }

    public function logout()
    {
        session_start();
        session_destroy();
        header("Location: index.php");
    }
}
?>
```

### 🔹 Dashboard HTML File (`dashboard.php`)

```html
<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

session_start();
require_once "classes/User.php";
$user = new User();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if (isset($_GET['logout'])) {
    $user->logout(); exit; } ?>

<!DOCTYPE html>
<html>
  <head>
    <title>Task Manager</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link
      rel="stylesheet"
      href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />

    <link rel="stylesheet" href="assets/css/style.css" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  </head>

  <body>
    <?php include('includes/sidebar.php'); ?>
    <div id="content">
      <?php include('includes/header.php'); ?>
      <div class="container-fluid mt-4">
        <?php include('task/manage_task.php'); ?>
      </div>
    </div>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script></script>
  </body>
</html>
```

### 🔹 Dashboard HTML File (`includes/header.php`)

```html
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Task Manager</a>
    <div class="d-flex ms-auto">
      <div class="dropdown">
        <button
          class="btn btn-secondary dropdown-toggle"
          type="button"
          id="dropdownMenuButton1"
          data-bs-toggle="dropdown"
          aria-expanded="false"
        >
          <i class="fas fa-user"></i> User
        </button>
        <ul
          class="dropdown-menu dropdown-menu-end"
          aria-labelledby="dropdownMenuButton1"
        >
          <li>
            <a class="dropdown-item" href="?logout=true"
              ><i class="fas fa-sign-out-alt"></i> Logout</a
            >
          </li>
        </ul>
      </div>
    </div>
  </div>
</nav>
```

### 🔹 Dashboard HTML File (`includes/sidebar.php`)

```html
<div id="sidebar">
  <ul class="nav flex-column">
    <li class="nav-item">
      <a class="nav-link" href="#"
        ><i class="fas fa-tachometer-alt"></i> Dashboard</a
      >
    </li>
    <!-- <li class="nav-item">
            <a class="nav-link" href="#"><i class="fas fa-users"></i> Users</a>
        </li> -->
    <li class="nav-item">
      <a class="nav-link" href="#"><i class="fas fa-file"></i> Tasks</a>
    </li>
  </ul>
</div>
```

### 🔹 Manage task HTML File (`task/manage_task.php`)

```html
<?php
require_once __DIR__ . '/../config/facebook.php';
?>
<h2 class="mb-4">Manage Task</h2>
<hr />
<div class="row">
  <div class="col-md-4">
    <!-- Add Task Form -->
    <?php include('task/add_task.php'); ?>
  </div>
  <div class="col-md-8">
    <!-- Task List -->
    <h4>Task List</h4>
    <table class="table table-bordered table-striped ">
      <thead>
        <tr class="table-secondary">
          <th>Title</th>
          <th>Description</th>
          <th>Due Date</th>
          <th class="text-center">Status</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody id="taskList"></tbody>
    </table>
  </div>
</div>
<script>
  // Initialize the Facebook SDK
  window.fbAsyncInit = function () {
    FB.init({
      appId: "<?= FB_APP_ID ?>",
      cookie: true,
      xfbml: true,
      version: "<?= FB_GRAPH_VERSION ?>", // Specify the Facebook Graph API version
    });

    // Check if the user is logged into Facebook
    FB.getLoginStatus(function (response) {
      if (response.status === "connected") {
        console.log("User is logged in to Facebook");
      } else {
        console.log("User is not logged in to Facebook");
      }
    });
  };

  // Load the SDK asynchronously
  (function (d, s, id) {
    var js,
      fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {
      return;
    }
    js = d.createElement(s);
    js.id = id;
    js.src = "https://connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  })(document, "script", "facebook-jssdk");

  $(function () {
    $(".datepicker").datepicker({
      dateFormat: "yy-mm-dd",
    });

    loadTasks(); // Load tasks on page load

    // Fetch tasks from API
    function loadTasks() {
      $.getJSON("api/tasks.php", function (data) {
        let rows = "";
        $.each(data, function (index, task) {
          let statusBadge =
            task.status == 0
              ? `<span class="badge bg-warning">Pending</span>`
              : `<span class="badge bg-success">Completed</span>`;
          rows += `<tr>
                            <td>${task.title}</td>
                            <td>${task.description}</td>
                            <td>${task.due_date}</td>
                            <td class="text-center">${statusBadge}</td>
                            <td class="text-center text-nowrap">
                                ${
                                  task.status === 0
                                    ? `<button class="btn btn-outline-primary btn-sm editTask"  
                                data-id="${task.id}" 
                                data-title="${task.title}" 
                                data-description="${task.description}" 
                                data-due_date="${task.due_date}" 
                                data-status="${task.status}"
                                ><i class="fa fa-edit"></i> Edit</button>
                                <button class="btn btn-outline-success btn-sm updateStatus" data-id="${task.id}" data-status="1">Complete</button>`
                                    : ""
                                }
                                ${
                                  task.status === 1
                                    ? `<button class="btn btn-primary btn-sm postToFacebook" data-id="${task.id}"><i class="fa-brands fa-facebook-f"></i> Post</button>`
                                    : ""
                                }
                                <button class="btn btn-outline-danger btn-sm deleteTask" data-id="${
                                  task.id
                                }"><i class="fa fa-times"></i> Delete</button>
                            </td>
                        </tr>`;
        });
        $("#taskList").html(rows);
      });
    }

    // Edit Task
    $(document).on("click", ".editTask", function () {
      $("#task_id").val($(this).data("id"));
      $("#title").val($(this).data("title"));
      $("#description").val($(this).data("description"));
      $("#due_date").val($(this).data("due_date"));

      $("#formTitle").text("Edit Task");
      $("#addTask")
        .text("Save Changes")
        .data("mode", "edit")
        .removeClass("btn-primary")
        .addClass("btn-success");
    });

    // Add task via API
    $("#addTask").click(function () {
      let mode = $(this).data("mode");
      event.preventDefault(); // Prevent the default form submission

      // Check form validation
      let form = $(this).closest("form")[0]; // Get the form
      if (form.checkValidity() === false) {
        // If form is not valid, show invalid feedback
        event.stopPropagation(); // Prevent further execution
        form.classList.add("was-validated"); // Add Bootstrap validation class
      } else {
        let taskData = {
          id: $("#task_id").val(),
          title: $("#title").val(),
          description: $("#description").val(),
          due_date: $("#due_date").val(),
        };

        if (mode === "add") {
          // Add Task
          $.ajax({
            url: "api/tasks.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(taskData),
            success: function () {
              loadTasks();
              resetForm();
              form.classList.remove("was-validated");
              form.reset();
            },
          });
        } else {
          // Update Task
          $.ajax({
            url: "api/tasks.php",
            type: "PUT",
            contentType: "application/json",
            data: JSON.stringify(taskData),
            success: function (response) {
              if (response.error) {
                alert("Error: " + response.error);
              } else {
                loadTasks();
                resetForm();
                form.classList.remove("was-validated");
                form.reset();
                alert(response.message);
              }
            },
            error: function (xhr, status, error) {
              // Handle the error case
              let response = JSON.parse(xhr.responseText); // Get the error message from the response
              if (response.error) {
                // Display the error message (you can show this in a modal, an alert, or a specific section of the page)
                alert("Error: " + response.error);
              }
            },
          });
        }
      }
    });

    function resetForm() {
      $("#task_id").val("");
      $("#title").val("");
      $("#description").val("");
      $("#due_date").val("");
      $("#formTitle").text("Add Task");
      $("#addTask")
        .text("Add Task")
        .data("mode", "add")
        .removeClass("btn-success")
        .addClass("btn-primary");
    }

    // Update task status
    $(document).on("click", ".updateStatus", function () {
      let taskId = $(this).data("id");
      let newStatus = $(this).data("status");

      $.ajax({
        url: "api/tasks.php",
        type: "PUT",
        contentType: "application/json",
        data: JSON.stringify({
          id: taskId,
          status: newStatus,
        }),
        success: function () {
          alert("Task marked as completed!");
          loadTasks();
        },
      });
    });

    // Delete task
    $(document).on("click", ".deleteTask", function () {
      let taskId = $(this).data("id");
      if (confirm("Are you sure you want to delete this task?")) {
        $.ajax({
          url: "api/tasks.php",
          type: "DELETE",
          contentType: "application/json",
          data: JSON.stringify({
            id: taskId,
          }),
          success: function () {
            loadTasks().done(function () {
              alert("Task deleted!");
            });
          },
        });
      }
    });

    // Post to Facebook
    $(document).on("click", ".postToFacebook", function () {
      let taskId = $(this).data("id");

      getTaskById(taskId)
        .then((task) => {
          console.log(task);
          postToFacebook(task);
        })
        .catch((error) => {
          console.error(error); // Handle error
        });
    });
    $(document).on("click", ".shareToFacebook", function () {
      let taskId = $(this).data("id");

      getTaskById(taskId)
        .then((task) => {
          console.log(task);
          shareToFacebook(task);
        })
        .catch((error) => {
          console.error(error);
        });
    });

    // Fetch Task by ID
    function getTaskById(taskId) {
      return new Promise((resolve, reject) => {
        $.ajax({
          url: `api/tasks.php?id=${taskId}`, // Fetch task by ID
          method: "GET",
          dataType: "json",
          success: function (response) {
            if (response.task) {
              resolve(response.task); // Resolve with the task data
            } else {
              reject("Task not found");
            }
          },
          error: function (xhr, status, error) {
            reject("Error fetching task: " + error);
          },
        });
      });
    }

    function shareToFacebook(task) {
      FB.ui(
        {
          method: "share",
          href: "<?= SITE_URL ?>/api/task.php?id=" + task.id, // Replace with your task URL
          quote: `Task Completed: ${task.title}\n Details: ${task.description}\n Due Date: ${task.due_date}`,
        },
        function (response) {
          if (response && !response.error_message) {
            alert("Task successfully shared on Facebook!");
          } else {
            alert("Failed to share task.");
          }
        }
      );
    }

    function postToFacebook(task, pageAccessToken) {
      let message = `Task Completed: ${task.title}\n Details: ${task.description}\n Due Date: ${task.due_date}`;
      loginToFacebook(function (pageAccessToken) {
        $.ajax({
          url: `https://graph.facebook.com/<?= FB_GRAPH_VERSION ?>/<?= FB_PAGE_ID ?>/feed`,
          type: "POST",
          data: {
            message: message,
            access_token: pageAccessToken,
          },
          success: function (response) {
            alert("Task successfully posted on Facebook Page!");
            console.log(response);
          },
          error: function (error) {
            alert("Failed to post task.");
            console.log(error);
          },
        });
      });
    }

    // function postToFacebook(task) {
    //     loginToFacebook(function(pageAccessToken) {
    //         FB.api('/me/feed', 'POST', {
    //             message: `Task Completed: ${task.title}\n Details: ${task.description}\n Due Date: ${task.due_date}`,
    //             access_token: pageAccessToken // Use Page Access Token
    //         }, function(response) {
    //             if (response && !response.error) {
    //                 alert("Task successfully posted to Facebook Page!");
    //             } else {
    //                 console.error("Error posting:", response.error);
    //                 alert("Failed to post task.");
    //             }
    //         });
    //     });
    // }

    function loginToFacebook(callback) {
      FB.getLoginStatus(function (response) {
        if (response.status === "connected") {
          console.log("Logged in!");
          getPageAccessToken(response.authResponse.accessToken, callback);
        } else {
          FB.login(
            function (response) {
              if (response.authResponse) {
                console.log("Login successful!");
                getPageAccessToken(response.authResponse.accessToken, callback);
              } else {
                console.log("User canceled login.");
              }
            },
            {
              scope: "pages_manage_posts,pages_read_engagement",
            }
          );
        }
      });
    }

    function getPageAccessToken(userAccessToken, callback) {
      FB.api(
        "/me/accounts",
        "GET",
        {
          access_token: userAccessToken,
        },
        function (response) {
          if (response.data && response.data.length > 0) {
            let pageAccessToken = response.data[0].access_token; // First page access token
            callback(pageAccessToken);
          } else {
            alert("No pages found. Make sure you are an admin.");
          }
        }
      );
    }
  });
</script>
```

### 🔹 Manage task HTML File (`task/add_task.php`)

```html
<div class="mb-4">
  <form action="" class="needs-validation" novalidate>
    <h4 id="formTitle">Add Task</h4>
    <p class="text-muted">Fields marked with an asterisk (*) are mandatory.</p>
    <!-- Bootstrap vertical form -->
    <input type="hidden" id="task_id" />
    <div class="mb-3">
      <label for="title" class="form-label"
        >Task Title <span class="text-danger">*</span></label
      >
      <input
        type="text"
        id="title"
        class="form-control mb-2"
        placeholder=""
        required
      />
      <div class="invalid-feedback">Please provide a task title.</div>
    </div>
    <div class="mb-3">
      <label for="description" class="form-label">Task Description</label>
      <textarea
        id="description"
        class="form-control mb-2"
        rows="5"
        placeholder=""
      ></textarea>
    </div>
    <div class="mb-3">
      <label for="due_date" class="form-label"
        >Due Date <span class="text-danger">*</span></label
      >
      <input
        type="text"
        id="due_date"
        class="form-control mb-2 datepicker"
        placeholder=""
        required
      />
      <div class="invalid-feedback">Please provide a valid due date.</div>
    </div>
    <button class="btn btn-success" id="addTask" data-mode="add">
      Add Task
    </button>
  </form>
</div>
```

### 🔹 Manage task HTML File (`assets/style.css`)

```css
.login-form {
  border: 1px solid #e9e9e9;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  padding: 20px;
  border-radius: 8px;
  background-color: white;
}
.password-toggle {
  cursor: pointer;
}

body {
  display: flex;
  background-color: #f4f7f9;
}

#sidebar {
  width: 250px;
  background: linear-gradient(135deg, #343a40, #212529);

  height: 100vh;
  padding-top: 20px;
  color: white;
}

#sidebar .nav-link {
  color: white;
  padding: 12px 20px;
  border-left: 3px solid transparent;
}

#sidebar .nav-link:hover,
#sidebar .nav-link.active {
  background-color: rgba(255, 255, 255, 0.1);

  border-left-color: #007bff;
}

#sidebar .nav-link i {
  margin-right: 10px;
}

#content {
  flex-grow: 1;
  padding: 20px;
}

.navbar {
  background-color: white;

  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.dropdown-menu {
  border: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
```
