<?php $__env->startSection('content'); ?>
    <div class="section-header">
        <h1>Edit Employee</h1>
        <div class="ml-auto">
            <a href="<?php echo e(route('employees.index')); ?>" class="btn btn-primary">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="section-body">
        <?php if($errors->any()): ?>
            <div class="alert alert-danger">Please correct the highlighted fields below.</div>
        <?php endif; ?>

        <form action="<?php echo e(route('employees.update', $employee->id)); ?>" method="POST" enctype="multipart/form-data" novalidate>
            <?php echo method_field('PUT'); ?>
            <?php echo csrf_field(); ?>
            <?php echo $__env->make('employees.partials.form', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </form>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/employees/edit.blade.php ENDPATH**/ ?>