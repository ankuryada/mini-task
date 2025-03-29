<!DOCTYPE html>
<html>

<head>
    <title>Mini Task Management System</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .error-text {
            font-size: 0.875em;
            color: #e3342f;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h1>Task Management System</h1>

        <!-- Form to add a new task -->
        <div class="card mb-4">
            <div class="card-header">Add New Task</div>
            <div class="card-body">
                <form id="taskForm">
                    <div class="form-group">
                        <label for="title">Title*</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Task Title">
                        <span class="error-text" id="title_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="description">Description (Optional)</label>
                        <textarea class="form-control" id="description" name="description" placeholder="Task Description"></textarea>
                        <span class="error-text" id="description_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="priority">Priority*</label>
                        <select class="form-control" id="priority" name="priority">
                            <option value="">Select Priority</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                        <span class="error-text" id="priority_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="due_date">Due Date*</label>
                        <input type="date" class="form-control" id="due_date" name="due_date">
                        <span class="error-text" id="due_date_error"></span>
                    </div>
                    <div class="form-group">
                        <label for="status">Status*</label>
                        <select class="form-control" id="status" name="status">
                            <option value="Pending">Pending</option>
                            <option value="Completed">Completed</option>
                        </select>
                        <span class="error-text" id="status_error"></span>
                    </div>
                    <input type="hidden" id="task_id">

                    <button type="submit" class="btn btn-primary">Add Task</button>
                </form>
            </div>
        </div>

        <!-- Task List -->
        <div class="card">
            <div class="card-header">Tasks</div>
            <div class="card-body">
                <table class="table" id="tasksTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Priority</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Tasks will be loaded here via AJAX -->
                    </tbody>
                </table>
                <!-- Pagination Links -->
                <nav>
                    <ul class="pagination" id="pagination"></ul>
                </nav>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            // Set CSRF Token for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Function to fetch tasks with pagination
            function fetchTasks(page = 1) {
                $.ajax({
                    url: `/api/tasks?page=${page}`,
                    type: 'GET',
                    success: function(data) {
                        var tbody = '';
                        $.each(data.data, function(index, task) {
                            tbody += `<tr>
                                <td>${task.id}</td>
                                <td>${task.title}</td>
                                <td>${task.priority}</td>
                                <td>${task.due_date}</td>
                                <td>${task.status}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-task" data-id="${task.id}" data-title="${task.title}" data-description="${task.description}" data-priority="${task.priority}" data-due_date="${task.due_date}" data-status="${task.status}">Edit</button>
                                    <button class="btn btn-danger btn-sm delete-task" data-id="${task.id}">Delete</button>
                                </td>
                            </tr>`;
                        });
                        $('#tasksTable tbody').html(tbody);

                        // Build pagination links
                        var pagination = '';
                        for (var i = 1; i <= data.last_page; i++) {
                            pagination += `<li class="page-item ${data.current_page == i ? 'active' : ''}">
                                <a class="page-link" href="#" data-page="${i}">${i}</a>
                            </li>`;
                        }
                        $('#pagination').html(pagination);
                    }
                });
            }

            // Initial fetch of tasks
            fetchTasks();

            // Pagination link click event
            $(document).on('click', '.page-link', function(e) {
                e.preventDefault();
                var page = $(this).data('page');
                fetchTasks(page);
            });

            // Clear error messages on input change
            $('#taskForm input, #taskForm textarea, #taskForm select').on('input change', function() {
                $('#' + this.name + '_error').text('');
            });

            // Handle form submission for adding a new task
            $('#taskForm').on('submit', function(e) {
                e.preventDefault();
                $('.error-text').text('');

                var taskId = $('#task_id').val();
                var formData = {
                    title: $('#title').val(),
                    description: $('#description').val(),
                    priority: $('#priority').val(),
                    due_date: $('#due_date').val(),
                    status: $('#status').val()
                };

                let url = '';
                let method = '';
                let successMessage = '';

                if (taskId) {
                    // Update
                    url = `/api/tasks/${taskId}`;
                    method = 'PUT';
                    successMessage = 'Task updated successfully!';
                } else {
                    // Create
                    url = '/api/tasks';
                    method = 'POST';
                    successMessage = 'Task added successfully!';
                }

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    success: function(data) {
                        alert(successMessage);
                        $('#taskForm')[0].reset();
                        $('#task_id').val('');
                        $('#taskForm button[type="submit"]').text('Add Task');
                        fetchTasks();
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON;
                        $.each(errors, function(key, messages) {
                            $('#' + key + '_error').text(messages[0]);
                        });
                    }
                });
            });

            // Edit task
            $(document).on('click', '.edit-task', function() {
                $('#task_id').val($(this).data('id'));
                $('#title').val($(this).data('title'));
                $('#description').val($(this).data('description'));
                $('#priority').val($(this).data('priority'));
                $('#due_date').val($(this).data('due_date'));
                $('#status').val($(this).data('status'));

                // Scroll to form
                $('html, body').animate({
                    scrollTop: $("#taskForm").offset().top
                }, 500);

                // Change button text
                $('#taskForm button[type="submit"]').text('Update Task');
            });

            // task deletion
            $(document).on('click', '.delete-task', function() {
                if (confirm("Are you sure you want to delete this task?")) {
                    var id = $(this).data('id');
                    $.ajax({
                        url: `/api/tasks/${id}`,
                        type: 'DELETE',
                        success: function(data) {
                            alert(data.message);
                            fetchTasks();
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>