<?php $__env->startSection('content'); ?>
<div id="app">
    <section class="section" style="min-height: 100vh;">
        <div class="container-fluid h-100">
            <div class="row h-100">
                <div class="col-md-6 d-none d-md-block p-0"
                    style="background-image: url('<?php echo e(asset('assets/img/bg-auth1.png')); ?>');
                           background-size: cover;
                           background-position: center;
                           min-height: 100vh;">
                </div>
                <div class="col-md-6 d-flex align-items-center justify-content-center bg-light">

                    <div style="width: 100%; max-width: 420px;">
                        <div class="text-center mb-4">
                            <h3 class="custom-title">Asset Management System</h3>
                            <p class="custom-subtitle">Asset Inventory Using QR Codes</p>
                        </div>

                        <div class="card card-primary shadow">
                            <div class="card-header">
                                <h4>Please sign in to continue</h4>
                            </div>

                            <div class="card-body">

                                <?php if($errors->any()): ?>
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li><?php echo e($error); ?></li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" action="<?php echo e(route('login')); ?>" class="needs-validation" novalidate>
                                    <?php echo csrf_field(); ?>

                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text"><i class="fas fa-envelope"></i></div>
                                            </div>
                                            <input id="email" type="email" class="form-control" name="email"
                                                placeholder="Enter your email" required autofocus>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="password" class="d-block">Password</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text"><i class="fas fa-lock"></i></div>
                                            </div>
                                            <input id="password" type="password" class="form-control" name="password"
                                                placeholder="Enter your password" required>
                                        </div>
                                    </div>

                                    <div class="form-group d-flex justify-content-between align-items-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="remember" class="custom-control-input" id="remember-me">
                                            <label class="custom-control-label" for="remember-me">Remember Me</label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                                            Login
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>

                        <div class="simple-footer text-center mt-3 text-muted">
                            &copy; <?php echo e(date('Y')); ?> Aplikasi Inventory Asset (Betran-1152525003)
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/auth/login.blade.php ENDPATH**/ ?>