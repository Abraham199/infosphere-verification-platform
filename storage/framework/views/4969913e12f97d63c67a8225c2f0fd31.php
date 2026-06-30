<?php $__env->startSection('title', 'Wallet Reservations'); ?>

<?php $__env->startSection('content'); ?>
<?php echo $__env->make('tenant.wallet.partials.nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['title' => 'Reservation History','subtitle' => 'Funds reserved for verification or service workflows.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Reservation History','subtitle' => 'Funds reserved for verification or service workflows.']); ?>
    <?php if (isset($component)) { $__componentOriginal793d2b22631f88b8a3d00569a12acf88 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal793d2b22631f88b8a3d00569a12acf88 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table','data' => ['headers' => ['Reference', 'Amount', 'Captured', 'Released', 'Status', 'Created'],'rows' => collect($reservations)->map(fn ($row) => [
            e($row['reference'] ?? '-'),
            e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['amount'] ?? 0), 2)),
            e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['captured_amount'] ?? 0), 2)),
            e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['released_amount'] ?? 0), 2)),
            view('components.ui.status-badge', ['status' => $row['status'] ?? 'pending'])->render(),
            e((string) ($row['created_at'] ?? '-')),
        ])->all(),'empty' => 'No reservations have been recorded for this wallet.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['headers' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Reference', 'Amount', 'Captured', 'Released', 'Status', 'Created']),'rows' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect($reservations)->map(fn ($row) => [
            e($row['reference'] ?? '-'),
            e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['amount'] ?? 0), 2)),
            e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['captured_amount'] ?? 0), 2)),
            e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['released_amount'] ?? 0), 2)),
            view('components.ui.status-badge', ['status' => $row['status'] ?? 'pending'])->render(),
            e((string) ($row['created_at'] ?? '-')),
        ])->all()),'empty' => 'No reservations have been recorded for this wallet.']); ?>
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

<?php echo $__env->make('layouts.tenant', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER-PC\Documents\Codex\2026-06-24\phase-0-software-architecture-planning-you\resources\views/tenant/wallet/reservations.blade.php ENDPATH**/ ?>