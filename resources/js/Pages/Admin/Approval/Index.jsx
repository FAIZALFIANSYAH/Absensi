import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function ApprovalIndex({ users, recentLogs, status }) {
    const approveForm = useForm({});
    const rejectForm = useForm({ reason: '' });
    const [selectedUser, setSelectedUser] = useState(null);

    const approve = (userId) => {
        approveForm.post(route('admin.approval.approve', userId));
    };

    const openRejectModal = (user) => {
        rejectForm.reset();
        setSelectedUser(user);
    };

    const closeRejectModal = () => {
        setSelectedUser(null);
        rejectForm.reset();
    };

    const submitReject = (event) => {
        event.preventDefault();

        if (!selectedUser) {
            return;
        }

        rejectForm.post(route('admin.approval.reject', selectedUser.id), {
            onSuccess: () => closeRejectModal(),
        });
    };

    return (
        <AdminLayout
            header={
                <div className="flex flex-col gap-2">
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        Approval User
                    </h2>
                    <p className="text-sm text-slate-600">
                        Tinjau pendaftar baru sebelum mereka bisa masuk ke dashboard utama.
                    </p>
                </div>
            }
        >
            <Head title="Approval User" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="grid gap-4 md:grid-cols-3">
                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <p className="text-sm text-slate-500">Menunggu approval</p>
                            <p className="mt-2 text-3xl font-semibold text-slate-900">
                                {users.length}
                            </p>
                        </div>
                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <p className="text-sm text-slate-500">Pendaftar siswa</p>
                            <p className="mt-2 text-3xl font-semibold text-slate-900">
                                {users.filter((user) => user.role === 'student').length}
                            </p>
                        </div>
                        <div className="rounded-2xl bg-white p-6 shadow-sm">
                            <p className="text-sm text-slate-500">Pendaftar guru</p>
                            <p className="mt-2 text-3xl font-semibold text-slate-900">
                                {users.filter((user) => user.role === 'teacher').length}
                            </p>
                        </div>
                    </div>

                    <div className="overflow-hidden rounded-2xl bg-white shadow-sm">
                        <div className="border-b border-slate-100 p-6">
                            <h3 className="text-lg font-semibold text-slate-900">
                                Pendaftar menunggu persetujuan
                            </h3>
                            <p className="mt-1 text-sm text-slate-600">
                                Superadmin meninjau akun siswa dan guru sebelum
                                mereka dapat mengakses dashboard.
                            </p>
                            {status && (
                                <p className="mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                                    {status}
                                </p>
                            )}
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200">
                                <thead className="bg-slate-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">
                                            Nama
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">
                                            Email
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">
                                            Role
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">
                                            Tanggal daftar
                                        </th>
                                        <th className="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-200 bg-white">
                                    {users.length === 0 ? (
                                        <tr>
                                            <td
                                                colSpan="5"
                                                className="px-6 py-10 text-center text-sm text-slate-500"
                                            >
                                                Tidak ada akun yang menunggu approval.
                                            </td>
                                        </tr>
                                    ) : (
                                        users.map((user) => (
                                            <tr key={user.id}>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">
                                                    {user.name}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                                    {user.email}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm capitalize text-slate-600">
                                                    <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium uppercase tracking-[0.12em] text-slate-700">
                                                        {user.role}
                                                    </span>
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                                    {new Date(user.created_at).toLocaleString(
                                                        'id-ID',
                                                    )}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-right text-sm">
                                                    <div className="flex justify-end gap-2">
                                                        <PrimaryButton
                                                            onClick={() => approve(user.id)}
                                                            disabled={approveForm.processing}
                                                        >
                                                            Approve
                                                        </PrimaryButton>
                                                        <DangerButton
                                                            onClick={() => openRejectModal(user)}
                                                            disabled={rejectForm.processing}
                                                        >
                                                            Reject
                                                        </DangerButton>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Audit log approval terbaru
                        </h3>
                        <div className="mt-4 space-y-3">
                            {recentLogs.length === 0 ? (
                                <p className="text-sm text-slate-500">
                                    Belum ada aktivitas approval yang tercatat.
                                </p>
                            ) : (
                                recentLogs.map((log) => (
                                    <div
                                        key={log.id}
                                        className="rounded-2xl border border-slate-200 p-4"
                                    >
                                        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <p className="text-sm font-semibold text-slate-900">
                                                    {log.action === 'approve' ? 'Approve' : 'Reject'}{' '}
                                                    untuk {log.user?.name ?? 'User tidak ditemukan'}
                                                </p>
                                                <p className="text-sm text-slate-600">
                                                    {log.user?.email} oleh {log.actor?.name ?? 'Admin'}
                                                </p>
                                            </div>
                                            <p className="text-xs uppercase tracking-[0.14em] text-slate-500">
                                                {new Date(log.created_at).toLocaleString('id-ID')}
                                            </p>
                                        </div>
                                        {log.reason && (
                                            <p className="mt-3 text-sm text-slate-600">
                                                Alasan: {log.reason}
                                            </p>
                                        )}
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>
            </div>

            <Modal show={selectedUser !== null} onClose={closeRejectModal} maxWidth="lg">
                <form onSubmit={submitReject} className="space-y-6 p-6">
                    <div>
                        <h3 className="text-lg font-semibold text-slate-900">
                            Tolak pendaftaran
                        </h3>
                        <p className="mt-2 text-sm leading-6 text-slate-600">
                            Berikan alasan jika perlu. Alasan ini membantu saat
                            admin perlu menjelaskan kenapa akun belum bisa diaktifkan.
                        </p>
                    </div>

                    <div className="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                        <p className="font-medium text-slate-900">
                            {selectedUser?.name}
                        </p>
                        <p>{selectedUser?.email}</p>
                    </div>

                    <div>
                        <label
                            htmlFor="reason"
                            className="text-sm font-medium text-slate-700"
                        >
                            Alasan penolakan
                        </label>
                        <TextInput
                            id="reason"
                            value={rejectForm.data.reason}
                            className="mt-2 block w-full"
                            onChange={(event) =>
                                rejectForm.setData('reason', event.target.value)
                            }
                        />
                        <InputError
                            message={rejectForm.errors.reason}
                            className="mt-2"
                        />
                    </div>

                    <div className="flex justify-end gap-3">
                        <SecondaryButton onClick={closeRejectModal}>
                            Batal
                        </SecondaryButton>
                        <DangerButton disabled={rejectForm.processing}>
                            Konfirmasi Reject
                        </DangerButton>
                    </div>
                </form>
            </Modal>
        </AdminLayout>
    );
}
