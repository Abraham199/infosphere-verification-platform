<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status' => 'pending']));

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

foreach (array_filter((['status' => 'pending']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $normalized = strtolower((string) $status);
    $tone = match ($normalized) {
        'active', 'completed', 'sent', 'successful', 'success', 'published' => 'success',
        'failed', 'cancelled', 'inactive', 'disabled' => 'danger',
        'pending', 'draft', 'processing', 'retrying' => 'warning',
        default => 'secondary',
    };
?>

<span <?php echo e($attributes->merge(['class' => 'badge text-bg-'.$tone])); ?>><?php echo e(str($status)->headline()); ?></span>
<?php /**PATH C:\Users\USER-PC\Documents\Codex\2026-06-24\phase-0-software-architecture-planning-you\resources\views/components/ui/status-badge.blade.php ENDPATH**/ ?>