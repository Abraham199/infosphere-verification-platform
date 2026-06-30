@props(['stats' => []])

<div class="ivp-stat-grid">
    @foreach($stats as $stat)
        <x-ui.stat-card :label="$stat['label']" :value="$stat['value']" :icon="$stat['icon'] ?? 'fa-chart-simple'" :tone="$stat['tone'] ?? 'primary'" :caption="$stat['caption'] ?? null" />
    @endforeach
</div>
