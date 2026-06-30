<?php $__env->startSection('content'); ?>
<div class="container" style="max-width: 520px;">
    <div class="ivp-card p-4">
        <h1 class="h4 mb-3">Create account</h1>
        <form method="POST" action="<?php echo e(route('register')); ?>">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <label class="form-label" for="name">Name</label>
                <input class="form-control" id="name" name="name" value="<?php echo e(old('name')); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" id="password" type="password" name="password" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password_confirmation">Confirm Password</label>
                <input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required>
            </div>
            <button class="btn btn-ivp w-100">Register</button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER-PC\Documents\Codex\2026-06-24\phase-0-software-architecture-planning-you\resources\views/auth/register.blade.php ENDPATH**/ ?>