<?php
    $label = fn ($v) => $v ? ucwords(strtolower(str_replace('_', ' ', $v))) : '-';
?>

<?php $__env->startSection('content'); ?>
    <div class="section-header">
        <h1>Software Licenses</h1>
        <div class="ml-auto">
            <a href="<?php echo e(route('software-licenses.create')); ?>" class="btn btn-primary">
                <i class="fa fa-plus"></i> Add License
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
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table_id" class="table table-bordered table-hover table-striped table-sm">
                        <thead>
                            <tr>
                                <th width="4%">No</th>
                                <th>Software</th>
                                <th>Publisher</th>
                                <th>Type</th>
                                <th>Seats</th>
                                <th>Expiry</th>
                                <th width="16%">Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $licenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $license): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $free = $license->seats_total - $license->seats_in_use;
                                ?>
                                <tr>
                                    <td><?php echo e($loop->iteration); ?></td>
                                    <td>
                                        <b><?php echo e($license->name); ?></b>
                                        <?php if($license->version): ?>
                                            <small class="text-muted"><?php echo e($license->version); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($license->publisher ?? '-'); ?></td>
                                    <td><?php echo e($label($license->license_type)); ?></td>
                                    <td>
                                        <?php echo e($license->seats_in_use); ?> / <?php echo e($license->seats_total); ?>

                                        <span class="badge <?php echo e($free > 0 ? 'badge-success' : 'badge-danger'); ?>">
                                            <?php echo e($free > 0 ? $free . ' free' : 'full'); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <?php if($license->expiry_date): ?>
                                            <?php echo e($license->expiry_date->format('d M Y')); ?>

                                            <?php if($license->isExpired()): ?>
                                                <span class="badge badge-danger">expired</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo e(route('software-licenses.show', $license->id)); ?>"
                                           class="btn btn-success btn-sm">Detail</a>
                                        <a href="<?php echo e(route('software-licenses.edit', $license->id)); ?>"
                                           class="btn btn-warning btn-sm">Edit</a>
                                        <form id="delete-license-<?php echo e($license->id); ?>"
                                              action="<?php echo e(route('software-licenses.destroy', $license->id)); ?>"
                                              method="POST" class="d-inline">
                                            <?php echo method_field('DELETE'); ?>
                                            <?php echo csrf_field(); ?>
                                            <button type="button" class="btn btn-danger btn-sm swal-confirm"
                                                    data-form="delete-license-<?php echo e($license->id); ?>">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="7" class="text-center">No software license recorded yet.</td></tr>
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

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/software-licenses/index.blade.php ENDPATH**/ ?>