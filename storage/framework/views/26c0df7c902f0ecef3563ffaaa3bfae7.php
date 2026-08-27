<?php $__env->startSection('content'); ?>
<div class="section-header">
    <h1>Asset Stock Take List</h1>
    <div class="ml-auto">
        <a href="<?php echo e(route('stock-takes.create')); ?>" class="btn btn-primary">
            <i class="fa fa-plus"></i> New Stock Take
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
                <table class="table table-bordered table-striped" id="table-stock-takes">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Stock Take Code</th>
                            <th>Stock Take Name</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $stockTakes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stockTake): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($stockTake->stock_take_code); ?></td>
                                <td><?php echo e($stockTake->name); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($stockTake->stock_take_date)->format('d-m-Y')); ?></td>
                                <td>
                                    <?php if($stockTake->status === 'DRAFT'): ?>
                                        <span class="badge badge-warning">DRAFT</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">FINAL</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($stockTake->user->name); ?></td>
                                <td>
                                    <a href="<?php echo e(route('stock-takes.show', $stockTake->id)); ?>"
                                    class="btn btn-info btn-sm">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>

                                    <?php if($stockTake->status === 'FINAL'): ?>
                                        <a href="<?php echo e(route('stock-takes.pdf', $stockTake->id)); ?>"
                                        target="_blank"
                                        class="btn btn-danger btn-sm ml-1">
                                            <i class="fa fa-file-pdf"></i>
                                        </a>
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
        $('#table-stock-takes').DataTable();
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/stock-takes/index.blade.php ENDPATH**/ ?>