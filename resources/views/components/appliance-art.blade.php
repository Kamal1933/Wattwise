@if ($photoUrl)
    <img src="{{ $photoUrl }}" alt="{{ $name ?? 'Foto peralatan' }}" loading="lazy" {{ $attributes->merge(['class' => $class.' rounded-lg object-cover bg-white']) }}>
@else
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 96 96" fill="none" role="img" aria-label="{{ $name ?? 'Peralatan listrik' }}">
    <ellipse cx="48" cy="88" rx="30" ry="4" fill="#0F172A" opacity="0.08"/>
    @switch($type)
        @case('ac')
            <rect x="8" y="24" width="80" height="34" rx="9" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2"/>
            <rect x="8" y="24" width="80" height="10" rx="9" fill="#F1F5F9"/>
            <rect x="16" y="46" width="64" height="5" rx="2.5" fill="#E2E8F0"/>
            <path d="M16 51h64" stroke="#94A3B8" stroke-width="1.5"/>
            <circle cx="76" cy="38" r="2.5" fill="#22C55E"/>
            <rect x="60" y="36" width="11" height="4" rx="2" fill="#BFDBFE"/>
            <path d="M24 64c2 5-2 9 0 14M40 64c2 5-2 9 0 14M56 64c2 5-2 9 0 14M72 64c2 5-2 9 0 14" stroke="#60A5FA" stroke-width="2.5" stroke-linecap="round"/>
            @break

        @case('fridge')
            <rect x="26" y="8" width="44" height="78" rx="7" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2"/>
            <path d="M26 34h44" stroke="#CBD5E1" stroke-width="2"/>
            <rect x="60" y="16" width="4" height="12" rx="2" fill="#94A3B8"/>
            <rect x="60" y="42" width="4" height="20" rx="2" fill="#94A3B8"/>
            <rect x="32" y="40" width="20" height="8" rx="2" fill="#DBEAFE"/>
            <circle cx="36" cy="44" r="1.5" fill="#2563EB"/>
            <path d="M30 86v-2M66 86v-2" stroke="#94A3B8" stroke-width="3" stroke-linecap="round"/>
            @break

        @case('washer')
            <rect x="18" y="10" width="60" height="76" rx="8" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2"/>
            <path d="M18 26h60" stroke="#E2E8F0" stroke-width="2"/>
            <rect x="24" y="15" width="18" height="6" rx="3" fill="#DBEAFE"/>
            <circle cx="64" cy="18" r="3.5" fill="#E2E8F0" stroke="#94A3B8" stroke-width="1.5"/>
            <circle cx="54" cy="18" r="2" fill="#22C55E"/>
            <circle cx="48" cy="56" r="21" fill="#E2E8F0" stroke="#94A3B8" stroke-width="2"/>
            <circle cx="48" cy="56" r="15" fill="#60A5FA"/>
            <path d="M34 58c5-4 9 4 14 0s9-4 14 0v6a15 15 0 01-28 0z" fill="#2563EB"/>
            <circle cx="43" cy="51" r="3" fill="#FFFFFF" opacity="0.7"/>
            @break

        @case('tv')
            <rect x="8" y="14" width="80" height="52" rx="5" fill="#172554"/>
            <rect x="12" y="18" width="72" height="44" rx="2" fill="#1E40AF"/>
            <path d="M12 52l20-16 14 10 14-14 24 20v8a2 2 0 01-2 2H14a2 2 0 01-2-2z" fill="#3B82F6"/>
            <circle cx="66" cy="28" r="5" fill="#FACC15"/>
            <path d="M14 20l18 0-18 14z" fill="#FFFFFF" opacity="0.12"/>
            <path d="M40 66l-4 14M56 66l4 14" stroke="#64748B" stroke-width="3" stroke-linecap="round"/>
            <rect x="30" y="78" width="36" height="4" rx="2" fill="#94A3B8"/>
            @break

        @case('lamp')
            <circle cx="48" cy="36" r="27" fill="#FEF9C3"/>
            <path d="M48 14a20 20 0 00-12 36c3 2.5 4 5 4 8v4h16v-4c0-3 1-5.5 4-8a20 20 0 00-12-36z" fill="#FDE047" stroke="#EAB308" stroke-width="2"/>
            <path d="M42 46l3 5h6l3-5" stroke="#CA8A04" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M41 24a12 12 0 016-4" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round"/>
            <rect x="39" y="62" width="18" height="6" rx="2" fill="#94A3B8"/>
            <rect x="40" y="68" width="16" height="5" rx="2" fill="#64748B"/>
            <path d="M44 76h8" stroke="#475569" stroke-width="3" stroke-linecap="round"/>
            @break

        @case('rice_cooker')
            <path d="M18 44h60v26a14 14 0 01-14 14H32a14 14 0 01-14-14z" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2"/>
            <path d="M16 44a32 18 0 0164 0z" fill="#F1F5F9" stroke="#CBD5E1" stroke-width="2"/>
            <rect x="38" y="20" width="20" height="7" rx="3.5" fill="#94A3B8"/>
            <path d="M18 54h60" stroke="#E2E8F0" stroke-width="2"/>
            <rect x="38" y="60" width="20" height="12" rx="3" fill="#DBEAFE"/>
            <circle cx="44" cy="66" r="2" fill="#EF4444"/>
            <circle cx="52" cy="66" r="2" fill="#22C55E"/>
            <path d="M36 14c-2-3 2-5 0-8M48 12c-2-3 2-5 0-8M60 14c-2-3 2-5 0-8" stroke="#94A3B8" stroke-width="2" stroke-linecap="round"/>
            @break

        @case('pump')
            <rect x="16" y="66" width="64" height="12" rx="3" fill="#94A3B8"/>
            <rect x="22" y="34" width="38" height="32" rx="8" fill="#2563EB"/>
            <path d="M28 40v20M34 40v20M40 40v20M46 40v20" stroke="#1D4ED8" stroke-width="2.5"/>
            <circle cx="66" cy="50" r="12" fill="#60A5FA" stroke="#2563EB" stroke-width="3"/>
            <circle cx="66" cy="50" r="4" fill="#FFFFFF"/>
            <path d="M66 38V22h12" stroke="#64748B" stroke-width="6" stroke-linejoin="round"/>
            <path d="M82 24c0 4-4 6-4 9a4 4 0 008 0c0-3-4-5-4-9z" fill="#38BDF8"/>
            @break

        @case('iron')
            <path d="M14 70c6-22 24-34 52-34h14v26a8 8 0 01-8 8z" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2"/>
            <path d="M14 70h58a8 8 0 008-8v-2H22c-4 0-7 4-8 10z" fill="#94A3B8"/>
            <path d="M44 36c0-10 6-16 16-16h20v16" stroke="#2563EB" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="62" cy="50" r="4" fill="#60A5FA"/>
            <path d="M26 82c2-3-2-5 0-8M38 84c2-3-2-5 0-8M50 84c2-3-2-5 0-8" stroke="#BFDBFE" stroke-width="2" stroke-linecap="round"/>
            @break

        @case('fan')
            <path d="M48 58v22" stroke="#94A3B8" stroke-width="5" stroke-linecap="round"/>
            <ellipse cx="48" cy="82" rx="18" ry="4" fill="#64748B"/>
            <circle cx="48" cy="34" r="26" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="2"/>
            <path d="M48 34c-2-12 6-20 12-16s-2 14-12 16zM48 34c12-2 20 6 16 12s-14-2-16-12zM48 34c-10 6-20 2-20-6s12-4 20 6z" fill="#60A5FA"/>
            <circle cx="48" cy="34" r="5" fill="#2563EB"/>
            <circle cx="48" cy="34" r="26" stroke="#94A3B8" stroke-width="1" stroke-dasharray="2 4"/>
            @break

        @case('router')
            <path d="M30 40V18M66 40V18" stroke="#64748B" stroke-width="4" stroke-linecap="round"/>
            <rect x="14" y="40" width="68" height="26" rx="8" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2"/>
            <circle cx="28" cy="53" r="2.5" fill="#22C55E"/>
            <circle cx="38" cy="53" r="2.5" fill="#22C55E"/>
            <circle cx="48" cy="53" r="2.5" fill="#60A5FA"/>
            <circle cx="58" cy="53" r="2.5" fill="#E2E8F0"/>
            <path d="M40 22a12 12 0 0116 0M36 16a18 18 0 0124 0" stroke="#2563EB" stroke-width="3" stroke-linecap="round"/>
            <circle cx="48" cy="27" r="2.5" fill="#2563EB"/>
            <path d="M22 66v6M74 66v6" stroke="#94A3B8" stroke-width="3" stroke-linecap="round"/>
            @break

        @case('heater')
            <rect x="26" y="10" width="44" height="66" rx="14" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2"/>
            <circle cx="48" cy="34" r="10" fill="#F1F5F9" stroke="#94A3B8" stroke-width="2"/>
            <path d="M48 34l5-5" stroke="#EF4444" stroke-width="2.5" stroke-linecap="round"/>
            <rect x="38" y="52" width="20" height="6" rx="3" fill="#FECACA"/>
            <path d="M38 76v8M58 76v8" stroke="#64748B" stroke-width="4" stroke-linecap="round"/>
            <circle cx="38" cy="86" r="3" fill="#EF4444"/>
            <circle cx="58" cy="86" r="3" fill="#3B82F6"/>
            @break

        @case('microwave')
            <rect x="10" y="22" width="76" height="48" rx="7" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2"/>
            <rect x="16" y="28" width="46" height="36" rx="4" fill="#1E3A8A"/>
            <path d="M18 30h18l-18 14z" fill="#FFFFFF" opacity="0.15"/>
            <rect x="68" y="30" width="12" height="6" rx="2" fill="#DBEAFE"/>
            <circle cx="74" cy="46" r="3" fill="#94A3B8"/>
            <circle cx="74" cy="56" r="3" fill="#94A3B8"/>
            <path d="M18 70v6M78 70v6" stroke="#94A3B8" stroke-width="3" stroke-linecap="round"/>
            @break

        @default
            <rect x="22" y="14" width="52" height="64" rx="12" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2"/>
            <circle cx="48" cy="46" r="16" fill="#F1F5F9" stroke="#94A3B8" stroke-width="2"/>
            <rect x="41" y="40" width="4" height="10" rx="2" fill="#475569"/>
            <rect x="51" y="40" width="4" height="10" rx="2" fill="#475569"/>
            <path d="M52 22l-6 9h6l-4 8" stroke="#FACC15" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
    @endswitch
</svg>
@endif
