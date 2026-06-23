<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     * ★ /webhook must be excluded — Meta POSTs to it without a CSRF token
     *   and will get 403 otherwise, breaking ALL incoming messages.
     */
    protected $except = [
        '/webhook',
        'webhook',
    ];
}

