<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title' => null, 'subtitle' => null, 'actions' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['title' => null, 'subtitle' => null, 'actions' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section <?php echo e($attributes->merge(['class' => 'ivp-card'])); ?>>
    <?php if($title || $subtitle || $actions): ?>
        <div class="ivp-card-header">
            <div>
                <?php if($title): ?><h2 class="ivp-card-title"><?php echo e($title); ?></h2><?php endif; ?>
                <?php if($subtitle): ?><p class="ivp-card-subtitle"><?php echo e($subtitle); ?></p><?php endif; ?>
            </div>
            <?php if($actions): ?><div class="ivp-card-actions"><?php echo e($actions); ?></div><?php endif; ?>
        </div>
    <?php endif; ?>
    <div class="ivp-card-body">
        <?php echo e($slot); ?>

    </div>
</section>
<?php /**PATH C:\Users\USER-PC\Documents\Codex\2026-06-24\phase-0-software-architecture-planning-you\resources\views/components/ui/card.blade.php ENDPATH**/ ?>