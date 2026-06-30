<?php if (isset($component)) { $__componentOriginal74888cb3b248a08ce228c04e2cfe93a9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal74888cb3b248a08ce228c04e2cfe93a9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.tabs','data' => ['tabs' => [
    ['label' => 'Overview', 'href' => route('tenant.wallet.index', ['tenant' => $tenant]), 'icon' => 'fa-wallet', 'active' => request()->routeIs('tenant.wallet.index')],
    ['label' => 'Transactions', 'href' => route('tenant.wallet.transactions', ['tenant' => $tenant]), 'icon' => 'fa-list', 'active' => request()->routeIs('tenant.wallet.transactions')],
    ['label' => 'Funding', 'href' => route('tenant.wallet.funding.index', ['tenant' => $tenant]), 'icon' => 'fa-credit-card', 'active' => request()->routeIs('tenant.wallet.funding.*')],
    ['label' => 'Reservations', 'href' => route('tenant.wallet.reservations', ['tenant' => $tenant]), 'icon' => 'fa-lock', 'active' => request()->routeIs('tenant.wallet.reservations')],
]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.tabs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tabs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
    ['label' => 'Overview', 'href' => route('tenant.wallet.index', ['tenant' => $tenant]), 'icon' => 'fa-wallet', 'active' => request()->routeIs('tenant.wallet.index')],
    ['label' => 'Transactions', 'href' => route('tenant.wallet.transactions', ['tenant' => $tenant]), 'icon' => 'fa-list', 'active' => request()->routeIs('tenant.wallet.transactions')],
    ['label' => 'Funding', 'href' => route('tenant.wallet.funding.index', ['tenant' => $tenant]), 'icon' => 'fa-credit-card', 'active' => request()->routeIs('tenant.wallet.funding.*')],
    ['label' => 'Reservations', 'href' => route('tenant.wallet.reservations', ['tenant' => $tenant]), 'icon' => 'fa-lock', 'active' => request()->routeIs('tenant.wallet.reservations')],
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
<?php /**PATH C:\Users\USER-PC\Documents\Codex\2026-06-24\phase-0-software-architecture-planning-you\resources\views/tenant/wallet/partials/nav.blade.php ENDPATH**/ ?>