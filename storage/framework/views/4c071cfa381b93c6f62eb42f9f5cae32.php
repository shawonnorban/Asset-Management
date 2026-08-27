<?php $__env->startSection('content'); ?>
<div class="section-header">
    <h1>Audit Detail</h1>
    <div class="ml-auto">
        <a href="<?php echo e(route('audit.index')); ?>" class="btn btn-primary">
            Back
        </a>
    </div>
</div>

<div class="section-body">

    <div class="card card-primary mb-3">
        <div class="card-body">
            <table class="table table-sm">
                <tr><th>Time</th><td><?php echo e($auditLog->occurred_at); ?></td></tr>
                <tr><th>User</th><td><?php echo e($auditLog->user_name); ?></td></tr>
                <tr><th>Action</th><td><?php echo e($auditLog->action); ?></td></tr>
                <tr><th>Table</th><td><?php echo e($auditLog->table_name); ?></td></tr>
                <tr><th>Row ID</th><td><?php echo e($auditLog->row_id); ?></td></tr>
                <tr><th>URL</th><td><?php echo e($auditLog->url); ?></td></tr>
                <tr><th>IP</th><td><?php echo e($auditLog->ip_address); ?></td></tr>
                <tr><th>Method</th><td><?php echo e($auditLog->http_method); ?></td></tr>
                <tr><th>Message</th><td><?php echo e($auditLog->message ?? '-'); ?></td></tr>
            </table>
        </div>
    </div>

    <div class="row">
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Before</div>
                <div class="card-body">
                    <pre><?php echo e(json_encode($auditLog->before_data, JSON_PRETTY_PRINT)); ?></pre>
                </div>
            </div>
        </div>

        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">After</div>
                <div class="card-body">
                    <pre><?php echo e(json_encode($auditLog->after_data, JSON_PRETTY_PRINT)); ?></pre>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/audit/show.blade.php ENDPATH**/ ?>