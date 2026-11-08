{{-- Expects: $recommendations (sorted), $severityCounts; optional $emptyMessage, $showFilter --}}
@if ($recommendations->isEmpty())
    <div class="card flex items-center gap-4 p-6 text-sm text-slate-600">
        <span class="grid size-10 shrink-0 place-items-center rounded-full bg-emerald-50 text-emerald-600">
            <x-icon name="check" />
        </span>
        {{ $emptyMessage ?? 'Tidak ada aturan yang terpicu. Pemakaian peralatan Anda sudah dalam batas wajar.' }}
    </div>
@else
    @if ($showFilter ?? true)
        <div class="mb-4 flex flex-wrap justify-end gap-1.5" role="group" aria-label="Saring berdasarkan tingkat">

                <button type="button" data-severity-filter="all" aria-pressed="true" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-600 aria-pressed:border-navy aria-pressed:bg-navy aria-pressed:text-white">
                    Semua <span class="tabular-nums">{{ $recommendations->count() }}</span>
                </button>
                @foreach ($severityMeta as $severity => $meta)
                    @if ($severityCounts->has($severity))
                        <button type="button" data-severity-filter="{{ $severity }}" aria-pressed="false" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-600 aria-pressed:border-navy aria-pressed:bg-navy aria-pressed:text-white">
                            {{ $meta['label'] }} <span class="tabular-nums">{{ $severityCounts[$severity] }}</span>
                        </button>
                    @endif
                @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        @foreach ($recommendations as $rec)
            @php
                $meta = $severityMeta[$rec->severity] ?? $severityMeta['low'];
                $steps = collect(preg_split('/\r\n|\r|\n/', (string) $rec->actionable_steps))
                    ->map(fn ($line) => trim(preg_replace('/^\d+[.)]\s*/', '', $line)))
                    ->filter();
            @endphp
            <article data-severity="{{ $rec->severity }}" class="card relative flex flex-col overflow-hidden p-5 pl-6">
                <span class="absolute inset-y-0 left-0 w-1 {{ $meta['rail'] }}" aria-hidden="true"></span>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-md px-2 py-0.5 text-xs font-semibold {{ $meta['pill'] }}">{{ $meta['label'] }}</span>
                    <span class="text-xs text-slate-400">{{ $rec->rule_code }}</span>
                    @if ($rec->estimated_cost_saving > 0)
                        <span class="ml-auto rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-800 tabular-nums">
                            Hemat {{ $rupiah($rec->estimated_cost_saving) }}/bln
                        </span>
                    @endif
                </div>

                <h3 class="mt-3 font-semibold text-navy">{{ $rec->title }}</h3>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ $rec->recommendation_text }}</p>

                @if ($steps->isNotEmpty())
                    <ol class="mt-4 space-y-2 text-sm text-slate-700">
                        @foreach ($steps as $step)
                            <li class="flex gap-2.5">
                                <span class="grid size-5 shrink-0 place-items-center rounded-full bg-electric-50 text-xs font-semibold text-electric">{{ $loop->iteration }}</span>
                                <span>{{ $step }}</span>
                            </li>
                        @endforeach
                    </ol>
                @endif

                <details class="group mt-4 border-t border-slate-100 pt-3">
                    <summary class="flex cursor-pointer list-none items-center gap-1.5 text-sm font-medium text-electric select-none hover:text-electric-600">
                        <x-icon name="chevron" class="size-4 transition-transform group-open:rotate-90" />
                        Kenapa saran ini muncul?
                    </summary>
                    <p class="mt-2 rounded-lg bg-ice p-3 text-sm leading-relaxed text-slate-600">{{ $rec->explanation_evidence }}</p>
                </details>
            </article>
        @endforeach
    </div>
@endif
