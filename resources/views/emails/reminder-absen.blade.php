<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judul }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);">

                    {{-- Header --}}
                    <tr>
                        <td style="background:#1d4ed8;padding:20px 24px;">
                            <div style="color:#ffffff;font-size:17px;font-weight:600;">⏰ Reminder Absen MagangHub</div>
                            <div style="color:#dbeafe;font-size:12px;margin-top:4px;">Pengingat otomatis 3× sehari (16.30 / 20.30 / 23.00 WITA)</div>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 12px;font-size:15px;color:#0f172a;">Halo, <strong>{{ $nama }}</strong>!</p>

                            @if($isLastSlot)
                                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px;margin:0 0 16px;">
                                    <p style="margin:0;font-size:14px;color:#991b1b;">
                                        <strong>⚠️ Peringatan terakhir.</strong> Absen MagangHub tutup jam <strong>{{ $deadline }}</strong>.
                                    </p>
                                </div>
                            @else
                                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px;margin:0 0 16px;">
                                    <p style="margin:0;font-size:14px;color:#92400e;">
                                        Kamu <strong>belum lapor absen</strong> hari ini. Batas: <strong>{{ $deadline }}</strong>.
                                    </p>
                                </div>
                            @endif

                            <p style="margin:0 0 20px;font-size:14px;color:#334155;line-height:1.6;">{{ $pesan }}</p>

                            {{-- Tombol --}}
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 12px;">
                                <tr>
                                    <td style="background:#1d4ed8;border-radius:8px;">
                                        <a href="{{ $dashboardUrl }}" style="display:inline-block;padding:12px 22px;color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;">Buka Dashboard Absen →</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 20px;font-size:13px;color:#64748b;">
                                Belum login? <a href="{{ $loginUrl }}" style="color:#1d4ed8;">Masuk ke Akun SIAPkerja</a> dulu.
                            </p>

                            {{-- Divider --}}
                            <div style="border-top:1px solid #e2e8f0;padding-top:16px;">
                                <p style="margin:0 0 8px;font-size:13px;color:#475569;">
                                    Sudah absen? Tandai supaya kamu berhenti dapat pengingat hari ini:
                                </p>
                                <table role="presentation" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="background:#059669;border-radius:8px;">
                                            <a href="{{ $appUrl }}/dashboard" style="display:inline-block;padding:10px 18px;color:#ffffff;font-size:13px;font-weight:600;text-decoration:none;">✅ Sudah Absen</a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#f8fafc;padding:16px 24px;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;font-size:12px;color:#64748b;line-height:1.6;">
                                Email ini dikirim otomatis karena kamu terdaftar di Reminder Absen MagangHub.<br>
                                Batas absen harian: <strong>tengah malam (00.00 WITA)</strong>.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>