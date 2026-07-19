@props(['value'])

<label {{ $attributes->merge(['class' => 'form-label small fw-semibold text-dark']) }}>
    {{ $value ?? $slot }}
</label>
