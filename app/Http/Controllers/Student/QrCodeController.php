<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\QRCode\StudentQrIdentityService;
use Inertia\Inertia;
use Inertia\Response;

class QrCodeController extends Controller
{
    public function __construct(
        private readonly StudentQrIdentityService $studentQrIdentityService,
    ) {
    }

    public function index(): Response
    {
        /** @var \App\Models\User $user */
        $user = request()->user()->load('studentProfile');
        $profile = $this->studentQrIdentityService->ensureForUser($user);

        return Inertia::render('Student/QRCode/Index', [
            'profile' => $profile ? [
                'nis' => $profile->nis,
                'qr_token' => $profile->qr_token,
                'qr_payload' => $this->studentQrIdentityService->payload($profile),
                'qr_generated_at' => $profile->qr_generated_at?->toIso8601String(),
            ] : null,
        ]);
    }
}
