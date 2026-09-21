<div x-data="{ open: true }" class="mx-auto max-w-6xl space-y-6 p-6">
    <div class="flex items-end justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-slate-900">Instructor balances</h1><p class="text-sm text-slate-500">Livewire view of the append-only earning ledger.</p></div>
        <button type="button" x-on:click="open = ! open" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white" x-text="open ? 'Collapse' : 'Expand'"></button>
    </div>
    <div x-show="open" x-transition class="space-y-4">
        <label class="block"><span class="text-sm font-medium text-slate-700">Search instructor</span><input wire:model.live.debounce.300ms="search" class="mt-1 w-full rounded-lg border-slate-300" placeholder="Name"></label>
        <div class="grid gap-4 md:grid-cols-2">
            @foreach($rows as $row)
                <article wire:key="balance-{{ $row['instructor']->id }}" class="rounded-xl bg-white p-5 shadow ring-1 ring-slate-200">
                    <h2 class="font-semibold">{{ $row['instructor']->user->name }}</h2><p class="text-sm text-slate-500">{{ $row['instructor']->user->email }}</p>
                    <div class="mt-4 grid grid-cols-3 gap-3 text-sm"><div><span class="text-slate-500">Earned</span><strong class="block">{{ number_format($row['balance']->earned/100,2) }}</strong></div><div><span class="text-slate-500">Paid</span><strong class="block">{{ number_format($row['balance']->paid/100,2) }}</strong></div><div><span class="text-slate-500">Outstanding</span><strong class="block text-amber-600">{{ number_format($row['balance']->outstanding()/100,2) }}</strong></div></div>
                </article>
            @endforeach
        </div>
    </div>
</div>
