@props([
    'name',
    'label',
    'icon',
    'type' => 'text',
    'value' => null,
])

@php
    $hasError = $errors->has($name);
    $errorId  = $name . '-error';
@endphp

<div class="field">
    <label for="{{ $name }}">{{ $label }}</label>

    <div class="input-wrap">
        <i class="ti ti-{{ $icon }}" aria-hidden="true"></i>

        <input
            {{ $attributes->merge([
                'type'             => $type,
                'id'               => $name,
                'name'             => $name,
                'aria-invalid'     => $hasError ? 'true' : 'false',
                'aria-describedby' => $hasError ? $errorId : null,
            ]) }}
            @if ($value !== null) value="{{ $value }}" @endif
        >
    </div>

    @if ($hasError)
        <p class="field-error" id="{{ $errorId }}">{{ $errors->first($name) }}</p>
    @endif
</div>
