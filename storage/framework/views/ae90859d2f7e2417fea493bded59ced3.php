<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Platform'); ?> - <?php echo e(config('app.name')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.scss', 'resources/js/app.js']); ?>
</head>
<body>
<div class="ivp-app-shell">
    <aside class="ivp-sidebar">
        <a class="ivp-brand" href="<?php echo e(route('platform.dashboard')); ?>">
            <span class="ivp-brand-mark"><i class="fa-solid fa-globe" aria-hidden="true"></i></span>
            <span>IVP Platform</span>
        </a>
        <nav class="nav flex-column gap-1" aria-label="Platform navigation">
            <a class="nav-link <?php if(request()->routeIs('platform.dashboard')): ?> active <?php endif; ?>" href="<?php echo e(route('platform.dashboard')); ?>"><i class="fa-solid fa-chart-line"></i>Overview</a>
            <a class="nav-link <?php if(request()->routeIs('platform.wallet.*')): ?> active <?php endif; ?>" href="<?php echo e(route('platform.wallet.index')); ?>"><i class="fa-solid fa-wallet"></i>Wallets</a>
            <a class="nav-link <?php if(request()->routeIs('platform.verification.*')): ?> active <?php endif; ?>" href="<?php echo e(route('platform.verification.index')); ?>"><i class="fa-solid fa-id-card"></i>Verifications</a>
            <a class="nav-link <?php if(request()->routeIs('platform.products.*')): ?> active <?php endif; ?>" href="<?php echo e(route('platform.products.index')); ?>"><i class="fa-solid fa-boxes-stacked"></i>Products</a>
        </nav>
    </aside>
    <main class="ivp-main">
        <header class="ivp-topbar">
            <div>
                <p class="ivp-page-eyebrow mb-1">Platform Administration</p>
                <h1 class="ivp-page-title"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></h1>
            </div>
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['type' => 'submit','variant' => 'outline-secondary','size' => 'sm','icon' => 'fa-arrow-right-from-bracket']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','variant' => 'outline-secondary','size' => 'sm','icon' => 'fa-arrow-right-from-bracket']); ?>Logout <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
            </form>
        </header>

        <?php if(session('status')): ?>
            <?php if (isset($component)) { $__componentOriginal746de018ded8594083eb43be3f1332e1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal746de018ded8594083eb43be3f1332e1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.alert','data' => ['variant' => 'success','icon' => 'fa-circle-check']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'success','icon' => 'fa-circle-check']); ?><?php echo e(session('status')); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal746de018ded8594083eb43be3f1332e1)): ?>
<?php $attributes = $__attributesOriginal746de018ded8594083eb43be3f1332e1; ?>
<?php unset($__attributesOriginal746de018ded8594083eb43be3f1332e1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal746de018ded8594083eb43be3f1332e1)): ?>
<?php $component = $__componentOriginal746de018ded8594083eb43be3f1332e1; ?>
<?php unset($__componentOriginal746de018ded8594083eb43be3f1332e1); ?>
<?php endif; ?>
        <?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>
    </main>
</div>
</body>
</html>
<?php /**PATH C:\Users\USER-PC\Documents\Codex\2026-06-24\phase-0-software-architecture-planning-you\resources\views/layouts/platform.blade.php ENDPATH**/ ?>