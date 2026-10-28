{{-- Built from <x-cb::...> components only, so it looks right in both the AdminLTE and the Tailwind theme. --}}
<x-cb::grid cols="4">
    @foreach($stats as $stat)
        <x-cb::stat :label="$stat['label']" :value="$stat['value']" :icon="$stat['icon']" :color="$stat['color']" :url="$stat['url']" />
    @endforeach
</x-cb::grid>

<x-cb::grid cols="2">
    <x-cb::card title="Away today" description="Approved leave that covers today." flush>
        <x-cb::table>
            <table>
                <thead><tr><th>Employee</th><th>Type</th><th class="text-end">Back on</th></tr></thead>
                <tbody>
                @forelse($awayToday as $leave)
                    <tr>
                        <td>{{ $leave->employee->name }}</td>
                        <td><x-cb::badge color="info">{{ ucfirst($leave->type) }}</x-cb::badge></td>
                        <td class="text-end">{{ $leave->ends_on->addDay()->format('d M') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">Everyone is in today.</td></tr>
                @endforelse
                </tbody>
            </table>
        </x-cb::table>
    </x-cb::card>

    <x-cb::card title="Departments" description="Current headcount." flush>
        <x-slot:actions><x-cb::button :href="$allDepartmentsUrl" size="sm">All departments</x-cb::button></x-slot:actions>
        <x-cb::table>
            <table>
                <thead><tr><th>Department</th><th class="text-end">Employees</th></tr></thead>
                <tbody>
                @foreach($departments as $department)
                    <tr>
                        <td><a href="{{ $departmentUrl($department) }}">{{ $department->name }}</a></td>
                        <td class="text-end">{{ $department->employees_count }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </x-cb::table>
    </x-cb::card>
</x-cb::grid>
