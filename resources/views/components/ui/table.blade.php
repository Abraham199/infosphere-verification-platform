@props(['headers' => [], 'rows' => [], 'empty' => 'No records found.'])

<div class="ivp-table-wrap">
    <table {{ $attributes->merge(['class' => 'table ivp-table align-middle mb-0']) }}>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th scope="col">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{!! $cell !!}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ max(count($headers), 1) }}">
                        <x-ui.empty-state icon="fa-folder-open" :title="$empty" compact />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
