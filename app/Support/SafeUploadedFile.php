<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class SafeUploadedFile
{
    public static function originalName(UploadedFile $file): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_BASENAME);
        $extension = strtolower($file->getClientOriginalExtension());
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);

        $sanitizedBaseName = Str::of($baseName)
            ->ascii()
            ->replaceMatches('/[^A-Za-z0-9._-]+/', '-')
            ->trim('-_.')
            ->limit(80, '')
            ->value();

        if ($sanitizedBaseName === '') {
            $sanitizedBaseName = 'file';
        }

        return $extension !== ''
            ? sprintf('%s.%s', $sanitizedBaseName, $extension)
            : $sanitizedBaseName;
    }
}
