        <!-- DELETE TASK MODAL -->
        <div class="modal fade" id="deleteTaskModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title text-danger">Delete Task</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p>
                            This action cannot be undone.<br>
                            To confirm deletion, type <strong>Delete</strong> below:
                        </p>

                        <input type="text" id="confirmDeleteInput" class="form-control"
                            placeholder="Type Delete to confirm">

                        <input type="hidden" id="deleteTaskId">
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button class="btn btn-danger" id="confirmDeleteBtn" disabled>
                            Delete Task
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <!-- </section> -->
        </div>
        </div>