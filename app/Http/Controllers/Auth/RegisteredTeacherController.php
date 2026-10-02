<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterTeacher;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TeacherRegisterRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredTeacherController extends Controller
{
    public function __construct(
        private readonly RegisterTeacher $registerTeacher,
    ) {
    }

    public function create(): Response
    {
        return Inertia::render('Auth/RegisterTeacher');
    }

    public function store(TeacherRegisterRequest $request): RedirectResponse
    {
        $user = $this->registerTeacher->handle([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'nip' => $request->string('nip')->toString(),
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
            ->with('status', 'Registrasi guru berhasil. Akun Anda sedang menunggu persetujuan admin.');
    }
}
