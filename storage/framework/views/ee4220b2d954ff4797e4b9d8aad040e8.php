<?php $__env->startSection('title', 'Verification Services'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('tenant.verification.partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => 'Available Services','subtitle' => 'Tenant pricing resolved through the Verification Pricing Engine.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Available Services','subtitle' => 'Tenant pricing resolved through the Verification Pricing Engine.']); ?>
    <?php if (isset($component)) { $__componentOriginal793d2b22631f88b8a3d00569a12acf88 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal793d2b22631f88b8a3d00569a12acf88 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table','data' => ['headers' => ['Service', 'Code', 'Price', 'Status', 'Action'],'rows' => collect($services)->map(fn ($service) => [
            e($service['name']),
            e($service['service_code']),
            e($service['currency'].' '.number_format((float) $service['price'], 2)),
            view('components.ui.status-badge', ['status' => $service['status']])->render(),
            '<a class=&quot;btn btn-sm btn-outline-primary&quot; href=&quot;'.e(route('tenant.verification.index', ['tenant' => $tenant, 'service' => $service['service_code']])).'&quot;><i class=&quot;fa-solid fa-arrow-right me-1&quot;></i>Select</a>',
        ])->all(),'empty' => 'No active verification services are configured.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['headers' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Service', 'Code', 'Price', 'Status', 'Action']),'rows' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect($services)->map(fn ($service) => [
            e($service['name']),
            e($service['service_code']),
            e($service['currency'].' '.number_format((float) $service['price'], 2)),
            view('components.ui.status-badge', ['status' => $service['status']])->render(),
            '<a class=&quot;btn btn-sm btn-outline-primary&quot; href=&quot;'.e(route('tenant.verification.index', ['tenant' => $tenant, 'service' => $service['service_code']])).'&quot;><i class=&quot;fa-solid fa-arrow-right me-1&quot;></i>Select</a>',
        ])->all()),'empty' => 'No active verification services are configured.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal793d2b22631f88b8a3d00569a12acf88)): ?>
<?php $attributes = $__attributesOriginal793d2b22631f88b8a3d00569a12acf88; ?>
<?php unset($__attributesOriginal793d2b22631f88b8a3d00569a12acf88); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal793d2b22631f88b8a3d00569a12acf88)): ?>
<?php $component = $__componentOriginal793d2b22631f88b8a3d00569a12acf88; ?>
<?php unset($__componentOriginal793d2b22631f88b8a3d00569a12acf88); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.tenant', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER-PC\Documents\Codex\2026-06-24\phase-0-software-architecture-planning-you\resources\views/tenant/verification/services.blade.php ENDPATH**/ ?>