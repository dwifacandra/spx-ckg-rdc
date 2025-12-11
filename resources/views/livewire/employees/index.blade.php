<div>
    <div class="bg-white dark:bg-zinc-800 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <h2 class="text-2xl font-semibold mb-4">Employees</h2>

            @if (session()->has('message'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session("message") }}
            </div>
            @endif

            <div class="mb-4 flex flex-col sm:flex-row gap-4 items-stretch sm:items-center">
                <div class="flex-1">
                    <flux:input icon="magnifying-glass" placeholder="Search Employees" wire:model.live="search" />
                </div>
                <flux:button.group>
                    <flux:button variant="outline" icon="arrow-down-tray" wire:click="downloadTemplate">
                        Download Template
                    </flux:button>
                    <flux:modal.trigger name="import-modal">
                        <flux:button variant="outline" icon="arrow-up-tray">
                            Import
                        </flux:button>
                    </flux:modal.trigger>
                </flux:button.group>
            </div>

            <div class="overflow-x-auto border-t border-neutral-200 dark:border-neutral-700">
                <table class="min-w-full" wire:poll>
                    <thead class="bg-neutral-50 dark:bg-neutral-700">
                        <tr>
                            @foreach([
                            'Ops ID',
                            'Staff Name',
                            'Gender',
                            'Contract Type',
                            'Agency',
                            'Joined Date',
                            'Ops Status',
                            'Employee ID',
                            'Passport ID',
                            ] as $header)
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">
                                {{ $header }}
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse($employees as $employee)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $employee->ops_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300">
                                {{ $employee->staff_name }}
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300 capitalize">
                                {{ $employee->gender }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300">
                                {{ $employee->contract_type }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300">
                                {{ $employee->agency }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300">
                                @if ($employee->joined_date)
                                {{ $employee->joined_date->format('d/m/Y') }}
                                @else
                                N/A
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300">
                                @php
                                $status = strtolower($employee->ops_status);
                                $color = match ($status) {
                                'active' => 'bg-green-100 text-green-800 dark:bg-green-700/30 dark:text-green-300',
                                'inactive' => 'bg-red-100 text-red-800 dark:bg-red-700/30 dark:text-red-300',
                                'on leave' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-700/30
                                dark:text-yellow-300',
                                default => 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700/30
                                dark:text-neutral-300',
                                };
                                @endphp
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                    {{ ucfirst($employee->ops_status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300">
                                {{ $employee->employee_id ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-300">
                                {{ $employee->passport_id ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10"
                                class="px-6 py-4 whitespace-nowrap text-sm text-neutral-500 dark:text-neutral-300 text-center">
                                Tidak ada data karyawan yang ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $employees->links() }}
            </div>
        </div>
    </div>
    <livewire:employees.import />
</div>
