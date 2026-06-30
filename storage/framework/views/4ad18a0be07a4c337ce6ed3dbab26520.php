<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title' => 'Nothing here yet', 'message' => null, 'icon' => 'fa-inbox', 'compact' => false]));

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

foreach (array_filter((['title' => 'Nothing here yet', 'message' => null, 'icon' => 'fa-inbox', 'compact' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => $compact ? 'ivp-empty ivp-empty-compact' : 'ivp-empty'])); ?>>
    <i class="fa-solid <?php echo e($icon); ?>" aria-hidden="true"></i>
    <strong><?php echo e($title); ?></strong>
    <?php if($message): ?><p><?php echo e($message); ?></p><?php endif; ?>
    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\Users\USER-PC\Documents\Codex\2026-06-24\phase-0-software-architecture-planning-you\resources\views/components/ui/empty-state.blade.php ENDPATH**/ ?>