<?php

/*
|--------------------------------------------------------------------------
| Ingesta de plataforma (Equipo 7) — autenticación por firma, no por Sanctum
|--------------------------------------------------------------------------
| Cada equipo que manda eventos/auditoría a /api/v1/plataforma/* tiene un
| secreto propio (mínimo 20 caracteres, nunca el mismo entre dominios).
| AuthenticatePlatformSource valida la firma HMAC-SHA256 contra el secreto
| de su dominio y pone request->attributes['platform_source'] = dominio;
| el controlador usa ESE valor como source_domain, nunca el que venga en
| el cuerpo de la petición.
|
| Mismo esquema ya usado y probado por Equipo 6 (comunidad:publicar-plataforma)
| en el repo grupal — se porta aquí para que nuestro receptor haga lo mismo.
*/

return [
    'ingest' => [
        'enabled' => env('PLATFORM_INGEST_ENABLED', true),

        'secrets' => [
            'community'   => env('PLATFORM_COMMUNITY_SECRET', 'secreto_comunidad_eq6_2026_seguro'),
            'inventory'   => env('PLATFORM_INVENTORY_SECRET', 'secreto_equipo4_eq3_2026'),
            'services'    => env('PLATFORM_SERVICES_SECRET', 'secreto_servicios_eq5_2026_seguro'),
            'wallet'      => env('PLATFORM_WALLET_SECRET', 'secreto_wallet_eq2_2026_seguro'),
            'identity'    => env('PLATFORM_IDENTITY_SECRET', 'secreto_equipo1_eq7_2026'),
            'marketplace' => env('PLATFORM_MARKETPLACE_SECRET', 'secreto_marketplace_eq3_2026_seguro'),
        ],
    ],
];
