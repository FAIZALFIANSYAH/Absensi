import StudentLayout from '@/Layouts/StudentLayout';
import { Head } from '@inertiajs/react';
import QRCode from 'qrcode';
import { useEffect, useState } from 'react';

export default function StudentQrCode({ profile }) {
    const [qrDataUrl, setQrDataUrl] = useState('');

    useEffect(() => {
        if (!profile?.qr_payload) {
            return;
        }

        QRCode.toDataURL(JSON.stringify(profile.qr_payload), {
            width: 300,
            margin: 1,
        }).then(setQrDataUrl);
    }, [profile]);

    return (
        <StudentLayout
            header={
                <div className="flex flex-col gap-2">
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        QR Identity
                    </h2>
                    <p className="text-sm text-slate-600">
                        Halaman identitas QR pribadi siswa. Payload QR dibatasi ke token yang dibutuhkan untuk absensi.
                    </p>
                </div>
            }
        >
            <Head title="QR Identity" />

            <div className="py-12">
                <div className="mx-auto max-w-5xl sm:px-6 lg:px-8">
                    <div className="rounded-2xl bg-white p-6 shadow-sm">
                        <div className="flex justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6">
                            {qrDataUrl ? (
                                <img
                                    src={qrDataUrl}
                                    alt="QR identitas siswa"
                                    className="h-72 w-72 rounded-xl bg-white p-4 shadow-sm"
                                />
                            ) : (
                                <div className="flex h-72 w-72 items-center justify-center rounded-xl bg-white text-sm text-slate-500 shadow-sm">
                                    Menyiapkan QR...
                                </div>
                            )}
                        </div>

                        <div className="mt-6 grid gap-4 md:grid-cols-2">
                            <div className="rounded-2xl border border-slate-200 p-4">
                                <p className="text-sm font-medium text-slate-900">NIS</p>
                                <p className="mt-1 text-sm text-slate-600">{profile?.nis || '-'}</p>
                            </div>
                            <div className="rounded-2xl border border-slate-200 p-4">
                                <p className="text-sm font-medium text-slate-900">Token QR</p>
                                <p className="mt-1 break-all text-sm text-slate-600">{maskToken(profile?.qr_token)}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </StudentLayout>
    );
}

function maskToken(token) {
    if (!token) {
        return '-';
    }

    if (token.length <= 12) {
        return token;
    }

    return `${token.slice(0, 6)}...${token.slice(-4)}`;
}
