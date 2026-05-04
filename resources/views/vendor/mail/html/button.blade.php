<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td align="center">
                        <a href="{{ $url }}" 
                           style="
                               background-color: #2563eb;
                               border-radius: 6px;
                               color: #ffffff;
                               display: inline-block;
                               font-size: 16px;
                               font-weight: bold;
                               padding: 12px 30px;
                               text-decoration: none;
                           "
                        >
                            {{ $slot }}
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>