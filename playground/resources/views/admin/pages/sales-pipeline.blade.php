{{-- Built from <x-cb::...> components only, so it looks right in both the AdminLTE and the Tailwind theme. --}}
<x-cb::grid cols="4">
    @foreach($stats as $stat)
        <x-cb::stat :label="$stat['label']" :value="$stat['value']" :icon="$stat['icon']" :color="$stat['color']" :url="$stat['url']" />
    @endforeach
</x-cb::grid>

<x-cb::grid cols="2">
    <x-cb::card title="Pipeline by stage" flush>
        <x-cb::table>
            <table>
                <thead><tr><th>Stage</th><th class="text-end">Deals</th><th class="text-end">Value</th></tr></thead>
                <tbody>
                @foreach($stages as $stage)
                    <tr>
                        <td><a href="{{ $stage['url'] }}"><x-cb::badge :color="$stage['color']">{{ $stage['label'] }}</x-cb::badge></a></td>
                        <td class="text-end">{{ $stage['deals'] }}</td>
                        <td class="text-end">${{ number_format($stage['amount']) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </x-cb::table>
    </x-cb::card>

    <x-cb::card title="Next activities" description="Open calls, emails, meetings and tasks." flush>
        <x-slot:actions><x-cb::button :href="$activitiesUrl" size="sm">All activities</x-cb::button></x-slot:actions>
        <x-cb::table>
            <table>
                <thead><tr><th>Activity</th><th>Deal</th><th class="text-end">Due</th></tr></thead>
                <tbody>
                @forelse($upcoming as $activity)
                    <tr>
                        <td><x-cb::badge color="secondary">{{ ucfirst($activity->type) }}</x-cb::badge> {{ $activity->subject }}</td>
                        <td>{{ $activity->deal?->title ?? '—' }}</td>
                        <td class="text-end">
                            @if($activity->due_at?->isPast())
                                <x-cb::badge color="danger">{{ $activity->due_at->format('d M') }}</x-cb::badge>
                            @else
                                {{ $activity->due_at?->format('d M') ?? '—' }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3">Nothing is due.</td></tr>
                @endforelse
                </tbody>
            </table>
        </x-cb::table>
    </x-cb::card>
</x-cb::grid>
