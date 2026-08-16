<h2>New Inquiry Received</h2>

<p>A new customer inquiry has been submitted with the following details:</p>

<table style="width: 100%; max-width: 600px; border-collapse: collapse; margin-top: 15px;">
    <tr>
        <td style="padding: 8px 0; font-weight: bold; width: 150px; vertical-align: top;">Reference:</td>
        <td style="padding: 8px 0;">{{ $inquiry->reference }}</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; font-weight: bold; vertical-align: top;">Customer:</td>
        <td style="padding: 8px 0;">{{ $inquiry->customer_name }}</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; font-weight: bold; vertical-align: top;">Email:</td>
        <td style="padding: 8px 0;">
            <a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a>
        </td>
    </tr>
    @if($inquiry->phone)
    <tr>
        <td style="padding: 8px 0; font-weight: bold; vertical-align: top;">Phone:</td>
        <td style="padding: 8px 0;">{{ $inquiry->phone }}</td>
    </tr>
    @endif
    <tr>
        <td style="padding: 8px 0; font-weight: bold; vertical-align: top;">Tour:</td>
        <td style="padding: 8px 0;">
            {{ $inquiry->tour->title ?? 'General Inquiry / Not specified' }}
        </td>
    </tr>
    <tr>
        <td style="padding: 8px 0; font-weight: bold; vertical-align: top;">Travel Date:</td>
        <td style="padding: 8px 0;">
            @if($inquiry->checkin_date)
                {{ \Carbon\Carbon::parse($inquiry->checkin_date)->format('d M Y') }}
                @if($inquiry->checkout_date)
                    → {{ \Carbon\Carbon::parse($inquiry->checkout_date)->format('d M Y') }}
                @endif
            @else
                Flexible
            @endif
        </td>
    </tr>
    @if($inquiry->number_of_adults || $inquiry->number_of_children)
    <tr>
        <td style="padding: 8px 0; font-weight: bold; vertical-align: top;">Guests:</td>
        <td style="padding: 8px 0;">
            {{ $inquiry->number_of_adults }} Adult(s)
            @if($inquiry->number_of_children > 0)
                , {{ $inquiry->number_of_children }} Child(ren)
            @endif
        </td>
    </tr>
    @endif
</table>

<h3 style="margin-top: 25px;">Message:</h3>
<div style="background: #f8f9fa; border-left: 4px solid #111844; padding: 15px; border-radius: 4px; font-family: inherit; font-size: 14px; color: #333; line-height: 1.6; white-space: pre-line;">
{{ $inquiry->message }}
</div>

<p style="margin-top: 30px; font-size: 13px; color: #666;">
    This is an automated notification. To reply to this customer, please log in to the admin panel and use the "Reply via Email" feature.
</p>
