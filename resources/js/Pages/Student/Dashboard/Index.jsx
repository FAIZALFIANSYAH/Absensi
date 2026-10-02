import StudentLayout from '@/Layouts/StudentLayout';
import { Head } from '@inertiajs/react';
import QRCode from 'qrcode';
import { useEffect, useState } from 'react';

export default function StudentDashboard({ profile, classes }) {
    const [qrDataUrl, setQrDataUrl] = useState('');

    useEffect(() => {
        if (!profile?.qr_payload) {
            return;
        }

        QRCode.toDataURL(JSON.stringify(profile.qr_payload), {
            width: 280,
            margin: 1,
        }).then(setQrDataUrl);
    }, [profile]);

    return (
        <StudentLayout
            header={
                <div className="flex flex-col gap-2">
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        Dashboard Siswa
                    </h2>
                    <p className="text-sm text-slate-600">
                        Profil siswa dan QR identitas pribadi sudah aktif.
                    </p>
                </div>
            }
        >
            <Head title="Dashboard Siswa" />

            <div className="py-12">
                <div className="mx-auto grid max-w-7xl gap-6 sm:px-6 lg:grid-cols-[0.95fr_1.05fr] lg:px-8">
                    <div className="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Profil siswa
                        </h3>
                        <div className="mt-4 grid gap-4 sm:grid-cols-2">
                            <Info label="NIS" value={profile?.nis} />
                            <Info label="NIK" value={profile?.nik} />
                            <Info label="Telepon" value={profile?.phone} />
                            <Info
                                label="Gender"
                                value={
                                    profile?.gender === 'male'
                                        ? 'Laki-laki'
                                        : profile?.gender === 'female'
                                          ? 'Perempuan'
                                          : '-'
                                }
                            />
                            <Info label="Tanggal lahir" value={profile?.birth_date} />
                            <Info
                                label="QR dibuat"
                                value={
                                    profile?.qr_generated_at
                                        ? new Date(profile.qr_generated_at).toLocaleString('id-ID')
                                        : '-'
                                }
                            />
                        </div>
                        <div className="mt-4">
                            <p className="text-sm font-medium text-slate-900">Alamat</p>
                            <p className="mt-1 text-sm leading-6 text-slate-600">
                                {profile?.address || '-'}
                            </p>
                        </div>
                    </div>

                    <div className="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">
                            QR identitas siswa
                        </h3>
                        <p className="mt-2 text-sm text-slate-600">
                            QR ini menjadi identitas dasar yang akan dipakai di flow absensi.
                        </p>

                        <div className="mt-6 flex justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6">
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

                        <div className="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p className="text-sm font-medium text-slate-900">Token QR</p>
                            <p className="mt-2 break-all text-sm text-slate-600">
                                {maskToken(profile?.qr_token)}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="mx-auto mt-6 max-w-7xl sm:px-6 lg:px-8">
                    <div className="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">Kelas saya</h3>
                        <div className="mt-4 grid gap-4 md:grid-cols-3">
                            {classes.map((schoolClass) => (
                                <div key={schoolClass.id} className="rounded-2xl border border-slate-200 p-4">
                                    <p className="font-medium text-slate-900">{schoolClass.name}</p>
                                    <p className="mt-1 text-sm text-slate-600">
                                        {schoolClass.grade_level}
                                        {schoolClass.major ? ` - ${schoolClass.major}` : ''}
                                    </p>
                                    <p className="mt-1 text-sm text-slate-600">{schoolClass.academic_year}</p>
                                </div>
                            ))}
                            {classes.length === 0 && (
                                <p className="text-sm text-slate-500">Belum ada kelas yang ditetapkan untuk akun ini.</p>
                            )}
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

function Info({ label, value }) {
    return (
        <div className="rounded-2xl border border-slate-200 p-4">
            <p className="text-sm font-medium text-slate-900">{label}</p>
            <p className="mt-1 text-sm text-slate-600">{value || '-'}</p>
        </div>
    );
}
