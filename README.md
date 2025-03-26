# Task Manager with Social Media Integration

## Overview

This is a simple Task Management System built with PHP and MySQL using Object-Oriented Programming (OOP). Users can log in, manage tasks (CRUD operations), and share completed tasks on Facebook.

## Features

- **User Authentication** (Login with session management)
- **Task Management** (Create, Read, Update, Delete tasks)
- **Social Media Integration**:
  - Post completed tasks to **Facebook** (via Facebook Graph API)
- **AJAX-Based CRUD Operations** (No page reload)
- **Bootstrap UI** (Optional: Responsive design)
- **REST API** (Optional: Manage tasks via API)

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

```php
 define('DB_HOST', 'localhost');
 define('DB_NAME', 'task_manager');
 define('DB_USER', 'root');
 define('DB_PASS', 'your_password');
```

### 3. Start the Server

For PHP's built-in server, run:

```sh
 php -S localhost:8000
```

Then, open [http://localhost:8000](http://localhost:8000) in your browser.

---

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

## License

This project is open-source under the **MIT License**.
