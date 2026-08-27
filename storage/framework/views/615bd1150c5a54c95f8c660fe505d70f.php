<?php $__env->startSection('content'); ?>
    <div class="section-header">
        <h1>Employees</h1>
        <?php if(auth()->user()->inRoles(['admin', 'manager'])): ?>
            <div class="ml-auto">
                <a href="<?php echo e(route('departments.index')); ?>" class="btn btn-light mr-2">
                    <i class="fa fa-sitemap"></i> Departments
                </a>
                <a href="<?php echo e(route('positions.index')); ?>" class="btn btn-light mr-2">
                    <i class="fa fa-user-tag"></i> Positions
                </a>
                <a href="<?php echo e(route('employees.create')); ?>" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Add Employee
                </a>
            </div>
        <?php endif; ?>
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

                <form method="GET" class="form-row align-items-end mb-3">
                    <div class="col-md-3">
                        <label>Department</label>
                        <select name="department_id" class="form-control">
                            <option value="">-- All Departments --</option>
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($department->id); ?>"
                                    <?php echo e(request('department_id') == $department->id ? 'selected' : ''); ?>>
                                    <?php echo e($department->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Location</label>
                        <select name="location_id" class="form-control">
                            <option value="">-- All Locations --</option>
                            <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($location->id); ?>"
                                    <?php echo e(request('location_id') == $location->id ? 'selected' : ''); ?>>
                                    <?php echo e($location->location_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button>
                        <a href="<?php echo e(route('employees.index')); ?>" class="btn btn-light">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table id="table_id" class="table table-bordered table-hover table-striped table-sm">
                        <thead>
                            <tr>
                                <th width="4%">No</th>
                                <th width="7%">Photo</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Location</th>
                                <th>Mobile</th>
                                <th>Mail Address</th>
                                <th>Join Date</th>
                                <th width="16%">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($loop->iteration); ?></td>
                                    <td class="text-center">
                                        <?php if($employee->image && Storage::disk('public')->exists($employee->image)): ?>
                                            <img src="<?php echo e(Storage::url($employee->image)); ?>" alt="employee photo"
                                                 style="width:48px; height:48px; object-fit:cover; border-radius:50%;">
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><b><?php echo e($employee->employee_code); ?></b></td>
                                    <td><?php echo e($employee->name); ?></td>
                                    <td><?php echo e($employee->department->name ?? '-'); ?></td>
                                    <td><?php echo e($employee->position->name ?? '-'); ?></td>
                                    <td><?php echo e($employee->location->location_name ?? '-'); ?></td>
                                    <td><?php echo e($employee->mobile ?? '-'); ?></td>
                                    <td><?php echo e($employee->mail_address ?? '-'); ?></td>
                                    <td><?php echo e(optional($employee->join_date)->format('d M Y') ?? '-'); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('employees.show', $employee->id)); ?>"
                                           class="btn btn-success btn-sm">Detail</a>

                                        <?php if(auth()->user()->inRoles(['admin', 'manager'])): ?>
                                            <a href="<?php echo e(route('employees.edit', $employee->id)); ?>"
                                               class="btn btn-warning btn-sm">Edit</a>

                                            <form id="delete<?php echo e($employee->id); ?>"
                                                  action="<?php echo e(route('employees.destroy', $employee->id)); ?>"
                                                  method="POST" class="d-inline">
                                                <?php echo method_field('delete'); ?>
                                                <?php echo csrf_field(); ?>
                                                <button type="button" class="btn btn-danger btn-sm swal-confirm"
                                                        data-form="delete<?php echo e($employee->id); ?>">
                                                    Delete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="11" class="text-center">No employee recorded yet.</td></tr>
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

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/employees/index.blade.php ENDPATH**/ ?>