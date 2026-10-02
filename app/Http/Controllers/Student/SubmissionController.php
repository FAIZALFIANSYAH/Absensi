<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionController extends Controller
{
    public function download(Submission $submission): StreamedResponse
    {
        $submission->loadMissing('assignment');
        $this->authorize('view', $submission);

        return Storage::disk('local')->download($submission->file_path, $submission->file_name);
    }
}
