<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center min-h-[2.1rem] px-4 py-2 rounded-lg border border-[#d9c2ad] bg-[#f8f2ea] font-semibold text-sm text-[#5f3f2a] hover:bg-[#f0e3d5] focus:outline-none focus:ring-2 focus:ring-[#7d583f] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
