<?php $__env->startSection('title', 'Wallet Operations'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginal74888cb3b248a08ce228c04e2cfe93a9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal74888cb3b248a08ce228c04e2cfe93a9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.tabs','data' => ['tabs' => [
    ['label' => 'Overview', 'href' => '#overview', 'icon' => 'fa-wallet', 'active' => true],
    ['label' => 'Transactions', 'href' => '#transactions', 'icon' => 'fa-list'],
    ['label' => 'Funding', 'href' => '#funding', 'icon' => 'fa-credit-card'],
    ['label' => 'Reservations', 'href' => '#reservations', 'icon' => 'fa-lock'],
]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.tabs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tabs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
    ['label' => 'Overview', 'href' => '#overview', 'icon' => 'fa-wallet', 'active' => true],
    ['label' => 'Transactions', 'href' => '#transactions', 'icon' => 'fa-list'],
    ['label' => 'Funding', 'href' => '#funding', 'icon' => 'fa-credit-card'],
    ['label' => 'Reservations', 'href' => '#reservations', 'icon' => 'fa-lock'],
])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal74888cb3b248a08ce228c04e2cfe93a9)): ?>
<?php $attributes = $__attributesOriginal74888cb3b248a08ce228c04e2cfe93a9; ?>
<?php unset($__attributesOriginal74888cb3b248a08ce228c04e2cfe93a9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal74888cb3b248a08ce228c04e2cfe93a9)): ?>
<?php $component = $__componentOriginal74888cb3b248a08ce228c04e2cfe93a9; ?>
<?php unset($__componentOriginal74888cb3b248a08ce228c04e2cfe93a9); ?>
<?php endif; ?>

<div class="ivp-grid-2">
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['id' => 'overview','title' => 'Wallet Overview','subtitle' => 'Balances across tenant wallets.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'overview','title' => 'Wallet Overview','subtitle' => 'Balances across tenant wallets.']); ?>
        <?php if (isset($component)) { $__componentOriginal793d2b22631f88b8a3d00569a12acf88 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal793d2b22631f88b8a3d00569a12acf88 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table','data' => ['headers' => ['Currency', 'Available', 'Reserved', 'Frozen', 'Status'],'rows' => collect($wallets)->map(fn ($row) => [
                e($row['currency'] ?? 'NGN'),
                e(number_format((float) ($row['available_balance'] ?? 0), 2)),
                e(number_format((float) ($row['reserved_balance'] ?? 0), 2)),
                e(number_format((float) ($row['frozen_balance'] ?? 0), 2)),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'active'])->render(),
            ])->all(),'empty' => 'No wallets found.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['headers' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Currency', 'Available', 'Reserved', 'Frozen', 'Status']),'rows' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect($wallets)->map(fn ($row) => [
                e($row['currency'] ?? 'NGN'),
                e(number_format((float) ($row['available_balance'] ?? 0), 2)),
                e(number_format((float) ($row['reserved_balance'] ?? 0), 2)),
                e(number_format((float) ($row['frozen_balance'] ?? 0), 2)),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'active'])->render(),
            ])->all()),'empty' => 'No wallets found.']); ?>
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

    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['id' => 'funding','title' => 'Funding History','subtitle' => 'Recent payment funding records.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'funding','title' => 'Funding History','subtitle' => 'Recent payment funding records.']); ?>
        <?php if (isset($component)) { $__componentOriginal793d2b22631f88b8a3d00569a12acf88 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal793d2b22631f88b8a3d00569a12acf88 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table','data' => ['headers' => ['Reference', 'Amount', 'Status', 'Created'],'rows' => collect($funding)->map(fn ($row) => [
                e($row['reference'] ?? '-'),
                e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['amount'] ?? 0), 2)),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'pending'])->render(),
                e((string) ($row['created_at'] ?? '-')),
            ])->all(),'empty' => 'No funding records yet.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['headers' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Reference', 'Amount', 'Status', 'Created']),'rows' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect($funding)->map(fn ($row) => [
                e($row['reference'] ?? '-'),
                e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['amount'] ?? 0), 2)),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'pending'])->render(),
                e((string) ($row['created_at'] ?? '-')),
            ])->all()),'empty' => 'No funding records yet.']); ?>
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
</div>

<div class="ivp-grid-2 mt-3">
    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['id' => 'transactions','title' => 'Transaction History','subtitle' => 'Latest wallet transactions.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'transactions','title' => 'Transaction History','subtitle' => 'Latest wallet transactions.']); ?>
        <?php if (isset($component)) { $__componentOriginal793d2b22631f88b8a3d00569a12acf88 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal793d2b22631f88b8a3d00569a12acf88 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table','data' => ['headers' => ['Reference', 'Type', 'Amount', 'Status'],'rows' => collect($transactions)->map(fn ($row) => [
                e($row['reference'] ?? '-'),
                e(str($row['type'] ?? '-')->headline()),
                e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['amount'] ?? 0), 2)),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'pending'])->render(),
            ])->all(),'empty' => 'No wallet transactions yet.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['headers' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Reference', 'Type', 'Amount', 'Status']),'rows' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect($transactions)->map(fn ($row) => [
                e($row['reference'] ?? '-'),
                e(str($row['type'] ?? '-')->headline()),
                e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['amount'] ?? 0), 2)),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'pending'])->render(),
            ])->all()),'empty' => 'No wallet transactions yet.']); ?>
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

    <?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['id' => 'reservations','title' => 'Reservation History','subtitle' => 'Reserved funds waiting for capture or release.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => 'reservations','title' => 'Reservation History','subtitle' => 'Reserved funds waiting for capture or release.']); ?>
        <?php if (isset($component)) { $__componentOriginal793d2b22631f88b8a3d00569a12acf88 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal793d2b22631f88b8a3d00569a12acf88 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.table','data' => ['headers' => ['Reference', 'Amount', 'Status', 'Created'],'rows' => collect($reservations)->map(fn ($row) => [
                e($row['reference'] ?? '-'),
                e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['amount'] ?? 0), 2)),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'pending'])->render(),
                e((string) ($row['created_at'] ?? '-')),
            ])->all(),'empty' => 'No reservations yet.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['headers' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['Reference', 'Amount', 'Status', 'Created']),'rows' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(collect($reservations)->map(fn ($row) => [
                e($row['reference'] ?? '-'),
                e(($row['currency'] ?? 'NGN').' '.number_format((float) ($row['amount'] ?? 0), 2)),
                view('components.ui.status-badge', ['status' => $row['status'] ?? 'pending'])->render(),
                e((string) ($row['created_at'] ?? '-')),
            ])->all()),'empty' => 'No reservations yet.']); ?>
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
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.platform', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER-PC\Documents\Codex\2026-06-24\phase-0-software-architecture-planning-you\resources\views/platform/wallet/index.blade.php ENDPATH**/ ?>