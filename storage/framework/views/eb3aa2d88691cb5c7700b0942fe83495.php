<?php $__env->startSection('content'); ?>
<div class="section-header">
    <h1>Dashboard</h1>
</div>

<div class="section-body">


<?php if(auth()->user()->role->role === 'admin'): ?>

    
    <div class="row">

        
        <div class="col-lg-3">
            <div class="card card-primary">
                <div class="card-header">Total Assets</div>
                <div class="card-body">
                    <p><?php echo e($totalAssets ?? 0); ?> Inventory</p>
                </div>
            </div>
        </div>

        
        <div class="col-lg-3">
            <div class="card card-danger">
                <div class="card-header">Total Locations</div>
                <div class="card-body">
                    <p><?php echo e($totalLocations ?? 0); ?> Location</p>
                </div>
            </div>
        </div>

        
        <div class="col-lg-3">
            <div class="card card-warning">
                <div class="card-header">Total Accounts</div>
                <div class="card-body">
                    <p><?php echo e($totalAccounts ?? 0); ?> Account</p>
                </div>
            </div>
        </div>

        
        <div class="col-lg-3">
            <div class="card card-success">
                <div class="card-header">Reports In Progress</div>
                <div class="card-body">
                    <p><?php echo e($pelaporanProses ?? 0); ?> Report</p>
                </div>
            </div>
        </div>

    </div>

    
    <div class="row">

        
        <div class="col-lg-6">
            <div class="card card-warning">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Account Activity</span>
                    <a href="<?php echo e(route('audit.index') ?? '#'); ?>" class="btn btn-warning btn-sm">
                        Audit Log
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Account</th>
                                <th>Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $auditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($loop->iteration); ?></td>
                                    <td>
                                        <?php echo e($log->user_name ?? 'System'); ?>

                                    </td>
                                    <td>
                                        <?php echo e(optional($log->occurred_at)->format('d-m-Y H:i')); ?>

                                    </td>
                                    <td>
                                        <?php if($log->action === 'CREATE'): ?>
                                            <span class="badge badge-success">CREATE</span>
                                        <?php elseif($log->action === 'UPDATE'): ?>
                                            <span class="badge badge-info">UPDATE</span>
                                        <?php elseif($log->action === 'DELETE'): ?>
                                            <span class="badge badge-danger">DELETE</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?php echo e($log->action); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        No system activity yet
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        
        <div class="col-lg-6">
            <div class="card card-primary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Account Status</span>
                    <a href="#" class="btn btn-primary btn-sm">
                        Status Login
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Account</th>
                                <th>Status</th>
                                <th>Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $usersStatus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($loop->iteration); ?></td>
                                    <td>
                                        <?php echo e($user->name); ?>

                                        <br>
                                        <small class="text-muted"><?php echo e($user->role->role ?? '-'); ?></small>
                                    </td>
                                    <td>
                                        <?php if($user->is_online): ?>
                                            <span class="badge badge-success">Online</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Offline</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo e($user->last_login_at
                                            ? $user->last_login_at->format('d M Y H:i')
                                            : '-'); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        No account data
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>



<?php elseif(auth()->user()->role->role === 'staff'): ?>

<div class="row">

    
    <div class="col-lg-3">
        <div class="card card-primary">
            <div class="card-header">Total Assets</div>
            <div class="card-body">
                <p><?php echo e($totalAssets ?? 0); ?> Asset</p>
            </div>
        </div>
    </div>

    
    <div class="col-lg-3">
        <div class="card card-danger">
            <div class="card-header">Total Locations</div>
            <div class="card-body">
                <p><?php echo e($totalLocations ?? 0); ?> Location</p>
            </div>
        </div>
    </div>

    
    <div class="col-lg-3">
        <div class="card card-warning">
            <div class="card-header">Total Categories</div>
            <div class="card-body">
                <p><?php echo e($totalCategories ?? 0); ?> Category</p>
            </div>
        </div>
    </div>

    
    <div class="col-lg-3">
        <div class="card card-success">
            <div class="card-header">Total Stock Takes</div>
            <div class="card-body">
                <p><?php echo e($totalStockTakes ?? 0); ?> Stock Take</p>
            </div>
        </div>
    </div>

