<?php

namespace Ycs77\LaravelRestoreSessionId;

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;

class UserSource
{
    /**
     * The session store instance.
     */
    protected Session $session;

    /**
     * The key for user source into session.
     */
    protected string $sessionKey = 'user_source_for_restore_session_id';

    /**
     * Create a new middleware.
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Preserve the user information into session.
     */
    public function preserve(Request $request): void
    {
        $this->session->put($this->sessionKey, [
            'ip' => $request->getClientIp(),
            'user_agent' => md5($request->server('HTTP_USER_AGENT')),
            'expired_at' => (string) now()->addMinutes(60),
        ]);
    }

    /**
     * Validate the user information from preserved user information in session.
     */
    public function validate(Request $request): bool
    {
        $userSource = $this->session->get($this->sessionKey);

        return $userSource
            && is_array($userSource)
            && isset($userSource['ip'])
            && isset($userSource['user_agent'])
            && isset($userSource['expired_at'])
            && $userSource['ip'] === $request->getClientIp()
            && $userSource['user_agent'] === md5($request->server('HTTP_USER_AGENT'))
            && now()->lt($userSource['expired_at']);
    }
}
