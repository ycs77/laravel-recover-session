# Laravel Recover Session

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![GitHub Tests Action Status][ico-github-action]][link-github-action]
[![Style CI Build Status][ico-style-ci]][link-style-ci]
[![Total Downloads][ico-downloads]][link-downloads]

Recover Laravel session when sending a form post request back from a third-party API like NewebPay.

Currently, Laravel's default Cookie SameSite value is set to `Lax`. This setting prevents cookies from being sent when using form post requests to transmit data to websites on other domains. Consequently, after completing a payment and being redirected back to the original website, users may appear to be automatically logged out due to the inability to retrieve the original login cookie. This package addresses and resolves this issue.

## Installation

Via Composer:

```bash
composer require ycs77/laravel-recover-session
```

Publish config:

```bash
php artisan vendor:publish --tag=recover-session-config
```

## Usage

Now you need to call `RecoverSession::preserve()` to save the current session ID into the cache and include the key in your callback URL. This allows the current session to be resumed after the API returns with the key:

```php
use Ycs77\LaravelRecoverSession\Facades\RecoverSession;

public function pay(Request $request)
{
    $key = RecoverSession::preserve($request);

    ThirdPartyApi::callbackUrl('/pay/callback?sid='.$key);

    // send post form request to the third-party API...
}
```

This package will automatically retrieve the encrypted session ID from the callback URL and restore the original session state upon returning to the site.

> Reference details for the SameSite: https://developers.google.com/search/blog/2020/01/get-ready-for-new-samesitenone-secure

## Manually Register Middleware

If you are not using the global recover session, you can set the config `recover-session.global` to `false`, and adjust the order of the middleware so that `RecoverSession` is placed below `StartSession`. by default, Laravel's `Kernel` does not include the `$middlewarePriority` property, so you need to add it manually.

If you are using Laravel 9 or 10, you should add the `$middlewarePriority` property in your application's `app/Http/Kernel.php` file:

```php
class Kernel extends HttpKernel
{
    /**
     * The priority-sorted list of middleware.
     *
     * Forces non-global middleware to always be in the given order.
     *
     * @var string[]
     */
    protected $middlewarePriority = [
        \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Ycs77\LaravelRecoverSession\Middleware\RecoverSession::class, // need to place `RecoverSession` below `StartSession`
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class,
        \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
        \Illuminate\Contracts\Session\Middleware\AuthenticatesSessions::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}
```

If you are using Laravel 11+, you can add the `RecoverSession` middleware to the `$middlewarePriority` property in the `app/Http/Kernel.php` file:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->priority([
        \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Ycs77\LaravelRecoverSession\Middleware\RecoverSession::class, // need to place `RecoverSession` below `StartSession`
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class,
        \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ]);
})
```

If you are using Laravel 11.31+, it provides a concise method to append middleware to the priority list:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->appendToPriorityList(
        \Ycs77\LaravelRecoverSession\Middleware\RecoverSession::class,
        \Illuminate\Routing\Middleware\ValidateSignature::class
    );
})
```

Final, you can add the `RecoverSession` middleware to the callback route for the API:

```php
use Ycs77\LaravelRecoverSession\Middleware\RecoverSession;

Route::post('/pay/callback', [PaymentController::class, 'callback'])
    ->middleware(RecoverSession::class);
```

## Sponsor

If you think this package has helped you, please consider [Becoming a sponsor](https://www.patreon.com/ycs77) to support my work~ and your avatar will be visible on my major projects.

<p align="center">
  <a href="https://www.patreon.com/ycs77">
    <img src="https://cdn.jsdelivr.net/gh/ycs77/static/sponsors.svg"/>
  </a>
</p>

<a href="https://www.patreon.com/ycs77">
  <img src="https://c5.patreon.com/external/logo/become_a_patron_button.png" alt="Become a Patron" />
</a>

## Credits

* [SameSite Cookie 之踩坑過程](https://kira5033.github.io/2020/09/samesite-cookie-%E4%B9%8B%E8%B8%A9%E5%9D%91%E9%81%8E%E7%A8%8B/)
* [imi/laravel-transsid](https://github.com/iMi-digital/laravel-transsid)

## License

[MIT LICENSE](LICENSE)

[ico-version]: https://img.shields.io/packagist/v/ycs77/laravel-recover-session?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen?style=flat-square
[ico-github-action]: https://img.shields.io/github/actions/workflow/status/ycs77/laravel-recover-session/tests.yml?branch=main&label=tests&style=flat-square
[ico-style-ci]: https://github.styleci.io/repos/651973134/shield?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/ycs77/laravel-recover-session?style=flat-square

[link-packagist]: https://packagist.org/packages/ycs77/laravel-recover-session
[link-github-action]: https://github.com/ycs77/laravel-recover-session/actions/workflows/tests.yml?query=branch%3Amain
[link-style-ci]: https://github.styleci.io/repos/651973134
[link-downloads]: https://packagist.org/packages/ycs77/laravel-recover-session
