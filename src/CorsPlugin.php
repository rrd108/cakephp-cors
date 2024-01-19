<?php
declare(strict_types=1);

namespace Rrd108\Cors;

use Cake\Core\BasePlugin;
use Cake\Http\MiddlewareQueue;
use Rrd108\Cors\Routing\Middleware\CorsMiddleware;

class CorsPlugin extends BasePlugin
{
    /**
     * @inheritDoc
     */
    public function middleware(MiddlewareQueue $middleware): MiddlewareQueue
    {
        // Add middleware here.
        return parent::middleware($middleware)
            ->insertBefore(
                'Cake\Routing\Middleware\RoutingMiddleware',
                new CorsMiddleware($this)
            );
    }
}
