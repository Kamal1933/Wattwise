@props(['user', 'size' => 'size-8', 'text' => 'text-xs'])
@php $photo = $user->photoUrl(); @endphp
@if ($photo)
    <img src="{{ $photo }}" alt="Foto {{ $user->name }}" {{ $attributes->class([$size, 'shrink-0 rounded-full object-cover']) }}>
@else
    <span aria-hidden="true" {{ $attributes->class([$size, $text, 'grid shrink-0 place-items-center rounded-full bg-gradient-to-br from-sky-400 to-electric font-bold text-white']) }}>{{ $user->initials() }}</span>
@endif
