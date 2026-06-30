<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label', 'value', 'icon' => 'fa-chart-simple', 'tone' => 'primary', 'caption' => null]));

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

foreach (array_filter((['label', 'value', 'icon' => 'fa-chart-simple', 'tone' => 'primary', 'caption' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<section <?php echo e($attributes->merge(['class' => 'ivp-stat'])); ?>>
    <div class="ivp-stat-icon ivp-stat-<?php echo e($tone); ?>">
        <i class="fa-solid <?php echo e($icon); ?>" aria-hidden="true"></i>
    </div>
    <div>
        <p class="ivp-stat-label"><?php echo e($label); ?></p>
        <strong class="ivp-stat-value"><?php echo e($value); ?></strong>
        <?php if($caption): ?><span class="ivp-stat-caption"><?php echo e($caption); ?></span><?php endif; ?>
    </div>
</section>
<?php /**PATH C:\Users\USER-PC\Documents\Codex\2026-06-24\phase-0-software-architecture-planning-you\resources\views/components/ui/stat-card.blade.php ENDPATH**/ ?>