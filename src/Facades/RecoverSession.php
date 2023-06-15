<?php

namespace Ycs77\LaravelRecoverSession\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string preserve(\Illuminate\Http\Request $request)
 * @method static void preserveUserSource(\Illuminate\Http\Request $request, int $minutes)
 * @method static bool recover(\Illuminate\Http\Request $request, string $key)
 * @method static null|string retrieve(string $key)
 *
 * @see \Ycs77\LaravelRecoverSession\RecoverSession
 */
class RecoverSession extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Ycs77\LaravelRecoverSession\RecoverSession::class;
    }
}
