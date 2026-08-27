<?php $__env->startSection('content'); ?>
    <div class="section-header">
        <h1>Account List</h1>
        <div class="ml-auto">
            <a href="<?php echo e(route('users.create')); ?>" class="btn btn-primary">
                <i class="fa fa-plus"></i> Add Account
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
            <div class="col">
                <div class="card card-primary">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-users"
                                   class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Created</th>
                                        <th width="180">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($loop->iteration); ?></td>
                                            <td><?php echo e($user->name); ?></td>
                                            <td><?php echo e($user->email); ?></td>
                                            <td>
                                                <span class="badge badge-info text-uppercase">
                                                    <?php echo e($user->role->role ?? '-'); ?>

                                                </span>
                                            </td>
                                            <td><?php echo e($user->created_at->format('d-m-Y')); ?></td>
                                            <td>
                                                <a href="<?php echo e(route('users.edit', $user->id)); ?>"
                                                   class="btn btn-warning btn-sm">
                                                    Edit
                                                </a>

                                                <form id="delete-user-<?php echo e($user->id); ?>"
                                                      action="<?php echo e(route('users.destroy', $user->id)); ?>"
                                                      method="POST"
                                                      class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="button"
                                                            class="btn btn-danger btn-sm swal-confirm"
                                                            data-form="delete-user-<?php echo e($user->id); ?>">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('#table-users').DataTable();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/users/index.blade.php ENDPATH**/ ?>