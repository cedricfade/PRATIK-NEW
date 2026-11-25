<?php

use App\Support\Media;

if (! function_exists('media')) {
    function media(string $folder, ?string $file): ?string
    {
        return Media::url($folder, $file);
    }
}

if (! function_exists('fcfa')) {
    /** Formate un prix stocké en texte ("140000000", "725 000"…) en "140 000 000 FCFA". */
    function fcfa(mixed $value, bool $suffix = true): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if ($digits === '' || (int) $digits === 0) {
            return 'Prix sur demande';
        }

        return number_format((int) $digits, 0, ',', "\u{202F}").($suffix ? ' FCFA' : '');
    }
}

if (! function_exists('tel_link')) {
    function tel_link(?string $number): string
    {
        $clean = preg_replace('/[^\d+]/', '', (string) $number);

        if ($clean !== '' && ! str_starts_with($clean, '+') && strlen($clean) === 10) {
            $clean = '+225'.$clean;
        }

        return 'tel:'.$clean;
    }
}
