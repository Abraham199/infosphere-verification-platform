@props(['title' => 'Unable to load this area', 'message' => 'Please try again or contact support.', 'icon' => 'fa-triangle-exclamation'])

<x-ui.empty-state :title="$title" :message="$message" :icon="$icon" {{ $attributes }} />
