<?php

namespace Ycs77\LaravelRestoreSessionId;

use Illuminate\Http\Request;

class RestoreSessionId
{
    /**
     * Preserve the user information into session.
     */
    public static function preserveUserSource(Request $request)
    {
        return app(UserSource::class)->preserve($request);
    }
}
