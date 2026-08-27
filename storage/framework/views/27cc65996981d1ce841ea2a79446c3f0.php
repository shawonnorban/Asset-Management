<?php $__env->startSection('content'); ?>
    <div class="section-header">
        <h1>Departments</h1>
        <div class="ml-auto">
            <a href="<?php echo e(route('employees.index')); ?>" class="btn btn-light mr-2">
                <i class="fa fa-id-card"></i> Employees
            </a>
            <a href="<?php echo e(route('departments.create')); ?>" class="btn btn-primary">
                <i class="fa fa-plus"></i> Add Department
            </a>
        </div>
    </div>

    <div class="section-body">
        <?php if(session()->has('success')): ?>
            <div class="alert alert-success" role="alert"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session()->has('error')): ?>
            <div class="alert alert-danger" role="alert"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="card card-primary">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table_id" class="table table-bordered table-hover table-striped table-sm">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Department</th>
                                <th width="15%">Employees</th>
                                <th width="20%">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($loop->iteration); ?></td>
                                    <td><?php echo e($department->name); ?></td>
                                    <td><?php echo e($department->employees_count); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('departments.edit', $department->id)); ?>"
                                           class="btn btn-warning btn-sm">Edit</a>

                                        <form id="delete<?php echo e($department->id); ?>"
                                              action="<?php echo e(route('departments.destroy', $department->id)); ?>"
                                              method="POST" class="d-inline">
                                            <?php echo method_field('delete'); ?>
                                            <?php echo csrf_field(); ?>
                                            <button type="button" class="btn btn-danger btn-sm swal-confirm"
                                                    data-form="delete<?php echo e($department->id); ?>">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="4" class="text-center">No department recorded yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('#table_id').DataTable();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/departments/index.blade.php ENDPATH**/ ?>