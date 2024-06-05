<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

use App\Contracts\PaymentGatewayInterface;
use App\Services\BillGatewayService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (app()->environment('local')) {
            Model::unguard();
        }

        $this->app->bind(PaymentGatewayInterface::class, function ($app, $parameters) {
            $type = $parameters['billingType'] ?? null;
        
            if (!$type) {
                throw new \InvalidArgumentException("Tipo não fornecido");
            }
        
            $allowedTypes = [
                'BOLETO' => 'App\\Services\\BillGatewayService',
                'PIX' => 'App\\Services\\PixGatewayService',
                'CREDIT_CARD' => 'App\\Services\\CardGatewayService',
            ];
        
            if (!array_key_exists($type, $allowedTypes)) {
                throw new \InvalidArgumentException("Tipo não suportado");
            }
        
            $className = $allowedTypes[$type];
        
            return new $className();
        });
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //

        $this->bootRoute();
    }

    public function bootRoute(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

    }
}
