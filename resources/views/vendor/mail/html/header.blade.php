@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            <img src="{{ public_path('assets/images/logo.png') }}" class="logo" alt="System Logo">
            <br />
            {{ $slot }}
        </a>
    </td>
</tr>
