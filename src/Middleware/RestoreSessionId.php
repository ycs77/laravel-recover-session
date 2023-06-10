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
    public function handle(Request $request, Closure $next, string $key = 'sid'): Response
    {
        if ($sessionId = $this->decryptSessionId($request, $key)) {
            $this->restoreSessionId($this->session, $sessionId);
        }

        return $next($request);
    }

    /**
     * Decrypt the session id from callback url query.
     */
    protected function decryptSessionId(Request $request, string $key): string
    {
        try {
            return $this->encrypter->decrypt(Base64Url::decode($request->query($key)), false);
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
     * Restore the session id for current request.
     */
    protected function restoreSessionId(Session $session, string $sessionId): void
    {
        $session->invalidate();

        $session->setId($sessionId);

        $session->start();
    }
}
