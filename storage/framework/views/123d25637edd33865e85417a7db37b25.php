<aside id="sidebar-wrapper">
    <div class="sidebar-brand">
        <a href="<?php echo e(route('home')); ?>">Assets</a>
    </div>
    <div class="sidebar-brand sidebar-brand-sm">
        <a href="<?php echo e(route('home')); ?>">SI</a>
    </div>

    <ul class="sidebar-menu">
        <?php if(empty($sidebarMenu)): ?>
            <li class="menu-header">Dashboard</li>
            <li><a class="nav-link" href="<?php echo e(route('home')); ?>"><i class="fa fa-fire"></i> <span>Dashboard</span></a></li>
        <?php else: ?>
            <?php $__currentLoopData = $sidebarMenu; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(!empty($block['header'])): ?>
                    <li class="menu-header"><?php echo e($block['header']); ?></li>
                <?php endif; ?>

                <?php $__currentLoopData = $block['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $url = (isset($item['route']) && \Route::has($item['route'])) 
                                ? route($item['route']) 
                                : (isset($item['url']) ? url($item['url']) : '#');

                        $isActive = false;
                        if (isset($item['route']) && \Route::currentRouteName() === $item['route']) {
                            $isActive = true;
                        } elseif ($url !== '#' && Str::startsWith(request()->path(), trim(parse_url($url, PHP_URL_PATH), '/'))) {
                            $isActive = true;
                        }

                        $icon = $item['icon'] ?? 'fa fa-circle';
                    ?>

                    <li class="<?php echo e($isActive ? 'active' : ''); ?>">
                        <a class="nav-link" href="<?php echo e($url); ?>">
                            <i class="<?php echo e($icon); ?>"></i> <span><?php echo e($item['label']); ?></span>
                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
    </ul>
</aside>
<?php /**PATH C:\wamp64\www\Enterprise-Asset-Management\resources\views/partials/sidebar.blade.php ENDPATH**/ ?>