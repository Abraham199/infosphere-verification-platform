<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row align-items-center g-5">
        <div class="col-lg-7">
            <p class="text-uppercase small fw-semibold text-primary">Info-Sphere Technologies</p>
            <h1 class="display-5 fw-bold">Infosphere Verification Portal</h1>
            <p class="lead text-muted">A secure multi-tenant SaaS foundation for identity verification, tenant operations, and future digital services.</p>
            <div class="d-flex gap-2">
                <a class="btn btn-ivp" href="<?php echo e(route('register')); ?>">Create Account</a>
                <a class="btn btn-outline-secondary" href="<?php echo e(route('login')); ?>">Sign In</a>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="ivp-card p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="fw-semibold">Foundation Status</span>
                    <span class="badge text-bg-primary">Phase 1</span>
                </div>
                <ul class="list-unstyled mb-0 text-muted">
                    <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Multi-tenant foundation</li>
                    <li class="mb-2"><i class="fa-solid fa-check text-success me-2"></i>Authentication and RBAC</li>
                    <li><i class="fa-solid fa-check text-success me-2"></i>Future module placeholders</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER-PC\Documents\Codex\2026-06-24\phase-0-software-architecture-planning-you\resources\views/guest/welcome.blade.php ENDPATH**/ ?>