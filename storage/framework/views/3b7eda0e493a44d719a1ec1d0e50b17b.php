<?php $__env->startSection('content'); ?>
    <div class="section-header">
        <h1>Incoming Issue Reports</h1>
    </div>

    <div class="section-body">
        <?php if(session()->has('success')): ?>
            <div class="alert alert-success" role="alert">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col">
                <div class="card card-primary">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table_id"
                                class="table table-bordered table-hover table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Asset Name</th>
                                        <th>Location</th>
                                        <th>View</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $issueReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $issueReport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($loop->iteration); ?></td>
                                            <td><?php echo e($issueReport->title); ?></td>
                                            <td>
                                                <?php if($issueReport->status === 'Pending'): ?>
                                                    <span class="badge badge-warning m-2">Pending</span>
                                                <?php elseif($issueReport->status === 'In Review'): ?>
                                                    <span class="badge badge-primary m-2">Under Repair</span>
                                                <?php elseif($issueReport->status === 'Completed'): ?>
                                                    <span class="badge badge-success m-2">Completed</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary m-2">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($issueReport->asset->asset_name ?? '-'); ?></td>
                                            <td><?php echo e($issueReport->asset->location->location_name ?? '-'); ?></td>
                                            <td>
                                                <a href="/incoming-reports/detail/<?php echo e($issueReport->id); ?>"
                                                    class="btn btn-success btn-sm">Detail</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#table_id').DataTable();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/incoming-reports/index.blade.php ENDPATH**/ ?>