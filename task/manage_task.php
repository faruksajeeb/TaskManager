<?php
require_once __DIR__ . '/../config/facebook.php';
?>
<h2 class="mb-4">Manage Task</h2>
<hr>
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
window.fbAsyncInit = function() {
    FB.init({
        appId: "<?= FB_APP_ID ?>",
        cookie: true,
        xfbml: true,
        version: "<?= FB_GRAPH_VERSION ?>" // Specify the Facebook Graph API version
    });

    // Check if the user is logged into Facebook
    FB.getLoginStatus(function(response) {
        if (response.status === 'connected') {
            console.log('User is logged in to Facebook');
        } else {
            console.log('User is not logged in to Facebook');
        }
    });
};

// Load the SDK asynchronously
(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {
        return;
    }
    js = d.createElement(s);
    js.id = id;
    js.src = "https://connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

$(function() {

    $(".datepicker").datepicker({
        dateFormat: 'yy-mm-dd'
    });

    loadTasks(); // Load tasks on page load

    // Fetch tasks from API
    function loadTasks() {
        $.getJSON("api/tasks.php", function(data) {
            let rows = "";
            $.each(data, function(index, task) {
                let statusBadge = task.status == 0 ?
                    `<span class="badge bg-warning">Pending</span>` :
                    `<span class="badge bg-success">Completed</span>`;
                rows += `<tr>
                            <td>${task.title}</td>
                            <td>${task.description}</td>
                            <td>${task.due_date}</td>
                            <td class="text-center">${statusBadge}</td>
                            <td class="text-center text-nowrap">
                                ${task.status === 0 ?
                            `<button class="btn btn-outline-primary btn-sm editTask"  
                                data-id="${task.id}" 
                                data-title="${task.title}" 
                                data-description="${task.description}" 
                                data-due_date="${task.due_date}" 
                                data-status="${task.status}"
                                ><i class="fa fa-edit"></i> Edit</button>
                                <button class="btn btn-outline-success btn-sm updateStatus" data-id="${task.id}" data-status="1">Complete</button>`
                            : ''}
                                ${task.status === 1 ?
                            `<button class="btn btn-primary btn-sm postToFacebook" data-id="${task.id}"><i class="fa-brands fa-facebook-f"></i> Post</button>`
                            : ''}
                                <button class="btn btn-outline-danger btn-sm deleteTask" data-id="${task.id}"><i class="fa fa-times"></i> Delete</button>
                            </td>
                        </tr>`;
            });
            $("#taskList").html(rows);
        });
    }

    // Edit Task
    $(document).on("click", ".editTask", function() {
        $("#task_id").val($(this).data("id"));
        $("#title").val($(this).data("title"));
        $("#description").val($(this).data("description"));
        $("#due_date").val($(this).data("due_date"));

        $("#formTitle").text("Edit Task");
        $("#addTask").text("Save Changes").data("mode", "edit").removeClass("btn-primary").addClass(
            "btn-success");
    });

    // Add task via API
    $("#addTask").click(function() {
        let mode = $(this).data("mode");
        event.preventDefault(); // Prevent the default form submission

        // Check form validation
        let form = $(this).closest('form')[0]; // Get the form
        if (form.checkValidity() === false) {
            // If form is not valid, show invalid feedback
            event.stopPropagation(); // Prevent further execution
            form.classList.add('was-validated'); // Add Bootstrap validation class
        } else {

            let taskData = {
                id: $("#task_id").val(),
                title: $("#title").val(),
                description: $("#description").val(),
                due_date: $("#due_date").val()
            };

            if (mode === "add") {
                // Add Task
                $.ajax({
                    url: "api/tasks.php",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify(taskData),
                    success: function() {
                        loadTasks();
                        resetForm();
                        form.classList.remove('was-validated');
                        form.reset();
                    }
                });
            } else {
                // Update Task
                $.ajax({
                    url: "api/tasks.php",
                    type: "PUT",
                    contentType: "application/json",
                    data: JSON.stringify(taskData),
                    success: function(response) {
                        if (response.error) {
                            alert("Error: " + response.error);
                        } else {
                            loadTasks();
                            resetForm();
                            form.classList.remove('was-validated');
                            form.reset();
                            alert(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle the error case
                        let response = JSON.parse(xhr
                            .responseText); // Get the error message from the response
                        if (response.error) {
                            // Display the error message (you can show this in a modal, an alert, or a specific section of the page)
                            alert("Error: " + response.error);
                        }
                    }
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
        $("#addTask").text("Add Task").data("mode", "add").removeClass("btn-success").addClass(
            "btn-primary");
    }

    // Update task status
    $(document).on("click", ".updateStatus", function() {
        let taskId = $(this).data("id");
        let newStatus = $(this).data("status");

        $.ajax({
            url: "api/tasks.php",
            type: "STATUS_COMPLETE",
            contentType: "application/json",
            data: JSON.stringify({
                id: taskId,
                status: newStatus
            }),
            success: function() {
                alert("Task marked as completed!");
                loadTasks();
            }
        });
    });

    // Delete task
    $(document).on("click", ".deleteTask", function() {
        let taskId = $(this).data("id");
        if (confirm("Are you sure you want to delete this task?")) {
            $.ajax({
                url: "api/tasks.php",
                type: "DELETE",
                contentType: "application/json",
                data: JSON.stringify({
                    id: taskId
                }),
                success: function() {
                    loadTasks().done(function() {
                        alert("Task deleted!");
                    });

                }
            });
        }
    });

    // Post to Facebook
    $(document).on('click', '.postToFacebook', function() {
        let taskId = $(this).data('id');

        getTaskById(taskId)
            .then(task => {
                console.log(task);
                postToFacebook(task);
            })
            .catch(error => {
                console.error(error); // Handle error
            });
    });
    $(document).on('click', '.shareToFacebook', function() {
        let taskId = $(this).data('id');

        getTaskById(taskId)
            .then(task => {
                console.log(task);
                shareToFacebook(task);

            })
            .catch(error => {
                console.error(error);
            });
    });


    // Fetch Task by ID
    function getTaskById(taskId) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: `api/tasks.php?id=${taskId}`, // Fetch task by ID
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.task) {
                        resolve(response.task); // Resolve with the task data
                    } else {
                        reject('Task not found');
                    }
                },
                error: function(xhr, status, error) {
                    reject('Error fetching task: ' + error);
                }
            });
        });
    }

    function shareToFacebook(task) {
        FB.ui({
            method: 'share',
            href: '<?= SITE_URL ?>/api/task.php?id=' + task
                .id, // Replace with your task URL
            quote: `Task Completed: ${task.title}\n Details: ${task.description}\n Due Date: ${task.due_date}`
        }, function(response) {
            if (response && !response.error_message) {
                alert("Task successfully shared on Facebook!");
            } else {
                alert("Failed to share task.");
            }
        });
    }

    function postToFacebook(task, pageAccessToken) {
        let message =
            `Task Completed: ${task.title}\n Details: ${task.description}\n Due Date: ${task.due_date}`;
        loginToFacebook(function(pageAccessToken) {
            $.ajax({
                url: `https://graph.facebook.com/<?= FB_GRAPH_VERSION ?>/<?= FB_PAGE_ID ?>/feed`,
                type: 'POST',
                data: {
                    message: message,
                    access_token: pageAccessToken
                },
                success: function(response) {
                    alert("Task successfully posted on Facebook Page!");
                    console.log(response);
                },
                error: function(error) {
                    alert("Failed to post task.");
                    console.log(error);
                }
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
        FB.getLoginStatus(function(response) {
            if (response.status === 'connected') {
                console.log("Logged in!");
                getPageAccessToken(response.authResponse.accessToken, callback);
            } else {
                FB.login(function(response) {
                    if (response.authResponse) {
                        console.log("Login successful!");
                        getPageAccessToken(response.authResponse.accessToken, callback);
                    } else {
                        console.log("User canceled login.");
                    }
                }, {
                    scope: 'pages_manage_posts,pages_read_engagement'
                });
            }
        });
    }

    function getPageAccessToken(userAccessToken, callback) {
        FB.api('/me/accounts', 'GET', {
            access_token: userAccessToken
        }, function(response) {
            if (response.data && response.data.length > 0) {
                let pageAccessToken = response.data[0].access_token; // First page access token
                callback(pageAccessToken);
            } else {
                alert("No pages found. Make sure you are an admin.");
            }
        });
    }
});
</script>