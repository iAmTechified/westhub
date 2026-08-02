@props(['icon', 'label'])

<li class="flex items-center gap-4 text-[#3D3D3D] font-bold text-lg">
    <div class="w-10 h-10 rounded-xl bg-[#EAFAFF] flex items-center justify-center text-[#14ABD5] shadow-sm transform group-hover:scale-110 transition-transform">
        <{{ $icon }} class="w-5 h-5" />
    </div>
    {{ $label }}
</li>
