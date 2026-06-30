<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('code'); ?> - <?php echo e(config('app.name')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.scss', 'resources/js/app.js']); ?>
</head>
<body>
<main class="container py-5">
    <div class="ivp-card p-5 mx-auto" style="max-width: 640px;">
        <p class="text-primary fw-semibold mb-2"><?php echo $__env->yieldContent('code'); ?></p>
        <h1 class="h3"><?php echo $__env->yieldContent('title'); ?></h1>
        <p class="text-muted"><?php echo $__env->yieldContent('message'); ?></p>
        <a class="btn btn-ivp" href="<?php echo e(route('home')); ?>">Return Home</a>
    </div>
</main>
</body>
</html>
<?php /**PATH C:\Users\USER-PC\Documents\Codex\2026-06-24\phase-0-software-architecture-planning-you\resources\views/errors/layout.blade.php ENDPATH**/ ?>