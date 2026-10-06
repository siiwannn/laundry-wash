@props(['label', 'value', 'icon', 'note' => null, 'tone' => 'slate'])
<div class="metric-card">
    <span class="metric-icon metric-icon-{{ $tone }}" aria-hidden="true"><i class="bi bi-{{ $icon }}"></i></span>
    <div class="metric-label">{{ $label }}</div>
    <div class="metric-value">{{ $value }}</div>
    @if($note)<div class="metric-note">{{ $note }}</div>@endif
</div>
