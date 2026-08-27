<?php $__env->startSection('content'); ?>
    <div class="section-header">
        <h1>Categories</h1>
        <div class="ml-auto">
            <a href="/categories/create" class="btn btn-primary"><i class="fa fa-plus"></i> Add Category</a>
        </div>
    </div>

    <div class="section-body">
        <?php if(session()->has('success')): ?>
            <div class="alert alert-success" role="alert">
                <?php echo e(session('success')); ?>

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
                                        <th>Category</th>
                                        <th>Asset Type</th>
                                        <th>Assets</th>
                                        <th>Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($loop->iteration); ?></td>
                                            <td><?php echo e($category->category_name); ?></td>
                                            <td><?php echo e($assetTypes[$category->asset_type] ?? $category->asset_type); ?></td>
                                            <td><?php echo e($category->assets_count); ?></td>
                                            <td>
                                                <a href="/categories/<?php echo e($category->id); ?>/edit"
                                                    class="btn btn-warning btn-sm">Edit</a>

                                                <form id="form<?php echo e($category->id); ?>" 
                                                      action="/categories/<?php echo e($category->id); ?>"
                                                      method="POST" class="d-inline">
                                                    <?php echo method_field('delete'); ?>
                                                    <?php echo csrf_field(); ?>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm swal-confirm" 
                                                            data-form="form<?php echo e($category->id); ?>">
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

<?php echo $__env->make('layouts.main', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/categories/index.blade.php ENDPATH**/ ?>