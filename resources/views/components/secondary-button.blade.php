<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-outline-secondary rounded-pill px-4']) }}>
    {{ $slot }}
</button>
