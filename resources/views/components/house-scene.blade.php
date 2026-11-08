@props(['variant' => 'banner'])

@if ($variant === 'solar')
    {{-- Sidebar: house with rooftop solar panels and an energy badge --}}
    <svg {{ $attributes }} viewBox="0 0 240 180" fill="none" aria-hidden="true">
        <defs>
            <radialGradient id="solar-glow" cx="0.5" cy="0.5" r="0.5">
                <stop offset="0" stop-color="#4ADE80" stop-opacity="0.55"/>
                <stop offset="1" stop-color="#4ADE80" stop-opacity="0"/>
            </radialGradient>
            <linearGradient id="solar-wall" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#E2E8F0"/>
                <stop offset="1" stop-color="#CBD5E1"/>
            </linearGradient>
        </defs>
        <circle cx="170" cy="34" r="30" fill="url(#solar-glow)"/>
        <circle cx="170" cy="34" r="15" fill="#172554" stroke="#4ADE80" stroke-width="2.5"/>
        <path d="M172 24l-7 11h6l-3 9 8-12h-6z" fill="#4ADE80"/>
        <path d="M170 49v14" stroke="#4ADE80" stroke-width="2" stroke-dasharray="3 3"/>
        <ellipse cx="120" cy="164" rx="112" ry="12" fill="#14532D" opacity="0.6"/>
        <path d="M8 164c30-10 70-12 112-12s82 2 112 12" fill="#166534"/>
        <circle cx="30" cy="132" r="16" fill="#15803D"/>
        <circle cx="42" cy="120" r="14" fill="#16A34A"/>
        <rect x="36" y="134" width="4" height="22" fill="#78350F"/>
        <path d="M60 104h120v54H60z" fill="url(#solar-wall)"/>
        <path d="M50 108l70-48 70 48z" fill="#1E293B"/>
        <path d="M74 98l46-31 46 31z" fill="#1D4ED8"/>
        <path d="M84 91l36-24M100 98l20-14M120 67v31M140 98l-20-14M156 91l-36-24M90 87h60M98 81h44" stroke="#60A5FA" stroke-width="1.2" opacity="0.8"/>
        <rect x="72" y="116" width="26" height="20" rx="2" fill="#FDE68A"/>
        <rect x="142" y="116" width="26" height="20" rx="2" fill="#FDE68A"/>
        <path d="M85 116v20M72 126h26M155 116v20M142 126h26" stroke="#CBD5E1" stroke-width="2"/>
        <rect x="108" y="122" width="24" height="36" rx="2" fill="#92400E"/>
        <circle cx="127" cy="141" r="1.8" fill="#FDE68A"/>
        <circle cx="206" cy="138" r="13" fill="#15803D"/>
        <rect x="204" y="146" width="4" height="12" fill="#78350F"/>
    </svg>
@else
    {{-- Welcome banner: modern two-storey house with lit windows --}}
    <svg {{ $attributes }} viewBox="0 0 360 220" fill="none" aria-hidden="true">
        <defs>
            <linearGradient id="banner-wall" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#F8FAFC"/>
                <stop offset="1" stop-color="#CBD5E1"/>
            </linearGradient>
            <linearGradient id="banner-window" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0" stop-color="#FEF3C7"/>
                <stop offset="1" stop-color="#FBBF24"/>
            </linearGradient>
            <radialGradient id="banner-glow" cx="0.5" cy="0.5" r="0.5">
                <stop offset="0" stop-color="#FDE68A" stop-opacity="0.45"/>
                <stop offset="1" stop-color="#FDE68A" stop-opacity="0"/>
            </radialGradient>
        </defs>
        <ellipse cx="180" cy="150" rx="150" ry="70" fill="url(#banner-glow)"/>
        <path d="M0 204c60-14 120-18 180-18s120 4 180 18v16H0z" fill="#166534"/>
        <path d="M0 210c60-10 120-12 180-12s120 2 180 12v10H0z" fill="#15803D"/>
        {{-- Trees --}}
        <rect x="40" y="150" width="6" height="46" fill="#78350F"/>
        <circle cx="43" cy="140" r="26" fill="#15803D"/>
        <circle cx="30" cy="128" r="18" fill="#16A34A"/>
        <rect x="318" y="160" width="5" height="36" fill="#78350F"/>
        <circle cx="320" cy="150" r="20" fill="#16A34A"/>
        {{-- Ground floor --}}
        <rect x="88" y="128" width="190" height="68" fill="url(#banner-wall)"/>
        <rect x="100" y="140" width="62" height="44" rx="3" fill="url(#banner-window)"/>
        <path d="M131 140v44M100 162h62" stroke="#E2E8F0" stroke-width="3"/>
        <rect x="174" y="146" width="28" height="50" rx="2" fill="#92400E"/>
        <circle cx="197" cy="172" r="2" fill="#FDE68A"/>
        <rect x="214" y="140" width="52" height="44" rx="3" fill="url(#banner-window)"/>
        <path d="M240 140v44" stroke="#E2E8F0" stroke-width="3"/>
        {{-- Upper floor --}}
        <rect x="104" y="72" width="150" height="56" fill="#F1F5F9"/>
        <rect x="118" y="84" width="54" height="34" rx="3" fill="url(#banner-window)"/>
        <path d="M145 84v34" stroke="#E2E8F0" stroke-width="3"/>
        <rect x="186" y="84" width="54" height="34" rx="3" fill="url(#banner-window)"/>
        <path d="M213 84v34" stroke="#E2E8F0" stroke-width="3"/>
        <rect x="98" y="124" width="162" height="6" rx="2" fill="#94A3B8"/>
        {{-- Roof --}}
        <path d="M86 76l92-52 98 52z" fill="#1E293B"/>
        <path d="M100 76l78-44 84 44" stroke="#334155" stroke-width="4"/>
        <rect x="222" y="34" width="14" height="26" fill="#475569"/>
        {{-- Plants --}}
        <circle cx="96" cy="190" r="9" fill="#22C55E"/>
        <circle cx="274" cy="190" r="9" fill="#22C55E"/>
    </svg>
@endif
