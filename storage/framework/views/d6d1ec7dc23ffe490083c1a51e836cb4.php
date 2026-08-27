<?php
    $label = fn ($v) => $v ? ucwords(strtolower(str_replace('_', ' ', $v))) : '-';
?>

<?php $__env->startSection('content'); ?>
    <div class="section-header">
        <h1>Assets In Use</h1>
        <div class="ml-auto">
            <a href="<?php echo e(route('assets.index')); ?>" class="btn btn-primary">
                <i class="fa fa-cubes"></i> All Assets
            </a>
        </div>
    </div>

    <div class="section-body">
        <?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?>

        <div class="card card-primary">
            <div class="card-header">
                <h4>Open Handovers</h4>
                <div class="card-header-action">
                    <span class="badge badge-primary"><?php echo e($assignments->count()); ?> assigned</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table_id" class="table table-bordered table-hover table-striped table-sm">
                        <thead>
                            <tr>
                                <th width="4%">No</th>
                                <th>Asset</th>
                                <th>Category</th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Location</th>
                                <th>Assigned On</th>
                                <th>Condition</th>
                                <th>Handed By</th>
                                <th width="8%">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($loop->iteration); ?></td>
                                    <td>
                                        <b><?php echo e($row->asset->asset_code ?? '-'); ?></b><br>
                                        <small class="text-muted"><?php echo e($row->asset->asset_name ?? ''); ?></small>
                                    </td>
                                    <td><?php echo e($row->asset->category->category_name ?? '-'); ?></td>
                                    <td>
                                        <?php echo e($row->employee->name ?? '-'); ?><br>
                                        <small class="text-muted"><?php echo e($row->employee->employee_code ?? ''); ?></small>
                                    </td>
                                    <td><?php echo e($row->employee->department->name ?? '-'); ?></td>
                                    <td><?php echo e($row->location->location_name ?? ($row->asset->location->location_name ?? '-')); ?></td>
                                    <td><?php echo e(optional($row->assigned_at)->format('d M Y')); ?></td>
                                    <td><?php echo e($label($row->condition_on_assign)); ?></td>
                                    <td><?php echo e($row->handler->name ?? '-'); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('assets.show', $row->asset_id)); ?>"
                                           class="btn btn-success btn-sm">Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="10" class="text-center">No asset is currently assigned.</td>
                                </tr>
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

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/assignments/index.blade.php ENDPATH**/ ?>