
<x-layouts::app :title="__('Dashboard')">

    @php
        $user = auth()->user();
        $departmentId = $user->department;
        $departmentName = $user->departmentInfo?->department_name ?? '';

        /*
        |--------------------------------------------------------------------------
        | Contract Visibility
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Dashboard Statistics
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Contract Status Distribution
        |--------------------------------------------------------------------------
        */

        $statusCounts = (clone $contractsQuery)
            ->select('status', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Department Distribution
        |--------------------------------------------------------------------------
        */

        $departmentCounts = (clone $contractsQuery)
            ->select('uploader_dept', \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'))
            ->whereNotNull('uploader_dept')
            ->groupBy('uploader_dept')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $maxDepartmentCount = max(
            $departmentCounts->max('total') ?? 0,
            1
        );

        /*
        |--------------------------------------------------------------------------
        | Expiring Contracts
        |--------------------------------------------------------------------------
        */

        $expiringContracts = (clone $contractsQuery)
            ->whereDate('end_date', '>=', today())
            ->whereDate('end_date', '<=', now()->addDays(30)->toDateString())
            ->orderBy('end_date')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent Contracts
        |--------------------------------------------------------------------------
        */

        $recentContracts = (clone $contractsQuery)
            ->latest()
            ->limit(6)
            ->get();
    @endphp

    <div class="flex h-full w-full flex-1 flex-col gap-6">

        {{-- Header --}}
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">
                Dashboard
            </h1>

            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Welcome back, {{ $user->firstname ?? $user->username }}.
                Here's your contract management overview.
            </p>
        </div>

        {{-- Overview Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Total Contracts --}}
            <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">
                            Total Contracts
                        </p>

                        <h2 class="mt-2 text-3xl font-bold text-neutral-900 dark:text-white">
                            {{ number_format($totalContracts) }}
                        </h2>
                    </div>

                    <div class="rounded-xl bg-blue-50 p-3 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                        <flux:icon name="document-duplicate" class="size-6" />
                    </div>
                </div>

                <p class="mt-3 text-xs text-neutral-500">
                    Contracts available to you
                </p>
            </div>

            {{-- Active Contracts --}}
            <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">
                            Active Contracts
                        </p>

                        <h2 class="mt-2 text-3xl font-bold text-neutral-900 dark:text-white">
                            {{ number_format($activeContracts) }}
                        </h2>
                    </div>

                    <div class="rounded-xl bg-green-50 p-3 text-green-600 dark:bg-green-500/10 dark:text-green-400">
                        <flux:icon name="check-circle" class="size-6" />
                    </div>
                </div>

                <p class="mt-3 text-xs text-neutral-500">
                    Currently active contracts
                </p>
            </div>

            {{-- Expiring Soon --}}
            <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">
                            Expiring Soon
                        </p>

                        <h2 class="mt-2 text-3xl font-bold text-neutral-900 dark:text-white">
                            {{ number_format($expiringContractsCount) }}
                        </h2>
                    </div>

                    <div class="rounded-xl bg-amber-50 p-3 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                        <flux:icon name="clock" class="size-6" />
                    </div>
                </div>

                <p class="mt-3 text-xs text-neutral-500">
                    Expiring within the next 30 days
                </p>
            </div>

            {{-- Expired Contracts --}}
            <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-neutral-500 dark:text-neutral-400">
                            Expired Contracts
                        </p>

                        <h2 class="mt-2 text-3xl font-bold text-neutral-900 dark:text-white">
                            {{ number_format($expiredContracts) }}
                        </h2>
                    </div>

                    <div class="rounded-xl bg-red-50 p-3 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                        <flux:icon name="exclamation-triangle" class="size-6" />
                    </div>
                </div>

                <p class="mt-3 text-xs text-neutral-500">
                    Contracts past their end date
                </p>
            </div>

        </div>

        {{-- Analytics --}}
        <div class="grid gap-6 lg:grid-cols-2">

            {{-- Contract Status --}}
            <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

                <div class="mb-5">
                    <h2 class="font-semibold text-neutral-900 dark:text-white">
                        Contract Status
                    </h2>

                    <p class="mt-1 text-sm text-neutral-500">
                        Distribution of contracts by status
                    </p>
                </div>

                <div class="space-y-5">

                    @forelse ($statusCounts as $status)
                        @php
                            $percentage = $totalContracts > 0
                                ? ($status->total / $totalContracts) * 100
                                : 0;

                            $statusColor = match (strtolower($status->status ?? '')) {
                                'active' => 'bg-green-500',
                                'expired' => 'bg-red-500',
                                'suspended' => 'bg-amber-500',
                                'archived' => 'bg-neutral-400',
                                default => 'bg-blue-500',
                            };
                        @endphp

                        <div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="font-medium text-neutral-700 dark:text-neutral-300">
                                    {{ $status->status ?? 'Unknown' }}
                                </span>

                                <span class="text-neutral-500">
                                    {{ number_format($status->total) }}
                                    <span class="ml-1 text-xs">
                                        ({{ number_format($percentage, 1) }}%)
                                    </span>
                                </span>
                            </div>

                            <div class="h-2.5 overflow-hidden rounded-full bg-neutral-100 dark:bg-neutral-800">
                                <div
                                    class="{{ $statusColor }} h-full rounded-full transition-all"
                                    style="width: {{ $percentage }}%"
                                ></div>
                            </div>
                        </div>
                    @empty
                        <p class="py-8 text-center text-sm text-neutral-500">
                            No contract records available.
                        </p>
                    @endforelse

                </div>
            </div>

            {{-- Department Distribution --}}
            <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

                <div class="mb-5">
                    <h2 class="font-semibold text-neutral-900 dark:text-white">
                        Department Distribution
                    </h2>

                    <p class="mt-1 text-sm text-neutral-500">
                        Contracts grouped by uploading department
                    </p>
                </div>

                <div class="space-y-5">

                    @forelse ($departmentCounts as $department)
                        @php
                            $percentage = ($department->total / $maxDepartmentCount) * 100;
                        @endphp

                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                                <span class="truncate font-medium text-neutral-700 dark:text-neutral-300">
                                    {{ $department->uploader_dept }}
                                </span>

                                <span class="shrink-0 text-neutral-500">
                                    {{ number_format($department->total) }}
                                </span>
                            </div>

                            <div class="h-2.5 overflow-hidden rounded-full bg-neutral-100 dark:bg-neutral-800">
                                <div
                                    class="h-full rounded-full bg-blue-500"
                                    style="width: {{ $percentage }}%"
                                ></div>
                            </div>
                        </div>
                    @empty
                        <p class="py-8 text-center text-sm text-neutral-500">
                            No department records available.
                        </p>
                    @endforelse

                </div>
            </div>

        </div>

        {{-- Expiring Contracts --}}
        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

            <div class="flex items-center justify-between border-b border-neutral-200 px-5 py-4 dark:border-neutral-700">
                <div>
                    <h2 class="font-semibold text-neutral-900 dark:text-white">
                        Expiring Contracts
                    </h2>

                    <p class="mt-1 text-sm text-neutral-500">
                        Contracts expiring within the next 30 days
                    </p>
                </div>

                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                    {{ $expiringContractsCount }} Contracts
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-neutral-50 text-xs uppercase text-neutral-500 dark:bg-neutral-800/50">
                        <tr>
                            <th class="px-5 py-3 font-medium">Contract Name</th>
                            <th class="px-5 py-3 font-medium">Department</th>
                            <th class="px-5 py-3 font-medium">End Date</th>
                            <th class="px-5 py-3 font-medium">Days Remaining</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">

                        @forelse ($expiringContracts as $contract)
                            @php
                                $daysRemaining = (int) now()
                                    ->startOfDay()
                                    ->diffInDays(
                                        \Carbon\Carbon::parse($contract->end_date)->startOfDay(),
                                        false
                                    );
                            @endphp

                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/40">
                                <td class="px-5 py-4 font-medium text-neutral-900 dark:text-white">
                                    {{ $contract->contract_name }}
                                </td>

                                <td class="px-5 py-4 text-neutral-600 dark:text-neutral-400">
                                    {{ $contract->uploader_dept ?? 'N/A' }}
                                </td>

                                <td class="px-5 py-4 text-neutral-600 dark:text-neutral-400">
                                    {{ \Carbon\Carbon::parse($contract->end_date)->format('M d, Y') }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold
                                        {{ $daysRemaining <= 7
                                            ? 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400'
                                            : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' }}">
                                        {{ $daysRemaining }} days
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-neutral-500">
                                    No contracts expiring within the next 30 days.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Contracts --}}
        <div class="overflow-hidden rounded-xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">

            <div class="border-b border-neutral-200 px-5 py-4 dark:border-neutral-700">
                <h2 class="font-semibold text-neutral-900 dark:text-white">
                    Recent Contracts
                </h2>

                <p class="mt-1 text-sm text-neutral-500">
                    Latest contracts uploaded or updated
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">

                    <thead class="bg-neutral-50 text-xs uppercase text-neutral-500 dark:bg-neutral-800/50">
                        <tr>
                            <th class="px-5 py-3 font-medium">Contract</th>
                            <th class="px-5 py-3 font-medium">Type</th>
                            <th class="px-5 py-3 font-medium">Department</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Date Added</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">

                        @forelse ($recentContracts as $contract)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/40">

                                <td class="px-5 py-4 font-medium text-neutral-900 dark:text-white">
                                    {{ $contract->contract_name }}
                                </td>

                                <td class="px-5 py-4 text-neutral-600 dark:text-neutral-400">
                                    {{ $contract->contract_type }}
                                </td>

                                <td class="px-5 py-4 text-neutral-600 dark:text-neutral-400">
                                    {{ $contract->uploader_dept ?? 'N/A' }}
                                </td>

                                <td class="px-5 py-4">
                                    @php
                                        $badgeColor = match (strtolower($contract->status ?? '')) {
                                            'active' => 'bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400',
                                            'expired' => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
                                            'suspended' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                                            default => 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300',
                                        };
                                    @endphp

                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badgeColor }}">
                                        {{ $contract->status }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-neutral-500">
                                    {{ $contract->created_at?->format('M d, Y') ?? 'N/A' }}
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-neutral-500">
                                    No recent contracts found.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>

    </div>

</x-layouts::app>
