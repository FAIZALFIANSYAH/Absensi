<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterStudent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StudentRegisterRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredStudentController extends Controller
{
    public function __construct(
        private readonly RegisterStudent $registerStudent,
    ) {
    }

    public function create(): Response
    {
        return Inertia::render('Auth/RegisterStudent');
    }

    public function store(StudentRegisterRequest $request): RedirectResponse
    {
        $user = $this->registerStudent->handle([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'nis' => $request->string('nis')->toString(),
            'nik' => $request->string('nik')->toString(),
            'phone' => $request->string('phone')->toString() ?: null,
            'gender' => $request->string('gender')->toString() ?: null,
            'birth_date' => $request->input('birth_date') ?: null,
            'address' => $request->string('address')->toString() ?: null,
            'password' => $request->string('password')->toString(),
        ]);

        event(new Registered($user));

        return redirect()
            ->route('login')
            ->with('status', 'Registrasi siswa berhasil. Akun Anda sedang menunggu persetujuan admin.');
    }
}
