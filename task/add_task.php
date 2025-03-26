<div class="mb-4">
    <form action="" class="needs-validation" novalidate>
        <h4 id="formTitle">Add Task</h4>
        <p class="text-muted">Fields marked with an asterisk (*) are mandatory.</p>
        <!-- Bootstrap vertical form -->
        <input type="hidden" id="task_id">
        <div class="mb-3">
            <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
            <input type="text" id="title" class="form-control mb-2" placeholder="" required>
            <div class="invalid-feedback">Please provide a task title.</div>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Task Description</label>
            <textarea id="description" class="form-control mb-2" rows="5" placeholder=""></textarea>

        </div>
        <div class="mb-3">
            <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
            <input type="text" id="due_date" class="form-control mb-2 datepicker" placeholder="" required>
            <div class="invalid-feedback">Please provide a valid due date.</div>
        </div>
        <button class="btn btn-success" id="addTask" data-mode="add">Add Task</button>
    </form>

</div>