</div>
<div class="row">
    <div class="col">
        <div class="card card-primary">

            <div class="card-header">
                Report Status Inventory
                <div class="ml-auto">
                    <a href="<?php echo e(url('/review-reports')); ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-search"></i> Check All
                    </a>
                </div>
            </div>

            <div class="card-body">

                <?php if($staffReports->isEmpty()): ?>
                    <div class="alert alert-info mb-0">
                        You have not submitted any reports yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Title</th>
                                    <th>Asset Name</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $staffReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="text-center"><?php echo e($loop->iteration); ?></td>
                                        <td><?php echo e($row->title); ?></td>
                                        <td><?php echo e($row->asset->asset_name ?? '-'); ?></td>
                                        <td class="text-center">
                                            <?php if($row->status === 'Pending'): ?>
                                                <span class="badge badge-warning">Pending</span>
                                            <?php elseif(in_array($row->status, ['In Progress','In Review'])): ?>
                                                <span class="badge badge-info">In Progress</span>
                                            <?php elseif($row->status === 'Completed'): ?>
                                                <span class="badge badge-success">Completed</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><?php echo e($row->status); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php echo e($row->asset->location->location_name ?? '-'); ?>

                                        </td>
                                        <td class="text-center">
                                            <?php echo e($row->created_at->format('d-m-Y')); ?>

                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </div>
</div>


<?php elseif(auth()->user()->role->role === 'manager'): ?>

<div class="row">

    <div class="col-lg-3">
        <div class="card card-primary">
            <div class="card-header">Total Assets</div>
            <div class="card-body">
                <p><?php echo e($totalAssets ?? 0); ?> Asset</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card card-danger">
            <div class="card-header">Total Locations</div>
            <div class="card-body">
                <p><?php echo e($totalLocations ?? 0); ?> Location</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card card-warning">
            <div class="card-header">Depreciated Assets</div>
            <div class="card-body">
                <p><?php echo e($totalDepreciations ?? 0); ?> Asset</p>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card card-success">
            <div class="card-header">Total Stock Takes</div>
            <div class="card-body">
                <p><?php echo e($totalStockTakes ?? 0); ?> Stock Take</p>
            </div>
        </div>
    </div>

</div>
<div class="row">
    <div class="col-lg-6">
        <div class="card card-warning">
            <div class="card-header">
                Incoming Reports
                <div class="ml-auto">
                    <a href="<?php echo e(url('/incoming-reports')); ?>" class="btn btn-warning btn-sm">
                        View All
                    </a>
                </div>
            </div>

            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead class="text-center">
                        <tr>
                            <th>No</th>
                            <th>Title</th>
                            <th>Asset</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $incomingReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-center"><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($row->title); ?></td>
                                <td><?php echo e($row->asset->asset_name ?? '-'); ?></td>
                                <td class="text-center">
                                    <?php if($row->status === 'Pending'): ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php elseif(in_array($row->status, ['In Progress','In Review'])): ?>
                                        <span class="badge badge-info">In Progress</span>
                                    <?php elseif($row->status === 'Completed'): ?>
                                        <span class="badge badge-success">Completed</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><?php echo e($row->status); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    No reports
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
   <div class="col-lg-6">
    <div class="card card-primary">
        <div class="card-header">
            Asset Depreciation
            <div class="ml-auto">
                <a href="<?php echo e(route('depreciation.index')); ?>" class="btn btn-primary btn-sm">
                    Detail Depreciation
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="text-center">
                        <tr>
                            <th>No</th>
                            <th>Asset</th>
                            <th>Latest Book Value</th>
                            <th>Latest Period</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $latestDepreciations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $last = $asset->monthlyDepreciations->first();
                            ?>
                            <tr>
                                <td class="text-center"><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($asset->asset_name); ?></td>
                                <td class="text-right">
                                    Rp <?php echo e(number_format($last->ending_book_value ?? 0, 0, ',', '.')); ?>

                                </td>
                                <td class="text-center">
                                    <?php echo e(isset($last->period) 
                                        ? \Carbon\Carbon::parse($last->period)->format('m-Y') 
                                        : '-'); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    No depreciation data yet
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

</div>

<?php endif; ?>

</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/home.blade.php ENDPATH**/ ?>