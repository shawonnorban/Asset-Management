<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo e($title); ?></title>
    <style>
        @page { margin: 20px 22px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #0f172a; }
        h1 { font-size: 15px; margin: 0 0 2px; }
        .meta { font-size: 9px; color: #64748b; margin: 0 0 14px; }
        .head { border-bottom: 2px solid #0f172a; padding-bottom: 8px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        thead { display: table-header-group; }
        th { background: #0f172a; color: #fff; text-align: left; padding: 6px 5px; font-size: 8.5px; font-weight: 600; }
        td { padding: 5px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tr:nth-child(even) td { background: #f8fafc; }
        .empty { padding: 24px; text-align: center; color: #64748b; border: 1px dashed #cbd5e1; }
        .footer { margin-top: 14px; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="head">
        <h1><?php echo e($title); ?></h1>
        <p class="meta">
            <?php echo e(config('app.name', 'Asset Management')); ?>

            &middot; Generated <?php echo e($generatedAt); ?>

            <?php if(isset($generatedBy)): ?> &middot; by <?php echo e($generatedBy); ?> <?php endif; ?>
            &middot; <?php echo e(count($rows)); ?> <?php echo e(Str::plural('record', count($rows))); ?>

        </p>
    </div>

    <?php if(count($rows) === 0): ?>
        <p class="empty">No records matched this report.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <?php $__currentLoopData = $headings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $heading): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th><?php echo e($heading); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td><?php echo e($cell === null || $cell === '' ? '-' : $cell); ?></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p class="footer">Confidential &middot; <?php echo e(config('app.name', 'Asset Management')); ?></p>
</body>
</html>
<?php /**PATH C:\wamp64\www\Asset\Asset-Management\resources\views/reports/pdf.blade.php ENDPATH**/ ?>