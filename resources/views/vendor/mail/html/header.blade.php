@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'MyanGo')
<img src="{{ asset('images/MyanGo_Logo.png') }}" class="logo" alt="MyanGo Logo">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
