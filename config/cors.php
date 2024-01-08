<?php
return [
    'Cors' => [
        'AllowOrigin' => true,
        'AllowCredentials' => true,
        'AllowMethods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'AllowHeaders' => true,
        'ExposeHeaders' => false,
        'MaxAge' => 86400, // 1 day
    ]
];
