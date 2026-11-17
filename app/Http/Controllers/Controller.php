<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Ambil nilai query string sebagai string; abaikan nilai non-skalar (mis. array).
     */
    protected function queryString(Request $request, string $key): ?string
    {
        $value = $request->input($key);

        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
