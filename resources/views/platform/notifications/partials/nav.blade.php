<x-ui.tabs :tabs="[
    ['label' => 'Dashboard', 'href' => route('platform.notifications.index'), 'icon' => 'fa-gauge', 'active' => request()->routeIs('platform.notifications.index')],
    ['label' => 'Queue', 'href' => route('platform.notifications.queue'), 'icon' => 'fa-list-check', 'active' => request()->routeIs('platform.notifications.queue')],
    ['label' => 'Failed', 'href' => route('platform.notifications.failed'), 'icon' => 'fa-triangle-exclamation', 'active' => request()->routeIs('platform.notifications.failed')],
]" />
