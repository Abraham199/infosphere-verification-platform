<form class="row g-2 mb-3" method="GET">
    <div class="col-md-4">
        <label class="form-label" for="event_type">Type</label>
        <select id="event_type" name="event_type" class="form-select">
            <option value="">All types</option>
            @foreach($types as $key => $definition)
                <option value="{{ $key }}" @selected(($filters['event_type'] ?? null) === $key)>{{ $definition['label'] }}</option>
            @endforeach
        </select>
    </div>
    @isset($deliveryStatuses)
        <div class="col-md-3">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach($deliveryStatuses as $case)
                    <option value="{{ $case->value }}" @selected(($filters['status'] ?? null) === $case->value)>{{ str($case->value)->headline() }}</option>
                @endforeach
            </select>
        </div>
    @endisset
    <div class="col-md-3">
        <label class="form-label" for="channel">Channel</label>
        <select id="channel" name="channel" class="form-select">
            <option value="">All channels</option>
            @foreach($channels as $case)
                <option value="{{ $case->value }}" @selected(($filters['channel'] ?? null) === $case->value)>{{ str($case->value)->headline() }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <x-ui.button type="submit" variant="primary" icon="fa-filter">Filter</x-ui.button>
    </div>
</form>
