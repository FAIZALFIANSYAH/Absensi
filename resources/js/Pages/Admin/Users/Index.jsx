import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function AdminUsersIndex({ users, filters, options, summary, flashStatus }) {
    const filterForm = useForm({
        search: filters.search ?? '',
        role: filters.role ?? '',
        approval_status: filters.approval_status ?? '',
        active_status: filters.active_status ?? '',
    });

    const createForm = useForm({
        name: '',
        email: '',
        role: 'student',
        approval_status: 'approved',
        password: '',
        password_confirmation: '',
        nis: '',
        nip: '',
        nik: '',
        phone: '',
        gender: '',
        birth_date: '',
        address: '',
    });

    const submitFilters = (event) => {
        event.preventDefault();
        router.get(route('admin.users.index'), filterForm.data, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const resetFilters = () => {
        const next = {
            search: '',
            role: '',
            approval_status: '',
            active_status: '',
        };

        filterForm.setData(next);

        router.get(route('admin.users.index'), next, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    return (
        <AdminLayout
            header={
                <div className="flex flex-col gap-2">
                    <h2 className="text-xl font-semibold leading-tight text-slate-900">
                        User Management
                    </h2>
                    <p className="text-sm text-slate-600">
                        Pusat operasional superadmin untuk mengelola akun, approval, status aktif, dan reset password via email.
                    </p>
                </div>
            }
        >
            <Head title="User Management" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    {flashStatus && (
                        <div className="rounded-2xl border border-cyan-200 bg-cyan-50 px-4 py-3 text-sm font-medium text-cyan-700">
                            {flashStatus}
                        </div>
                    )}

                    <div className="grid gap-4 md:grid-cols-4">
                        <SummaryCard label="Total User" value={summary.total} />
                        <SummaryCard label="Pending" value={summary.pending} />
                        <SummaryCard label="Aktif" value={summary.active} />
                        <SummaryCard label="Nonaktif" value={summary.inactive} />
                    </div>

                    <div className="rounded-2xl bg-white p-6 shadow-sm">
                        <div className="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                            <div>
                                <h3 className="text-lg font-semibold text-slate-900">Search & Filter</h3>
                                <p className="text-sm text-slate-600">
                                    Cari user berdasarkan nama atau email, lalu sempitkan hasil berdasarkan role, approval, dan status aktif.
                                </p>
                            </div>
                        </div>

                        <form onSubmit={submitFilters} className="mt-4 grid gap-4 md:grid-cols-4">
                            <Field label="Cari">
                                <input
                                    value={filterForm.data.search}
                                    onChange={(event) => filterForm.setData('search', event.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    placeholder="Nama atau email"
                                />
                            </Field>
                            <Field label="Role">
                                <select
                                    value={filterForm.data.role}
                                    onChange={(event) => filterForm.setData('role', event.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    <option value="">Semua role</option>
                                    {options.roles.map((role) => (
                                        <option key={role.value} value={role.value}>
                                            {role.label}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <Field label="Approval">
                                <select
                                    value={filterForm.data.approval_status}
                                    onChange={(event) => filterForm.setData('approval_status', event.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    <option value="">Semua status</option>
                                    {options.approvalStatuses.map((status) => (
                                        <option key={status.value} value={status.value}>
                                            {status.label}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <Field label="Aktivasi">
                                <select
                                    value={filterForm.data.active_status}
                                    onChange={(event) => filterForm.setData('active_status', event.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    <option value="">Semua status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </Field>

                            <div className="md:col-span-4 flex flex-wrap gap-3">
                                <button
                                    type="submit"
                                    className="rounded-full bg-cyan-700 px-4 py-2 text-sm font-medium text-white"
                                    disabled={filterForm.processing}
                                >
                                    Terapkan Filter
                                </button>
                                <button
                                    type="button"
                                    onClick={resetFilters}
                                    className="rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
                                >
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>

                    <div className="rounded-2xl bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">Create User</h3>
                        <p className="mt-1 text-sm text-slate-600">
                            Buat akun teacher atau student secara manual. Reset password operasional tetap dilakukan via email link.
                        </p>

                        <form
                            onSubmit={(event) => {
                                event.preventDefault();
                                createForm.post(route('admin.users.store'), {
                                    preserveScroll: true,
                                    onSuccess: () => createForm.reset('name', 'email', 'password', 'password_confirmation', 'nis', 'nip', 'nik', 'phone', 'gender', 'birth_date', 'address'),
                                });
                            }}
                            className="mt-4 grid gap-4 md:grid-cols-2"
                        >
                            <Field label="Nama" error={createForm.errors.name}>
                                <input
                                    value={createForm.data.name}
                                    onChange={(event) => createForm.setData('name', event.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />
                            </Field>
                            <Field label="Email" error={createForm.errors.email}>
                                <input
                                    type="email"
                                    value={createForm.data.email}
                                    onChange={(event) => createForm.setData('email', event.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />
                            </Field>
                            <Field label="Role" error={createForm.errors.role}>
                                <select
                                    value={createForm.data.role}
                                    onChange={(event) => {
                                        const role = event.target.value;
                                        createForm.setData({
                                            ...createForm.data,
                                            role,
                                            nis: role === 'student' ? createForm.data.nis : '',
                                            nip: role === 'teacher' ? createForm.data.nip : '',
                                        });
                                    }}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    {options.roles
                                        .filter((role) => role.value !== 'superadmin')
                                        .map((role) => (
                                            <option key={role.value} value={role.value}>
                                                {role.label}
                                            </option>
                                        ))}
                                </select>
                            </Field>
                            <Field label="Approval awal" error={createForm.errors.approval_status}>
                                <select
                                    value={createForm.data.approval_status}
                                    onChange={(event) => createForm.setData('approval_status', event.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    {options.approvalStatuses
                                        .filter((status) => status.value !== 'rejected')
                                        .map((status) => (
                                            <option key={status.value} value={status.value}>
                                                {status.label}
                                            </option>
                                        ))}
                                </select>
                            </Field>
                            {createForm.data.role === 'student' && (
                                <Field label="NIS" error={createForm.errors.nis}>
                                    <input
                                        value={createForm.data.nis}
                                        onChange={(event) => createForm.setData('nis', event.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    />
                                </Field>
                            )}
                            {createForm.data.role === 'teacher' && (
                                <Field label="NIP" error={createForm.errors.nip}>
                                    <input
                                        value={createForm.data.nip}
                                        onChange={(event) => createForm.setData('nip', event.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    />
                                </Field>
                            )}
                            <Field label="NIK" error={createForm.errors.nik}>
                                <input
                                    value={createForm.data.nik}
                                    onChange={(event) => createForm.setData('nik', event.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />
                            </Field>
                            <Field label="Telepon" error={createForm.errors.phone}>
                                <input
                                    value={createForm.data.phone}
                                    onChange={(event) => createForm.setData('phone', event.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />
                            </Field>
                            <Field label="Gender" error={createForm.errors.gender}>
                                <select
                                    value={createForm.data.gender}
                                    onChange={(event) => createForm.setData('gender', event.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                >
                                    <option value="">Pilih gender</option>
                                    {options.genders.map((gender) => (
                                        <option key={gender.value} value={gender.value}>
                                            {gender.label}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <Field label="Tanggal lahir" error={createForm.errors.birth_date}>
                                <input
                                    type="date"
                                    value={createForm.data.birth_date}
                                    onChange={(event) => createForm.setData('birth_date', event.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />
                            </Field>
                            <Field label="Password awal" error={createForm.errors.password}>
                                <input
                                    type="password"
                                    value={createForm.data.password}
                                    onChange={(event) => createForm.setData('password', event.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />
                            </Field>
                            <Field label="Konfirmasi password" error={createForm.errors.password_confirmation}>
                                <input
                                    type="password"
                                    value={createForm.data.password_confirmation}
                                    onChange={(event) => createForm.setData('password_confirmation', event.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />
                            </Field>
                            <Field label="Alamat" error={createForm.errors.address} className="md:col-span-2">
                                <textarea
                                    value={createForm.data.address}
                                    onChange={(event) => createForm.setData('address', event.target.value)}
                                    rows="3"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />
                            </Field>
                            <div className="md:col-span-2">
                                <button
                                    type="submit"
                                    className="rounded-full bg-cyan-700 px-4 py-2 text-sm font-medium text-white"
                                    disabled={createForm.processing}
                                >
                                    Simpan User
                                </button>
                            </div>
                        </form>
                    </div>

                    <div className="space-y-4">
                        {users.map((user) => (
                            <UserCard key={user.id} user={user} options={options} />
                        ))}

                        {users.length === 0 && (
                            <div className="rounded-2xl bg-white p-6 text-sm text-slate-600 shadow-sm">
                                Tidak ada user yang cocok dengan filter saat ini.
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}

function UserCard({ user, options }) {
    const [editing, setEditing] = useState(false);
    const updateForm = useForm({
        name: user.name,
        email: user.email,
        nis: user.profile.nis ?? '',
        nip: user.profile.nip ?? '',
        nik: user.profile.nik ?? '',
        phone: user.profile.phone ?? '',
        gender: user.profile.gender ?? '',
        birth_date: user.profile.birth_date ?? '',
        address: user.profile.address ?? '',
    });
    const rejectForm = useForm({
        reason: user.rejection_reason ?? '',
    });

    const submitUpdate = (event) => {
        event.preventDefault();
        updateForm.patch(route('admin.users.update', user.id), {
            preserveScroll: true,
            onSuccess: () => setEditing(false),
        });
    };

    const submitReject = () => {
        rejectForm.post(route('admin.users.reject', user.id), {
            preserveScroll: true,
        });
    };

    return (
        <div className="rounded-2xl bg-white p-6 shadow-sm">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div className="space-y-3">
                    <div>
                        <h3 className="text-lg font-semibold text-slate-900">{user.name}</h3>
                        <p className="text-sm text-slate-600">{user.email}</p>
                    </div>
                    <div className="flex flex-wrap gap-2 text-xs font-semibold uppercase tracking-[0.14em]">
                        <Badge>{user.role}</Badge>
                        <Badge>{user.approval_status}</Badge>
                        <Badge tone={user.is_active ? 'emerald' : 'rose'}>
                            {user.is_active ? 'active' : 'inactive'}
                        </Badge>
                    </div>
                    <div className="grid gap-2 text-sm text-slate-600 md:grid-cols-2">
                        {user.profile.nis && <InfoLine label="NIS" value={user.profile.nis} />}
                        {user.profile.nip && <InfoLine label="NIP" value={user.profile.nip} />}
                        <InfoLine label="NIK" value={user.profile.nik || '-'} />
                        <InfoLine label="Telepon" value={user.profile.phone || '-'} />
                        <InfoLine label="Gender" value={formatGender(user.profile.gender)} />
                        <InfoLine label="Tgl lahir" value={user.profile.birth_date || '-'} />
                        <InfoLine label="Dibuat" value={user.created_at ? new Date(user.created_at).toLocaleString('id-ID') : '-'} />
                        <InfoLine label="Nonaktif" value={user.deactivated_at ? new Date(user.deactivated_at).toLocaleString('id-ID') : '-'} />
                    </div>
                    {user.profile.address && (
                        <p className="text-sm leading-6 text-slate-600">{user.profile.address}</p>
                    )}
                    {user.rejection_reason && (
                        <div className="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                            Alasan reject: {user.rejection_reason}
                        </div>
                    )}
                </div>

                <div className="flex flex-wrap gap-2 lg:max-w-sm lg:justify-end">
                    {user.can.update && (
                        <button
                            type="button"
                            onClick={() => setEditing((value) => !value)}
                            className="rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
                        >
                            {editing ? 'Tutup Edit' : 'Edit'}
                        </button>
                    )}
                    {user.can.approve && (
                        <button
                            type="button"
                            onClick={() => router.post(route('admin.users.approve', user.id), {}, { preserveScroll: true })}
                            className="rounded-full bg-emerald-700 px-4 py-2 text-sm font-medium text-white"
                        >
                            Approve
                        </button>
                    )}
                    {user.can.deactivate && (
                        <button
                            type="button"
                            onClick={() => router.post(route('admin.users.deactivate', user.id), {}, { preserveScroll: true })}
                            className="rounded-full bg-rose-700 px-4 py-2 text-sm font-medium text-white"
                        >
                            Deactivate
                        </button>
                    )}
                    {user.can.activate && (
                        <button
                            type="button"
                            onClick={() => router.post(route('admin.users.activate', user.id), {}, { preserveScroll: true })}
                            className="rounded-full bg-emerald-700 px-4 py-2 text-sm font-medium text-white"
                        >
                            Activate
                        </button>
                    )}
                    {user.can.send_reset_link && (
                        <button
                            type="button"
                            onClick={() => router.post(route('admin.users.password.reset-link', user.id), {}, { preserveScroll: true })}
                            className="rounded-full bg-cyan-700 px-4 py-2 text-sm font-medium text-white"
                        >
                            Kirim Reset Link
                        </button>
                    )}
                    {user.can.delete && (
                        <button
                            type="button"
                            onClick={() => {
                                if (window.confirm(`Hapus user ${user.name}?`)) {
                                    router.delete(route('admin.users.destroy', user.id), {
                                        preserveScroll: true,
                                    });
                                }
                            }}
                            className="rounded-full border border-rose-300 px-4 py-2 text-sm font-medium text-rose-700"
                        >
                            Delete
                        </button>
                    )}
                </div>
            </div>

            {(user.can.reject || user.can.update) && (
                <div className="mt-5 grid gap-4 lg:grid-cols-2">
                    {user.can.reject && (
                        <div className="rounded-2xl border border-slate-200 p-4">
                            <h4 className="text-sm font-semibold uppercase tracking-[0.14em] text-slate-700">
                                Reject User
                            </h4>
                            <label className="mt-3 block text-sm font-medium text-slate-700">
                                Alasan reject
                                <textarea
                                    value={rejectForm.data.reason}
                                    onChange={(event) => rejectForm.setData('reason', event.target.value)}
                                    rows="3"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />
                            </label>
                            {rejectForm.errors.reason && (
                                <p className="mt-1 text-sm text-rose-600">{rejectForm.errors.reason}</p>
                            )}
                            <button
                                type="button"
                                onClick={submitReject}
                                className="mt-3 rounded-full border border-amber-300 px-4 py-2 text-sm font-medium text-amber-700"
                            >
                                Reject
                            </button>
                        </div>
                    )}

                    {editing && user.can.update && (
                        <form onSubmit={submitUpdate} className="rounded-2xl border border-slate-200 p-4">
                            <h4 className="text-sm font-semibold uppercase tracking-[0.14em] text-slate-700">
                                Edit User
                            </h4>
                            <div className="mt-3 grid gap-4 md:grid-cols-2">
                                <Field label="Nama" error={updateForm.errors.name}>
                                    <input
                                        value={updateForm.data.name}
                                        onChange={(event) => updateForm.setData('name', event.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    />
                                </Field>
                                <Field label="Email" error={updateForm.errors.email}>
                                    <input
                                        type="email"
                                        value={updateForm.data.email}
                                        onChange={(event) => updateForm.setData('email', event.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    />
                                </Field>
                                {user.role === 'student' && (
                                    <Field label="NIS" error={updateForm.errors.nis}>
                                        <input
                                            value={updateForm.data.nis}
                                            onChange={(event) => updateForm.setData('nis', event.target.value)}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        />
                                    </Field>
                                )}
                                {user.role === 'teacher' && (
                                    <Field label="NIP" error={updateForm.errors.nip}>
                                        <input
                                            value={updateForm.data.nip}
                                            onChange={(event) => updateForm.setData('nip', event.target.value)}
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                        />
                                    </Field>
                                )}
                                <Field label="NIK" error={updateForm.errors.nik}>
                                    <input
                                        value={updateForm.data.nik}
                                        onChange={(event) => updateForm.setData('nik', event.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    />
                                </Field>
                                <Field label="Telepon" error={updateForm.errors.phone}>
                                    <input
                                        value={updateForm.data.phone}
                                        onChange={(event) => updateForm.setData('phone', event.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    />
                                </Field>
                                <Field label="Gender" error={updateForm.errors.gender}>
                                    <select
                                        value={updateForm.data.gender}
                                        onChange={(event) => updateForm.setData('gender', event.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    >
                                        <option value="">Pilih gender</option>
                                        {options.genders.map((gender) => (
                                            <option key={gender.value} value={gender.value}>
                                                {gender.label}
                                            </option>
                                        ))}
                                    </select>
                                </Field>
                                <Field label="Tanggal lahir" error={updateForm.errors.birth_date}>
                                    <input
                                        type="date"
                                        value={updateForm.data.birth_date}
                                        onChange={(event) => updateForm.setData('birth_date', event.target.value)}
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    />
                                </Field>
                                <Field label="Alamat" error={updateForm.errors.address} className="md:col-span-2">
                                    <textarea
                                        value={updateForm.data.address}
                                        onChange={(event) => updateForm.setData('address', event.target.value)}
                                        rows="3"
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                    />
                                </Field>
                            </div>
                            <div className="mt-4">
                                <button
                                    type="submit"
                                    disabled={updateForm.processing}
                                    className="rounded-full bg-cyan-700 px-4 py-2 text-sm font-medium text-white"
                                >
                                    Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    )}
                </div>
            )}
        </div>
    );
}

function SummaryCard({ label, value }) {
    return (
        <div className="rounded-2xl bg-white p-5 shadow-sm">
            <p className="text-sm font-medium text-slate-500">{label}</p>
            <p className="mt-2 text-2xl font-semibold text-slate-900">{value}</p>
        </div>
    );
}

function Badge({ children, tone = 'slate' }) {
    const tones = {
        slate: 'bg-slate-100 text-slate-700',
        emerald: 'bg-emerald-100 text-emerald-700',
        rose: 'bg-rose-100 text-rose-700',
    };

    return (
        <span className={`rounded-full px-3 py-1 ${tones[tone] ?? tones.slate}`}>
            {children}
        </span>
    );
}

function InfoLine({ label, value }) {
    return (
        <p>
            <span className="font-medium text-slate-900">{label}:</span> {value}
        </p>
    );
}

function Field({ label, error, children, className = '' }) {
    return (
        <label className={`block text-sm font-medium text-slate-700 ${className}`}>
            {label}
            {children}
            {error && <p className="mt-1 text-sm text-rose-600">{error}</p>}
        </label>
    );
}

function formatGender(gender) {
    if (gender === 'male') {
        return 'Laki-laki';
    }

    if (gender === 'female') {
        return 'Perempuan';
    }

    return '-';
}
