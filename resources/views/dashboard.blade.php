
<x-layouts::app :title="__('Dashboard')">

    @php
        $user = auth()->user();
        $departmentId = $user->department;
        $departmentName = $user->departmentInfo?->department_name ?? '';

        // Contract visibility
        if ($user->user_type === 'Admin' || (int) $departmentId === 16) {
            $contractsQuery = \App\Models\Contracts::query();
        } else {
            $contractsQuery = \App\Models\Contracts::where(function ($query) use (
                $user,
                $departmentName,
                $departmentId
            ) {
                $query->where(function ($q) use ($user, $departmentName) {
                    $q->where('uploader_dept', $departmentName)
                        ->where('status', 'Active')
                        ->where('uploader_id', $user->id);
                })
                ->orWhere('assigned_department', $departmentId);
            });
        }

        // Dashboard statistics
        $totalContracts = (clone $contractsQuery)->count();

        $activeContracts = (clone $contractsQuery)
            ->where('status', 'Active')
            ->count();

        $expiredContracts = (clone $contractsQuery)
            ->whereDate('end_date', '<', today())
            ->count();

        $expiringContractsCount = (clone $contractsQuery)
            ->whereDate('end_date', '>=', today())
            ->whereDate('end_date', '<=', now()->addDays(30)->toDateString())
            ->count();

        // Status distribution
        $statusCounts = (clone $contractsQuery)
            ->select('status', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        // Department distribution
        $departmentCounts = (clone $contractsQuery)
            ->select('uploader_dept', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->whereNotNull('uploader_dept')
            ->groupBy('uploader_dept')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $maxDepartmentCount = max($departmentCounts->max('total') ?? 0, 1);

        // Expiring contracts
        $expiringContracts = (clone $contractsQuery)
            ->whereDate('end_date', '>=', today())
            ->whereDate('end_date', '<=', now()->addDays(30)->toDateString())
            ->orderBy('end_date')
            ->limit(5)
            ->get();

        // Recently added contracts
        $recentContracts = (clone $contractsQuery)
            ->latest()
            ->limit(6)
            ->get();

        $statusColors = [
            'Active' => 'bg-emerald-500',
            'Expired' => 'bg-red-500',
            'Suspended' => 'bg-amber-500',
            'Archived' => 'bg-slate-500',
            'Pending' => 'bg-blue-500',
        ];

        $statusTextColors = [
            'Active' => 'bg-emerald-100 text-emerald-700',
            'Expired' => 'bg-red-100 text-red-700',
            'Suspended' => 'bg-amber-100 text-amber-700',
            'Archived' => 'bg-slate-100 text-slate-700',
            'Pending' => 'bg-blue-100 text-blue-700',
        ];
    @endphp

    <div class="min-h-screen space-y-7 bg-slate-50/70 p-4 sm:p-6 lg:p-8">

        {{-- Header --}}
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <div class="mb-1 flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                    <p class="text-xs font-bold uppercase tracking-widest text-blue-600">
                        Contract Management System
                    </p>
                </div>

                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">
                    Dashboard
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Welcome back, {{ $user->firstname ?? $user->name ?? 'User' }}.
                    Here's your contract overview.
                </p>
            </div>

            <div class="inline-flex w-fit items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <span class="text-lg">📅</span>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Today
                    </p>
                    <p class="text-sm font-bold text-slate-700">
                        {{ now()->format('F d, Y') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Statistics --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Total Contracts --}}
            <div class="relative overflow-hidden rounded-2xl border border-blue-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-full bg-blue-50"></div>

                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Total Contracts</p>
                        <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">
                            {{ number_format($totalContracts) }}
                        </h2>
                        <p class="mt-2 text-xs font-medium text-slate-400">
                            All accessible contracts
                        </p>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-xl font-bold text-white shadow-md shadow-blue-200">
                        📄
                    </div>
                </div>
            </div>

            {{-- Active Contracts --}}
            <div class="relative overflow-hidden rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-full bg-emerald-50"></div>

                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Active Contracts</p>
                        <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">
                            {{ number_format($activeContracts) }}
                        </h2>
                        <p class="mt-2 text-xs font-medium text-emerald-600">
                            Currently active
                        </p>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-600 text-xl font-bold text-white shadow-md shadow-emerald-200">
                        ✓
                    </div>
                </div>
            </div>

            {{-- Expiring Soon --}}
            <div class="relative overflow-hidden rounded-2xl border border-amber-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-full bg-amber-50"></div>

                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Expiring Soon</p>
                        <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">
                            {{ number_format($expiringContractsCount) }}
                        </h2>
                        <p class="mt-2 text-xs font-medium text-amber-600">
                            Within the next 30 days
                        </p>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500 text-xl font-bold text-white shadow-md shadow-amber-200">
                        ⏱
                    </div>
                </div>
            </div>

            {{-- Expired Contracts --}}
            <div class="relative overflow-hidden rounded-2xl border border-red-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-full bg-red-50"></div>

                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Expired Contracts</p>
                        <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900">
                            {{ number_format($expiredContracts) }}
                        </h2>
                        <p class="mt-2 text-xs font-medium text-red-600">
                            Past their end date
                        </p>
                    </div>

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-600 text-xl font-bold text-white shadow-md shadow-red-200">
                        !
                    </div>
                </div>
            </div>

        </div>

        {{-- Analytics --}}
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

            {{-- Contract Status --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">
                            Contract Status
                        </h3>
                        <p class="mt-1 text-xs text-slate-500">
                            Overview of contracts by current status
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-lg text-white">
                        ▥
                    </div>
                </div>

                @forelse ($statusCounts as $status)
                    @php
                        $percentage = $totalContracts > 0
                            ? ($status->total / $totalContracts) * 100
                            : 0;

                        $barColor = $statusColors[$status->status] ?? 'bg-indigo-500';
                    @endphp

                    <div class="mb-5 last:mb-0">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-700">
                                {{ $status->status ?: 'Unknown' }}
                            </span>

                            <div class="flex items-center gap-2">
                                <span class="text-sm font-extrabold text-slate-900">
                                    {{ number_format($status->total) }}
                                </span>
                                <span class="text-xs text-slate-400">
                                    {{ number_format($percentage, 1) }}%
                                </span>
                            </div>
                        </div>

                        <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="{{ $barColor }} h-full rounded-full transition-all duration-500"
                                style="width: {{ $percentage }}%"
                            ></div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 py-10 text-center">
                        <p class="text-sm font-medium text-slate-400">
                            No contract data available.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Department Distribution --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">
                            Department Distribution
                        </h3>
                        <p class="mt-1 text-xs text-slate-500">
                            Top departments by uploaded contracts
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-600 text-lg text-white">
                        🏢
                    </div>
                </div>

                @forelse ($departmentCounts as $department)
                    @php
                        $percentage = ($department->total / $maxDepartmentCount) * 100;
                    @endphp

                    <div class="mb-5 last:mb-0">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <span class="truncate text-sm font-semibold text-slate-700">
                                {{ $department->uploader_dept }}
                            </span>

                            <span class="shrink-0 text-sm font-extrabold text-slate-900">
                                {{ number_format($department->total) }}
                            </span>
                        </div>

                        <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                            <div
                                class="h-full rounded-full bg-violet-600 transition-all duration-500"
                                style="width: {{ $percentage }}%"
                            ></div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-200 py-10 text-center">
                        <p class="text-sm font-medium text-slate-400">
                            No department data available.
                        </p>
                    </div>
                @endforelse
            </div>

        </div>

        {{-- Expiring Contracts --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col justify-between gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 font-bold text-amber-700">
                            ⏱
                        </span>
                        <h3 class="text-base font-extrabold text-slate-900">
                            Expiring Contracts
                        </h3>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">
                        Contracts ending within the next 30 days
                    </p>
                </div>

                <span class="w-fit rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-700">
                    {{ $expiringContractsCount }} Upcoming
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[650px] text-left text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-4">Contract Name</th>
                            <th class="px-6 py-4">Department</th>
                            <th class="px-6 py-4">End Date</th>
                            <th class="px-6 py-4">Days Remaining</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($expiringContracts as $contract)
                            @php
                                $daysRemaining = (int) today()->diffInDays(
                                    \Illuminate\Support\Carbon::parse($contract->end_date),
                                    false
                                );
                            @endphp

                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-slate-800">
                                        {{ $contract->contract_name }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $contract->contract_type }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{ $contract->uploader_dept ?? '—' }}
                                </td>

                                <td class="px-6 py-4 font-medium text-slate-700">
                                    {{ \Illuminate\Support\Carbon::parse($contract->end_date)->format('M d, Y') }}
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-lg px-3 py-1.5 text-xs font-bold
                                        {{ $daysRemaining <= 7
                                            ? 'bg-red-100 text-red-700'
                                            : ($daysRemaining <= 15
                                                ? 'bg-amber-100 text-amber-700'
                                                : 'bg-emerald-100 text-emerald-700') }}">
                                        {{ $daysRemaining }} days
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-xl text-emerald-600">
                                        ✓
                                    </div>
                                    <p class="mt-3 font-semibold text-slate-700">
                                        No contracts expiring soon
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        There are no contracts ending within the next 30 days.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Contracts --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">
                        Recently Added Contracts
                    </h3>
                    <p class="mt-1 text-xs text-slate-500">
                        Latest contracts added to the system
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-lg text-white">
                    +
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[750px] text-left text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-4">Contract</th>
                            <th class="px-6 py-4">Type</th>
                            <th class="px-6 py-4">Department</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Date Added</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentContracts as $contract)
                            @php
                                $statusClass = $statusTextColors[$contract->status]
                                    ?? 'bg-slate-100 text-slate-700';
                            @endphp

                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-slate-800">
                                        {{ $contract->contract_name }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $contract->tc_no ?? 'No contract number' }}
                                    </p>
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{ $contract->contract_type ?? '—' }}
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{ $contract->uploader_dept ?? '—' }}
                                </td>

                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-lg px-3 py-1.5 text-xs font-bold {{ $statusClass }}">
                                        {{ $contract->status ?? 'Unknown' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 font-medium text-slate-600">
                                    {{ $contract->created_at?->format('M d, Y') ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm font-medium text-slate-400">
                                    No recent contracts found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex flex-col justify-between gap-2 border-t border-slate-200 pt-4 text-xs text-slate-400 sm:flex-row">
            <p>Contract Management System</p>
            <p>Dashboard overview · {{ now()->format('Y') }}</p>
        </div>

    </div>
</x-layouts::app>
