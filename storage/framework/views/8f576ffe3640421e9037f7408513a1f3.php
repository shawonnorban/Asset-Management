<?php $__env->startSection('content'); ?>
    <div class="section-header">
        <h1>Locations</h1>
        <div class="ml-auto">
            <a href="<?php echo e(route('locations.create')); ?>" class="btn btn-primary"><i class="fa fa-plus"></i> Add Location</a>
        </div>
    </div>

    <div class="section-body">
        <?php if(session()->has('success')): ?>
            <div class="alert alert-success" role="alert">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>
        <?php if(session()->has('error')): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col">
                <div class="card card-primary">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table_id" class="table table-bordered table-hover table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Location</th>
                                        <th>Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($loop->iteration); ?></td>
                                            
                                            <td><?php echo e($location->location_name); ?></td>
                                            <td>
                                                <a href="<?php echo e(route('locations.edit', $location->id)); ?>" class="btn btn-warning">Edit</a>

                                                <form id="delete-form-<?php echo e($location->id); ?>"
                                                      action="<?php echo e(route('locations.destroy', $location->id)); ?>"
                                                      method="POST" class="d-inline">
                                                    <?php echo method_field('DELETE'); ?>
                                                    <?php echo csrf_field(); ?>

                                                    
                                                    <button type="button"
                                                            class="btn btn-danger swal-confirm"
                                                            data-form="delete-form-<?php echo e($location->id); ?>">
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

    <!-- Datatables Jquery -->
    <script>
        $(document).ready(function() {
            $('#table_id').DataTable();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/locations/index.blade.php ENDPATH**/ ?>