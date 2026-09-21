<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            @foreach ($rows as $row)
                <section class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10" wire:key="instructor-{{ $row['instructor']->id }}">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="font-semibold text-gray-950 dark:text-white">{{ $row['instructor']->user->name }}</h2>
                            <p class="text-sm text-gray-500">{{ $row['instructor']->user->email }}</p>
                        </div>
                        <span class="rounded-full px-2 py-1 text-xs {{ $row['instructor']->active ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ $row['instructor']->active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <dl class="mt-5 grid grid-cols-3 gap-3 text-sm">
                        <div><dt class="text-gray-500">Earned</dt><dd class="font-semibold">{{ number_format($row['balance']->earned / 100, 2) }} {{ $row['instructor']->currency }}</dd></div>
                        <div><dt class="text-gray-500">Paid</dt><dd class="font-semibold">{{ number_format($row['balance']->paid / 100, 2) }} {{ $row['instructor']->currency }}</dd></div>
                        <div><dt class="text-gray-500">Outstanding</dt><dd class="font-semibold text-amber-600">{{ number_format($row['balance']->outstanding() / 100, 2) }} {{ $row['instructor']->currency }}</dd></div>
                    </dl>
                    <div class="mt-5 border-t border-gray-200 pt-4 dark:border-white/10">
                        <h3 class="text-sm font-medium">Payout history</h3>
                        <div class="mt-2 space-y-2 text-sm">
                            @forelse ($row['payouts'] as $payout)
                                <div class="flex items-center justify-between" wire:key="payout-{{ $payout->id }}"><span>{{ number_format($payout->amount_minor / 100, 2) }} {{ $payout->currency }}</span><span class="text-gray-500">{{ $payout->status->value }}</span></div>
                            @empty
                                <p class="text-gray-500">No payouts yet.</p>
                            @endforelse
                        </div>
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
