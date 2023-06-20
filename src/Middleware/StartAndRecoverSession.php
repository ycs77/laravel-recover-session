<?php

namespace Ycs77\LaravelRecoverSession\Middleware;

use Closure;
use Illuminate\Config\Repository as Config;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Session\SessionManager;
use Symfony\Component\HttpFoundation\Response;
use Ycs77\LaravelRecoverSession\RecoverSession as SessionRecoverer;

class StartAndRecoverSession extends StartSession
{
    /**
     * The config repository.
     */
    protected Config $config;

    /**
     * The session recoverer.
     */
    protected SessionRecoverer $sessionRecoverer;

    /**
     * Create a new session middleware.
     */
    public function __construct(SessionManager $manager,
                                Config $config,
                                SessionRecoverer $sessionRecoverer,
                                callable $cacheFactoryResolver = null)
    {
        parent::__construct($manager, $cacheFactoryResolver);

        $this->config = $config;
        $this->sessionRecoverer = $sessionRecoverer;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next): Response
    {
        if (! $this->sessionConfigured()) {
            return $next($request);
        }

        return parent::handle($request, function ($request) use ($next) {
            if ($sessionIdKey = $this->getSessionIdKey($request)) {
                $this->sessionRecoverer->recover($request, $sessionIdKey);
            }

            return $next($request);
        });
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
