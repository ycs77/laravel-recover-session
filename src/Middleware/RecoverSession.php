<?php

namespace Ycs77\LaravelRecoverSession\Middleware;

use Closure;
use Illuminate\Config\Repository as Config;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Ycs77\LaravelRecoverSession\RecoverSession as SessionRecoverer;

class RecoverSession
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(
        protected Config $config,
        protected SessionRecoverer $sessionRecoverer
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionIdKey = $this->getSessionIdKey($request);

        $this->sessionRecoverer->recover($request, $sessionIdKey);

        return $next($request);
    }

    /**
     * Get session ID from request.
     */
    protected function getSessionIdKey(Request $request): string|null
    {
        return $request->query(
            $this->config->get('recover-session.session_id_key')
        );
    }
}
