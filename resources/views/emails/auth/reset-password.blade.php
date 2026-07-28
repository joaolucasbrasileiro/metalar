<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Redefina sua senha da Metalar</title>
</head>
<body style="margin:0; padding:0; background:#f4f4f5; color:#18181b; font-family:Arial, Helvetica, sans-serif;">
    <span style="display:none; max-height:0; overflow:hidden; opacity:0;">
        Recebemos uma solicitacao para redefinir sua senha.
    </span>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%; background:#f4f4f5; margin:0; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%; max-width:640px; border-collapse:collapse;">
                    <tr>
                        <td style="background:#ffffff; border:1px solid #d4d4d8; border-radius:8px; overflow:hidden;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%; border-collapse:collapse;">
                                <tr>
                                    <td style="padding:28px 36px 24px 36px; background:#ffffff; border-bottom:1px solid #e4e4e7;">
                                        <img src="{{ $logoUrl }}" width="156" alt="Metalar" style="display:block; width:156px; max-width:70%; height:auto; border:0;">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:34px 36px 16px 36px;">
                                        <p style="margin:0 0 10px 0; color:#3f3f46; font-size:12px; line-height:18px; font-weight:700; text-transform:uppercase; letter-spacing:.08em;">
                                            Seguranca da conta
                                        </p>
                                        <h1 style="margin:0; color:#18181b; font-size:28px; line-height:34px; font-weight:800;">
                                            Redefina sua senha
                                        </h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 36px 4px 36px;">
                                        <p style="margin:0 0 18px 0; color:#3f3f46; font-size:16px; line-height:26px; font-weight:600;">
                                            Ola, {{ $userName }}.
                                        </p>
                                        <p style="margin:0 0 18px 0; color:#52525b; font-size:15px; line-height:25px;">
                                            Recebemos uma solicitacao para redefinir a senha da sua conta. Clique no botao abaixo para criar uma nova senha.
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 36px 30px 36px;">
                                        <a href="{{ $resetUrl }}" style="display:inline-block; background:#18181b; color:#ffffff; border-radius:6px; padding:14px 22px; font-size:14px; line-height:18px; font-weight:800; text-decoration:none;">
                                            Criar nova senha
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 36px 34px 36px;">
                                        <p style="margin:0 0 12px 0; color:#71717a; font-size:13px; line-height:22px;">
                                            Este link expira em 60 minutos. Se voce nao solicitou essa alteracao, ignore este email.
                                        </p>
                                        <p style="margin:0; color:#a1a1aa; font-size:12px; line-height:20px; word-break:break-all;">
                                            {{ $resetUrl }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 4px 0 4px; text-align:center;">
                            <p style="margin:0; color:#71717a; font-size:12px; line-height:20px;">
                                Metalar - Email transacional enviado para proteger sua conta.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
