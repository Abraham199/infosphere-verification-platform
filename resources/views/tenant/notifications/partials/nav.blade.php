<x-ui.tabs :tabs="[
    ['label' => 'Inbox', 'href' => route('tenant.notifications.index', ['tenant' => $tenant]), 'icon' => 'fa-inbox', 'active' => request()->routeIs('tenant.notifications.index')],
    ['label' => 'Unread', 'href' => route('tenant.notifications.unread', ['tenant' => $tenant]), 'icon' => 'fa-envelope', 'active' => request()->routeIs('tenant.notifications.unread')],
    ['label' => 'Read', 'href' => route('tenant.notifications.read', ['tenant' => $tenant]), 'icon' => 'fa-envelope-open', 'active' => request()->routeIs('tenant.notifications.read')],
    ['label' => 'Archived', 'href' => route('tenant.notifications.archived', ['tenant' => $tenant]), 'icon' => 'fa-box-archive', 'active' => request()->routeIs('tenant.notifications.archived')],
    ['label' => 'Preferences', 'href' => route('tenant.notifications.preferences', ['tenant' => $tenant]), 'icon' => 'fa-sliders', 'active' => request()->routeIs('tenant.notifications.preferences')],
]" />
