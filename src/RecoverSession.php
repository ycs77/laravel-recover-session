<?php

namespace Ycs77\LaravelRecoverSession;

use Illuminate\Http\Request;

class RecoverSession
{
    /**
     * Preserve the user information into session.
     */
    public static function preserveUserSource(Request $request, int $minutes = 60)
    {
        return app(UserSource::class)->preserve($request, $minutes);
    }
}
