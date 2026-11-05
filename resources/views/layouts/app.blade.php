<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
<script>
    window.addEventListener('load', function () {
        const loader = document.getElementById('pageLoader');

        if (!loader) return;

        // Wait briefly so the completed page transition feels smooth.
        setTimeout(function () {
            loader.classList.add('hidden');
            loader.classList.remove('flex');
        }, 250);
    });
</script>