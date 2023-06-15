<?php

namespace Ycs77\LaravelRecoverSession;

use Illuminate\Cache\Repository as Cache;
use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Session\Store as Session;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class RecoverSession
{
    /**
     * Create a new recover session manager instance.
     */
    public function __construct(
        protected Config $config,
        protected Cache $cache,
        protected Session $session,
        protected Encrypter $encrypter,
        protected UserSource $userSource
    ) {
    }

    /**
     * Preserve the current session ID.
     */
    public function preserve(Request $request): string
    {
        $this->preserveUserSource(
            $request, $minutes = $this->config->get('recover-session.ttl')
        );

        $key = Str::random(40);

        $this->cache->add(
            'recover_session_'.$key,
            $this->encrypter->encryptString($this->session->getId()),
            Carbon::now()->addMinutes($minutes)
        );

        return $key;
    }

    /**
     * Preserve the user information into session.
     */
    public function preserveUserSource(Request $request, int $minutes = 60): void
    {
        $this->userSource->preserve($request, $minutes);
    }

    /**
     * Recover the session ID for current request.
     */
    public function recover(Request $request, string $key): bool
    {
        if (! $sessionId = $this->retrieve($key)) {
            return false;
        }

        try {
            $sessionId = $this->encrypter->decryptString($sessionId);
        } catch (DecryptException $e) {
            return false;
        }

        $this->session->setId($sessionId);

        $this->session->start();

        if (! $this->userSource->validate($request)) {
            // If user soruce is invalid, will regenerate a new session id.
            $this->session->setId(null);

            $this->session->start();

            $this->userSource->clear();

            return false;
        }

        $this->userSource->clear();

        return true;
    }

    /**
     * Retrieve the session ID.
     */
    public function retrieve(string $key): ?string
    {
        if (! $this->cache->has('recover_session_'.$key)) {
            return null;
        }

        /** @var string */
        $sessionId = $this->cache->pull('recover_session_'.$key);

        return $sessionId;
    }
}
