@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand
        name=""
        logo="{{ asset('images/cms.png') }}"
        {{ $attributes }}
        class="w-full justify-center mx-auto"
    />
@else
    <flux:brand
        name=""
        logo="{{ asset('images/cms.png') }}"
        {{ $attributes }}
        class="w-full justify-center mx-auto"
    />
@endif