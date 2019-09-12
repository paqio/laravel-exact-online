<?php

namespace PendoNL\LaravelExactOnline\Providers;

use Illuminate\Support\ServiceProvider;
use PendoNL\LaravelExactOnline\LaravelExactOnline;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;

class LaravelExactOnlineServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../Http/routes.php');

        $this->loadViewsFrom(__DIR__.'/../views', 'laravelexactonline');

        $this->publishes([
            __DIR__.'/../views' => base_path('resources/views/vendor/laravelexactonline'),
            __DIR__.'/../exact.api.json' => storage_path('exact.api.json'),
            __DIR__.'/../config/laravel-exact-online.php' => config_path('laravel-exact-online.php')
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->alias(LaravelExactOnline::class, 'laravel-exact-online');

        $this->app->singleton('Exact\Connection', function () {

            $config = LaravelExactOnline::loadConfig() == null ? new \App\Exact() : LaravelExactOnline::loadConfig();

            $connection = new \Picqer\Financials\Exact\Connection();
//            $rand = Crypt::encryptString(Auth::user()->id);
            $connection->setRedirectUrl(route('exact.callback',['user' => Auth::user()->id]));
            $connection->setExactClientId(config('laravel-exact-online.exact_client_id'));
            $connection->setExactClientSecret(config('laravel-exact-online.exact_client_secret'));
            $connection->setBaseUrl('https://start.exactonline.' . config('laravel-exact-online.exact_country_code'));

            if (config('laravel-exact-online.exact_division') !== '') {
                $connection->setDivision(config('laravel-exact-online.exact_division'));
            }

            if (isset($config->authorisationCode)) {
                $connection->setAuthorizationCode($config->authorisationCode);
            }

            if (isset($config->accessToken)) {
                $connection->setAccessToken(unserialize($config->accessToken));
            }

            if (isset($config->refreshToken)) {
                $connection->setRefreshToken($config->refreshToken);
            }

            if (isset($config->tokenExpires)) {
                $connection->setTokenExpires($config->tokenExpires);
            }

            $connection->setTokenUpdateCallback('tokenUpdateCallback');
            
            try {
                if (isset($config->authorisationCode)) {
                    $connection->connect();
                }
            } catch (\Exception $e) {
                throw new \Exception('Could not connect to Exact: ' . $e->getMessage());
            }



            return $connection;
        });
    }

    function tokenUpdateCallback(\Picqer\Financials\Exact\Connection $connection)
    {
        
        $config = LaravelExactOnline::loadConfig() == null ? new \App\Exact() : LaravelExactOnline::loadConfig();

        $config->accessToken = serialize($connection->getAccessToken());
            $config->refreshToken = $connection->getRefreshToken();
            $config->tokenExpires = $connection->getTokenExpires();
        // Save the new tokens for next connections
            LaravelExactOnline::storeConfig($config);

    }
}
