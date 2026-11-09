<?php

declare(strict_types=1);

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
<<<<<<< HEAD
    title: 'TechLium API',
    description: "API REST du backend TechLium - marketplace de produits technologiques et backoffice de gestion.
=======
    title: 'TechniLium API',
    description: "API REST du backend TechniLium - marketplace de produits technologiques et backoffice de gestion.
>>>>>>> 2a32efc33a7ad925aaa66b3778733f66317cb71f

Authentification : jeton Bearer (Sanctum). Obtenez un jeton via les routes Auth, puis cliquez sur « Authorize ». Paiement à la livraison ; montants en F CFA.",
)]
#[OA\Server(url: '/api/v1', description: 'Serveur courant')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum personal access token',
)]
#[OA\Tag(name: 'Auth', description: 'Comptes, authentification (cahier des charges §5.1, §10.1 - module Identity)')]
#[OA\Tag(name: 'Catalog', description: 'Catégories, produits, offres (cahier des charges §5.2, §5.3, §6.1 - module Catalog)')]
#[OA\Tag(name: 'Orders', description: 'Panier et commandes, paiement à la livraison (cahier des charges §5.4, §5.5 - module Orders)')]
final class OpenApi {}
