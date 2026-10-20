<h1>{{ $heading }}</h1>
<p>{{ $bodyText }}</p>
@if ($details)
    <table role="presentation" cellspacing="0" cellpadding="8" border="0">
        @foreach ($details as $label => $value)
            @if ($value)
                <tr>
                    <th align="left">{{ $label }}</th>
                    <td>{{ $value }}</td>
                </tr>
            @endif
        @endforeach
    </table>
@endif
<p>Surat NX · {{ \App\Models\Setting::get('email') }}</p>