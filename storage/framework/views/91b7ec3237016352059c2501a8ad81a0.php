<?php $__env->startSection('content'); ?>
    <div class="section-header">
        <h1>Account Activity Log</h1>
    </div>

    <div class="section-body">

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" class="form-inline">
                    <select name="action" class="form-control mr-2">
                        <option value="">-- All Actions --</option>
                        <?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($action); ?>"
                                <?php echo e(request('action') == $action ? 'selected' : ''); ?>>
                                <?php echo e($action); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <select name="table" class="form-control mr-2">
                        <option value="">-- All Tables --</option>
                        <?php $__currentLoopData = $tables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($table); ?>"
                                <?php echo e(request('table') == $table ? 'selected' : ''); ?>>
                                <?php echo e($table); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <button class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>

        <div class="card card-primary">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table_log"
                           class="table table-bordered table-hover table-striped table-condensed">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Table</th>
                                <th>Row ID</th>
                                <th>IP</th>
                                <th>Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($log->occurred_at->format('d-m-Y H:i')); ?></td>
                                    <td><?php echo e($log->user_name); ?></td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo e($log->action); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($log->table_name); ?></td>
                                    <td><?php echo e($log->row_id ?? '-'); ?></td>
                                    <td><?php echo e($log->ip_address); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('audit.show', $log->id)); ?>"
                                           class="btn btn-sm btn-success">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script>
        $(document).ready(function () {
            $('#table_log').DataTable({
                order: [[0, 'desc']],
                pageLength: 10,
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/audit/index.blade.php ENDPATH**/ ?>