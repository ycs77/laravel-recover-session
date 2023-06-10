<?php

namespace Ycs77\LaravelRestoreSessionId\Middleware;

use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Ycs77\LaravelRestoreSessionId\Support\Base64Url;

class RestoreSessionId
{
    /**
     * The session store instance.
     */
    protected Session $session;

    /**
     * The encrypter instance.
     */
    protected Encrypter $encrypter;

    /**
     * The session ID key for get from request.
     */
    protected string $sessionIdKey = 'sid';

    /**
     * Create a new middleware.
     */
    public function __construct(Session $session, Encrypter $encrypter)
    {
        $this->session = $session;
        $this->encrypter = $encrypter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $encryptedSessionId = $this->getSessionIdFromRequest($request);

        if ($encryptedSessionId &&
            $sessionId = $this->decryptSessionId($encryptedSessionId)
        ) {
            $this->restoreSessionId($this->session, $sessionId);
        }

        return $next($request);
    }

    /**
     * Get session ID from request.
     */
    protected function getSessionIdFromRequest(Request $request): string|null
    {
        return $request->query($this->sessionIdKey);
    }

    /**
     * Decrypt the session ID from callback url query.
     */
    protected function decryptSessionId(string $sessionId): string
    {
        try {
            return $this->encrypter->decrypt(Base64Url::decode($sessionId), false);
        } catch (DecryptException $e) {
            $this->undecrypted($e);
        }
    }

    /**
     * Handle on undecrypted.
     */
    protected function undecrypted(DecryptException $e): void
    {
        //
    }

    /**
     * Restore the session ID for current request.
     */
    protected function restoreSessionId(Session $session, string $sessionId): void
    {
        $session->invalidate();

        $session->setId($sessionId);

        $session->start();
    }
}
