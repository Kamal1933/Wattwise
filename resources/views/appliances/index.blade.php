@extends('layouts.app')

@section('title', 'Data Peralatan Elektronik')
@section('page-title', 'Data Peralatan Elektronik')

@php $breadcrumbs = [['Data Peralatan Elektronik', null]]; @endphp

@section('header-actions')
    @if ($household)
        <button type="button" class="btn-primary" data-dialog-open="dialog-add-appliance">
            <x-icon name="plus" class="size-4" />
            <span class="hidden sm:inline">Tambah peralatan</span>
        </button>
    @endif
@endsection

@section('content')
    @if (! $household)
        @include('shared.no-household')
    @elseif ($household->appliances->isEmpty())
        @include('shared.no-appliances')
    @else
        @include('shared.appliance-table', ['appliances' => $household->appliances])
    @endif
@endsection
