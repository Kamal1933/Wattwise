@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan')

@php $breadcrumbs = [['Pengaturan', null]]; @endphp

@section('header-actions')
    <button type="button" class="btn-primary" data-dialog-open="dialog-add-household">
        <x-icon name="plus" class="size-4" />
        <span class="hidden sm:inline">Tambah rumah tangga</span>
    </button>
@endsection

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-navy">Rumah tangga</h2>
            <p class="mt-1 text-sm text-slate-500">Golongan tarif menentukan biaya per kWh dan batas daya. Samakan dengan rekening listrik PLN Anda.</p>
        </div>

        @forelse ($households as $item)
            @php
                $prefix = 'household-'.$item->id;
                $useOld = old('_household') === (string) $item->id;
                $isActive = $household?->id === $item->id;
            @endphp
            <section class="card p-6" aria-labelledby="{{ $prefix }}-title">
                <div class="flex flex-wrap items-center gap-3">
                    <h3 id="{{ $prefix }}-title" class="text-lg font-bold text-navy">{{ $item->name }}</h3>
                    @if ($isActive)
                        <span class="rounded-md bg-electric-50 px-2 py-0.5 text-xs font-semibold text-electric">Sedang dipakai</span>
                    @else
                        <a href="{{ route('settings.index', ['household_id' => $item->id]) }}" class="text-sm font-semibold text-electric hover:underline">Jadikan aktif</a>
                    @endif
                    <span class="ml-auto text-sm text-slate-500 tabular-nums">{{ $item->appliances_count }} peralatan</span>
                </div>

                <form method="POST" action="{{ route('settings.households.update', $item) }}" class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_household" value="{{ $item->id }}">
                    <div>
                        <label for="{{ $prefix }}-name" class="field-label">Nama</label>
                        <input id="{{ $prefix }}-name" name="name" type="text" required maxlength="150" class="field-input" value="{{ $useOld ? old('name') : $item->name }}">
                    </div>
                    <div>
                        <label for="{{ $prefix }}-tariff" class="field-label">Golongan tarif dan daya</label>
                        <select id="{{ $prefix }}-tariff" name="tariff_id" required class="field-input">
                            @foreach ($tariffs as $tariff)
                                <option value="{{ $tariff->id }}" @selected((int) ($useOld ? old('tariff_id') : $item->tariff_id) === $tariff->id)>
                                    {{ $tariff->code }}, {{ $number($tariff->power_limit_va, 0) }} VA, {{ $rupiah($tariff->rate_per_kwh, 2) }}/kWh
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="{{ $prefix }}-address" class="field-label">Alamat <span class="font-normal text-slate-400">(opsional)</span></label>
                        <input id="{{ $prefix }}-address" name="address" type="text" maxlength="255" class="field-input" value="{{ $useOld ? old('address') : $item->address }}">
                    </div>
                    <div>
                        <label for="{{ $prefix }}-occupants" class="field-label">Jumlah penghuni</label>
                        <input id="{{ $prefix }}-occupants" name="occupant_count" type="number" min="1" max="50" required class="field-input" value="{{ $useOld ? old('occupant_count') : $item->occupant_count }}">
                    </div>
                    @if ($useOld && $errors->any())
                        <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800 md:col-span-2">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    <div class="flex justify-end md:col-span-2">
                        <button type="submit" class="btn-primary">Simpan perubahan</button>
                    </div>
                </form>

                <form method="POST" action="{{ route('settings.households.destroy', $item) }}" class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4"
                      onsubmit="return confirm('Hapus {{ addslashes($item->name) }} beserta semua peralatan, riwayat, dan catatan meterannya?')">
                    @csrf
                    @method('DELETE')
                    <p class="text-sm text-slate-500">Menghapus rumah tangga juga menghapus peralatan, riwayat analisis, dan catatan meterannya.</p>
                    <button type="submit" class="btn-ghost text-red-700 hover:bg-red-50">
                        <x-icon name="trash" class="size-4" />
                        Hapus rumah tangga
                    </button>
                </form>
            </section>
        @empty
            @include('shared.no-household')
        @endforelse
    </div>
@endsection
