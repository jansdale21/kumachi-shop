<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center min-h-[2.1rem] px-4 py-2 rounded-lg border border-transparent bg-[#7d583f] font-semibold text-sm text-white hover:bg-[#6a4632] focus:outline-none focus:ring-2 focus:ring-[#7d583f] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
