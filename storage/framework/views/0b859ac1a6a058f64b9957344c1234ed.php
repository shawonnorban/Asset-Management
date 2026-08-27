<?php $__env->startSection('content'); ?>
<div class="section-header">
    <h1>Asset Depreciation</h1>
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
            <h4>Assets & Depreciation</h4>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="table-depreciation">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Asset Code</th>
                            <th>Asset Name</th>
                            <th>Method</th>
                            <th>Acquisition Cost</th>
                            <th>Latest Book Value</th>
                            <th>Latest Period</th>
                            <th>Status</th>
                            <th width="180">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $last = $asset->monthlyDepreciations->first();
                                $setting = $asset->depreciationSetting;
                            ?>
                            <tr>
                                <td><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($asset->asset_code); ?></td>
                                <td><?php echo e($asset->asset_name); ?></td>
                                <td>
                                    <?php echo e($setting?->method ?? '-'); ?>

                                </td>
                                <td>
                                    <?php echo e($setting ? number_format($setting->acquisition_cost, 0, ',', '.') : '-'); ?>

                                </td>
                                <td>
                                    <?php echo e($last ? number_format($last->ending_book_value, 0, ',', '.') : '-'); ?>

                                </td>
                                <td>
                                    <?php echo e($last?->period ?? '-'); ?>

                                </td>
                                <td>
                                    <?php if(!$setting): ?>
                                        <span class="badge badge-secondary">Not Configured</span>
                                    <?php elseif($setting->is_disposed): ?>
                                        <span class="badge badge-danger">Disposed</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('depreciation.show', $asset->id)); ?>"
                                       class="btn btn-info btn-sm">
                                        Detail
                                    </a>

                                    <?php if(auth()->user()->inRoles(['admin','manager']) && $setting && !$setting->is_disposed): ?>
                                        <form action="<?php echo e(route('depreciation.depreciate', $asset->id)); ?>"
                                              method="POST"
                                              class="d-inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit"
                                                    class="btn btn-warning btn-sm"
                                                    onclick="return confirm('Generate this month's depreciation?')">
                                                Depreciate
                                            </button>
                                        </form>
                                    <?php endif; ?>
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
        $('#table-depreciation').DataTable();
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/depreciation/index.blade.php ENDPATH**/ ?>