<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
</head>
<body style="margin:0;padding:0;background-color:#ecece9;color:#111113;font-family:Arial,Helvetica,sans-serif;-webkit-text-size-adjust:100%;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#ecece9;">
        <tr><td align="center" style="padding:28px 16px 36px;">
            <!--[if mso]><table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"><tr><td><![endif]-->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:600px;">
                <tr><td style="padding:0 4px 18px;">
                    <a href="{{ config('site.url') }}/{{ $lang }}" style="color:#111113;text-decoration:none;font-size:14px;font-weight:bold;letter-spacing:-0.2px;">fernandovasquez<span style="color:#ba1f5a;">.</span>tech</a>
                </td></tr>
                <tr><td style="background-color:#111113;border-radius:26px 26px 0 0;padding:32px 32px 30px;color:#ffffff;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
                        {{-- The memoji (trimmed, same file as the favicon); mail clients need an absolute URL --}}
                        <td width="32" valign="middle" style="width:32px;line-height:0;"><img src="{{ config('site.url') }}/favicon-192.png" width="32" height="32" alt="" style="display:block;width:32px;height:32px;border:0;outline:none;text-decoration:none;"></td>
                        <td valign="middle" style="padding-left:10px;color:#c6c6ca;font-size:11px;line-height:16px;letter-spacing:1.4px;text-transform:uppercase;">{{ $lang === 'en' ? 'A message from' : 'Un mensaje de' }}</td>
                    </tr></table>
                    <div style="padding-top:20px;font-size:32px;line-height:1.1;font-weight:bold;letter-spacing:-1.3px;color:#ffffff;">Fernando Vásquez<span style="color:#f95083;">.</span></div>
                    <div style="padding-top:9px;color:#b2b2b7;font-size:13px;line-height:1.5;">{{ $lang === 'en' ? 'Development & interface design' : 'Desarrollo y diseño de interfaces' }}</div>
                </td></tr>
                <tr><td height="5" style="height:5px;background-color:#f95083;background-image:linear-gradient(90deg,#c262bf 0%,#f95083 50%,#fe7b58 100%);font-size:0;line-height:0;">&nbsp;</td></tr>
                <tr><td style="background-color:#ffffff;padding:36px 32px 40px;border-radius:0 0 26px 26px;font-size:16px;line-height:1.65;color:#26262a;overflow-wrap:anywhere;word-break:break-word;">{!! nl2br(e($body)) !!}</td></tr>
                <tr><td style="padding:22px 5px 0;color:#5d5d63;font-size:12px;line-height:1.6;">
                    <a href="{{ config('site.url') }}/{{ $lang }}" style="color:#5d5d63;text-decoration:underline;">fernandovasquez.tech</a>
                    <span style="color:#a0a0a4;">&nbsp; · &nbsp;</span>{{ $lang === 'en' ? 'Made with care in Guatemala' : 'Hecho con dedicación en Guatemala' }}
                </td></tr>
            </table>
            <!--[if mso]></td></tr></table><![endif]-->
        </td></tr>
    </table>
</body>
</html>
