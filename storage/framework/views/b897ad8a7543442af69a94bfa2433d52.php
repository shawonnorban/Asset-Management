<?php
    $statusColors = [
        'IN_USE' => 'badge-success',
        'IN_STORAGE' => 'badge-secondary',
        'UNDER_REPAIR' => 'badge-warning',
        'RETIRED' => 'badge-dark',
        'DISPOSED' => 'badge-danger',
    ];
    $label = fn ($v) => $v ? ucwords(strtolower(str_replace('_', ' ', $v))) : '-';
?>

<?php $__env->startSection('content'); ?>
    <div class="section-header">
        <h1>Employee Detail</h1>
        <div class="ml-auto">
            <?php if(auth()->user()->inRoles(['admin', 'manager'])): ?>
                <a href="<?php echo e(route('employees.edit', $employee->id)); ?>" class="btn btn-warning">
                    <i class="fa fa-edit"></i> Edit
                </a>
            <?php endif; ?>
            <a href="<?php echo e(route('employees.index')); ?>" class="btn btn-primary">
                <i class="fa fa-arrow-left"></i> Back
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

        <div class="row">

            
            <div class="col-lg-4">

                <div class="card card-primary">
                    <div class="card-body text-center">
                        <?php if($employee->image && Storage::disk('public')->exists($employee->image)): ?>
                            <img src="<?php echo e(Storage::url($employee->image)); ?>" alt="employee photo"
                                 class="img-fluid mb-3"
                                 style="width:180px; height:180px; object-fit:cover; border-radius:50%;">
                        <?php else: ?>
                            <div class="mb-3 mx-auto d-flex align-items-center justify-content-center"
                                 style="width:180px; height:180px; border-radius:50%; background:#f2f3f7; color:#999;">
                                <i class="fa fa-user fa-4x"></i>
                            </div>
                        <?php endif; ?>

                        <h4 class="mb-1"><?php echo e($employee->name); ?></h4>
                        <div class="text-muted"><?php echo e($employee->position->name ?? '-'); ?></div>
                        <div class="text-muted">
                            <small><?php echo e($employee->department->name ?? '-'); ?></small>
                        </div>
                        <span class="badge badge-primary mt-2"><?php echo e($employee->employee_code); ?></span>
                    </div>
                </div>

                <div class="card card-primary">
                    <div class="card-header"><h4>Contact</h4></div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th width="110">Mobile</th><td><?php echo e($employee->mobile ?? '-'); ?></td></tr>
                            <tr><th>Mail Address</th>
                                <td>
                                    <?php if($employee->mail_address): ?>
                                        <a href="mailto:<?php echo e($employee->mail_address); ?>"><?php echo e($employee->mail_address); ?></a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td></tr>
                            <tr><th>Location</th><td><?php echo e($employee->location->location_name ?? '-'); ?></td></tr>
                            <tr><th>Join Date</th>
                                <td><?php echo e(optional($employee->join_date)->format('d M Y') ?? '-'); ?></td></tr>
                        </table>
                    </div>
                </div>

            </div>

            
            <div class="col-lg-8">

                <div class="card card-primary">
                    <div class="card-header"><h4>Personal</h4></div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th width="180">Father's Name</th><td><?php echo e($employee->father_name ?? '-'); ?></td></tr>
                            <tr><th>Mother's Name</th><td><?php echo e($employee->mother_name ?? '-'); ?></td></tr>
                            <tr><th>NID Number</th><td><?php echo e($employee->nid_number ?? '-'); ?></td></tr>
                            <tr><th>Present Address</th><td><?php echo e($employee->present_address ?? '-'); ?></td></tr>
                            <tr><th>Permanent Address</th><td><?php echo e($employee->permanent_address ?? '-'); ?></td></tr>
                        </table>
                    </div>
                </div>

                
                <div class="card card-primary">
                    <div class="card-header">
                        <h4>Assets Currently Held</h4>
                        <div class="card-header-action">
                            <span class="badge badge-primary"><?php echo e($employee->assets->count()); ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th width="8%">Image</th>
                                        <th>Asset Code</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $employee->assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="text-center">
                                                <?php if($asset->image && Storage::disk('public')->exists($asset->image)): ?>
                                                    <img src="<?php echo e(Storage::url($asset->image)); ?>" alt="asset image"
                                                         style="width:44px; height:44px; object-fit:cover; border-radius:4px;">
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo e(route('assets.show', $asset->id)); ?>">
                                                    <?php echo e($asset->asset_code); ?>

                                                </a>
                                            </td>
                                            <td><?php echo e($asset->asset_name); ?></td>
                                            <td><?php echo e($asset->category->category_name ?? '-'); ?></td>
                                            <td><?php echo e($asset->location->location_name ?? '-'); ?></td>
                                            <td>
                                                <span class="badge <?php echo e($statusColors[$asset->status] ?? 'badge-secondary'); ?>">
                                                    <?php echo e($label($asset->status)); ?>

                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                No asset is assigned to this employee.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                
                <div class="card card-primary">
                    <div class="card-header"><h4>Handover History</h4></div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Asset</th><th>From</th><th>To</th>
                                        <th>Condition</th><th>Handed By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $employee->assetAssignments->sortByDesc('assigned_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo e(route('assets.show', $row->asset_id)); ?>">
                                                    <?php echo e($row->asset->asset_code ?? '-'); ?>

                                                </a>
                                                <br><small class="text-muted"><?php echo e($row->asset->asset_name ?? ''); ?></small>
                                            </td>
                                            <td><?php echo e(optional($row->assigned_at)->format('d M Y')); ?></td>
                                            <td>
                                                <?php if($row->returned_at): ?>
                                                    <?php echo e($row->returned_at->format('d M Y')); ?>

                                                <?php else: ?>
                                                    <span class="badge badge-success">current</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo e($label($row->condition_on_assign)); ?>

                                                <?php if($row->condition_on_return): ?>
                                                    &rarr; <?php echo e($label($row->condition_on_return)); ?>

                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($row->handler->name ?? '-'); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No handover recorded yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/employees/show.blade.php ENDPATH**/ ?>