<?php $__env->startSection('content'); ?>
<div class="section-header">
    <h1>Create Asset Stock Take</h1>
    <div class="ml-auto">
        <a href="<?php echo e(route('stock-takes.index')); ?>" class="btn btn-primary">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="section-body">

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?php echo e(route('stock-takes.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>

        <div class="card card-primary">
            <div class="card-header">
                <h4>Stock Take Information</h4>
            </div>

            <div class="card-body">

                <div class="form-group">
                    <label>Stock Take Name <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           placeholder="Contoh: Stock Take Semester 1 2026"
                           value="<?php echo e(old('name')); ?>"
                           required>
                </div>

                <div class="form-group">
                    <label>Stock Take Date <span class="text-danger">*</span></label>
                    <input type="date"
                           name="stock_take_date"
                           class="form-control"
                           value="<?php echo e(old('stock_take_date', now()->toDateString())); ?>"
                           required>
                </div>

                <div class="alert alert-info">
                    <b>Note:</b><br>
                    - The stock take will be created with status <b>DRAFT</b><br>
                    - Once created, record the count result for each asset<br>
                    - A stock take can no longer be changed once <b>FINAL</b>
                </div>

            </div>

            <div class="card-footer text-right">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Save & Lanjut Input
                </button>
            </div>
        </div>

    </form>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/stock-takes/create.blade.php ENDPATH**/ ?>