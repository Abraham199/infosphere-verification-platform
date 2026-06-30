<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Tenant'); ?> - <?php echo e(config('app.name')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.scss', 'resources/js/app.js']); ?>
</head>
<body>
<?php ($tenantParam = ['tenant' => $tenant]); ?>
<div class="ivp-app-shell">
    <aside class="ivp-sidebar">
        <a class="ivp-brand" href="<?php echo e(route('tenant.dashboard', $tenantParam)); ?>">
            <span class="ivp-brand-mark"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span>
            <span><?php echo e($tenant->name ?? 'Tenant'); ?></span>
        </a>
        <nav class="nav flex-column gap-1" aria-label="Tenant navigation">
            <a class="nav-link <?php if(request()->routeIs('tenant.dashboard')): ?> active <?php endif; ?>" href="<?php echo e(route('tenant.dashboard', $tenantParam)); ?>"><i class="fa-solid fa-border-all"></i>Dashboard</a>
            <a class="nav-link <?php if(request()->routeIs('tenant.wallet.*')): ?> active <?php endif; ?>" href="<?php echo e(route('tenant.wallet.index', $tenantParam)); ?>"><i class="fa-solid fa-wallet"></i>Wallet</a>
            <a class="nav-link <?php if(request()->routeIs('tenant.verification.*')): ?> active <?php endif; ?>" href="<?php echo e(route('tenant.verification.index', $tenantParam)); ?>"><i class="fa-solid fa-id-card"></i>Verification</a>
            <a class="nav-link <?php if(request()->routeIs('tenant.products.*')): ?> active <?php endif; ?>" href="<?php echo e(route('tenant.products.index', $tenantParam)); ?>"><i class="fa-solid fa-box"></i>Products</a>
            <a class="nav-link <?php if(request()->routeIs('tenant.customer.*')): ?> active <?php endif; ?>" href="<?php echo e(route('tenant.customer.dashboard', $tenantParam)); ?>"><i class="fa-solid fa-user"></i>Customer</a>
        </nav>
    </aside>
    <main class="ivp-main">
        <header class="ivp-topbar">
            <div>
                <p class="ivp-page-eyebrow mb-1"><?php echo e($tenant->name ?? 'Tenant Workspace'); ?></p>
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

        <?php echo $__env->yieldContent('content'); ?>
    </main>
</div>
</body>
</html>
<?php /**PATH C:\Users\USER-PC\Documents\Codex\2026-06-24\phase-0-software-architecture-planning-you\resources\views/layouts/tenant.blade.php ENDPATH**/ ?>