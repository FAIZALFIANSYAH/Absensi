<?php

namespace App\Http\Middleware;

use App\Enums\ApprovalStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! $user->isAdmin() && ! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'Akun Anda sedang dinonaktifkan oleh admin.');
        }

        if ($user->approval_status === ApprovalStatus::Approved || $user->isAdmin()) {
            return $next($request);
        }

        return redirect()->route('approval.pending');
    }
}
