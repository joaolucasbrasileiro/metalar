<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Metalar API',
    description: 'Documentacao tecnica da API Metalar para autenticacao, catalogo, lojas, estoque, precos e promocoes.'
)]
#[OA\Server(
    url: '/',
    description: 'Servidor atual'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: 'Informe o token JWT no formato: Bearer {token}',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
#[OA\Tag(
    name: 'Auth',
    description: 'Autenticacao e gerenciamento da sessao JWT.'
)]
#[OA\Tag(
    name: 'Users',
    description: 'Perfil, administracao de usuarios e papeis.'
)]
#[OA\Tag(
    name: 'Public Catalog',
    description: 'Consulta publica de lojas, marcas, produtos, categorias, subcategorias e SKUs.'
)]
#[OA\Tag(
    name: 'Admin Catalog',
    description: 'Operacoes administrativas de catalogo.'
)]
#[OA\Tag(
    name: 'Staff Commerce',
    description: 'Operacoes por loja: estoque, precos, promocoes e movimentacoes.'
)]
class OpenApiSpec {}
