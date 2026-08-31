<!DOCTYPE html>
<html lang="<?php echo e(config('app.locale', 'en')); ?>">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" />

    <title><?php echo $__env->yieldContent('title', 'Asset Management System'); ?></title>

    <!-- CSRF token for forms & axios -->
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <!-- Favicon (optional) -->
    <link rel="icon" href="<?php echo e(asset('assets/img/logo-aiti.svg')); ?>" type="image/x-icon" />
    <!-- <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('assets/img/favicon.svg')); ?>"> -->


    <!-- General CSS Files -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/modules/bootstrap/css/bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/modules/fontawesome/css/all.min.css')); ?>">

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/modules/bootstrap-social/bootstrap-social.css')); ?>">

    <!-- Template CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/components.css')); ?>">

    
    <?php if(app()->environment('production') && config('services.ga.ua')): ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo e(config('services.ga.ua')); ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?php echo e(config('services.ga.ua')); ?>');
        </script>
    <?php endif; ?>

</head>

<body>
    <?php echo $__env->yieldContent('content'); ?>

    <!-- General JS Scripts (jQuery first, then plugins, then template scripts) -->
    <script src="<?php echo e(asset('assets/modules/jquery.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/modules/popper.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/modules/tooltip.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/modules/bootstrap/js/bootstrap.min.js')); ?>"></script>

    <!-- Optional plugins -->
    <script src="<?php echo e(asset('assets/modules/nicescroll/jquery.nicescroll.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/modules/moment.min.js')); ?>"></script>

    <!-- Template core -->
    <script src="<?php echo e(asset('assets/js/stisla.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/scripts.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/custom.js')); ?>"></script>

    
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH C:\wamp64\www\Asset\Asset-Management\resources\views/layouts/app.blade.php ENDPATH**/ ?>