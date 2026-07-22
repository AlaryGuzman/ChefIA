<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Comentario;
use App\Models\Compra;
use App\Models\Favorito;
use App\Models\Receta;
use App\Models\Resena;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@chefia.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('123456'),
                'role' => 'admin',
                'suspended_until' => null,
                'suspended_indefinitely' => false,
                'suspension_reason' => null,
            ]
        );

        $usuarios = collect([
            ['name' => 'maria', 'email' => 'maria@chefia.com'],
            ['name' => 'david', 'email' => 'david@chefia.com'],
            ['name' => 'alex', 'email' => 'alex@chefia.com'],
            ['name' => 'denis', 'email' => 'denis@chefia.com'],
            ['name' => 'alary', 'email' => 'alary@chefia.com'],
        ])->mapWithKeys(function (array $datos) {
            $usuario = User::updateOrCreate(
                ['email' => $datos['email']],
                [
                    'name' => $datos['name'],
                    'password' => Hash::make('123456'),
                    'role' => 'usuario',
                    'suspended_until' => null,
                    'suspended_indefinitely' => false,
                    'suspension_reason' => null,
                ]
            );

            return [$datos['name'] => $usuario];
        });

        $categorias = collect([
            ['nombre' => 'Desayunos', 'descripcion' => 'Ideas faciles para iniciar el dia.'],
            ['nombre' => 'Comidas', 'descripcion' => 'Platos fuertes y caseros.'],
            ['nombre' => 'Postres', 'descripcion' => 'Recetas dulces para compartir.'],
            ['nombre' => 'Saludable', 'descripcion' => 'Opciones frescas y balanceadas.'],
            ['nombre' => 'Rapidas', 'descripcion' => 'Recetas practicas para cocinar rapido.'],
            ['nombre' => 'Premium', 'descripcion' => 'Recetas de pago con acceso por pedido.'],
        ])->mapWithKeys(function (array $categoria) {
            $modelo = Categoria::updateOrCreate(
                ['nombre' => $categoria['nombre']],
                ['descripcion' => $categoria['descripcion']]
            );

            return [$categoria['nombre'] => $modelo];
        });

        $imagen = '/img/fondo-login.webp';

        $recetas = collect([
            [
                'usuario_id' => $usuarios['maria']->id,
                'categoria_id' => $categorias['Desayunos']->id,
                'titulo' => 'Chilaquiles verdes con pollo',
                'descripcion' => 'Totopos crujientes banados en salsa verde, crema, queso y pollo deshebrado.',
                'ingredientes' => "Totopos\nSalsa verde\nPollo deshebrado\nCrema\nQueso fresco\nCebolla\nCilantro",
                'pasos' => "Calienta la salsa verde.\nAgrega los totopos por un minuto.\nSirve con pollo, crema, queso, cebolla y cilantro.",
                'tiempo_preparacion' => 25,
                'imagen' => $imagen,
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $usuarios['david']->id,
                'categoria_id' => $categorias['Comidas']->id,
                'titulo' => 'Tacos de carne asada',
                'descripcion' => 'Tacos clasicos con carne marinada, cebolla, cilantro y salsa casera.',
                'ingredientes' => "Carne de res\nTortillas de maiz\nCebolla\nCilantro\nLimones\nSalsa roja",
                'pasos' => "Marina la carne con sal, ajo y limon.\nAsa la carne hasta dorar.\nPica y sirve en tortillas calientes con salsa.",
                'tiempo_preparacion' => 35,
                'imagen' => $imagen,
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $usuarios['alex']->id,
                'categoria_id' => $categorias['Rapidas']->id,
                'titulo' => 'Pasta cremosa con champinones',
                'descripcion' => 'Una pasta rapida, cremosa y perfecta para una comida de entre semana.',
                'ingredientes' => "Pasta\nChampinones\nCrema\nAjo\nQueso parmesano\nPerejil",
                'pasos' => "Cuece la pasta.\nSaltea ajo y champinones.\nAgrega crema y queso.\nMezcla con la pasta y termina con perejil.",
                'tiempo_preparacion' => 20,
                'imagen' => $imagen,
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $usuarios['denis']->id,
                'categoria_id' => $categorias['Saludable']->id,
                'titulo' => 'Bowl de quinoa y verduras',
                'descripcion' => 'Bowl fresco con quinoa, garbanzos, aguacate y verduras rostizadas.',
                'ingredientes' => "Quinoa\nGarbanzos\nAguacate\nTomate cherry\nEspinaca\nLimon\nAceite de oliva",
                'pasos' => "Cuece la quinoa.\nRostiza garbanzos y verduras.\nSirve con aguacate y aderezo de limon.",
                'tiempo_preparacion' => 30,
                'imagen' => $imagen,
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $usuarios['alary']->id,
                'categoria_id' => $categorias['Premium']->id,
                'titulo' => 'Ramen casero especial',
                'descripcion' => 'Caldo profundo con fideos, huevo marinado y toppings estilo restaurante.',
                'ingredientes' => "Fideos ramen\nCaldo de pollo\nSalsa de soya\nHuevo\nCebollin\nChampinones\nPanceta",
                'pasos' => "Prepara un caldo concentrado.\nCuece fideos y huevo.\nDora la panceta.\nSirve con toppings y cebollin fresco.",
                'tiempo_preparacion' => 55,
                'imagen' => $imagen,
                'es_premium' => true,
                'precio' => 79.00,
            ],
            [
                'usuario_id' => $usuarios['maria']->id,
                'categoria_id' => $categorias['Premium']->id,
                'titulo' => 'Asado de boda estilo ChefIA',
                'descripcion' => 'Receta tradicional con salsa intensa de chiles y carne suave.',
                'ingredientes' => "Carne de cerdo\nChile ancho\nChile guajillo\nAjo\nEspecias\nChocolate\nCaldo",
                'pasos' => "Cuece la carne hasta suavizar.\nLicua chiles hidratados con especias.\nFrie la salsa y cocina la carne dentro hasta espesar.",
                'tiempo_preparacion' => 90,
                'imagen' => $imagen,
                'es_premium' => true,
                'precio' => 99.00,
            ],
            [
                'usuario_id' => $usuarios['david']->id,
                'categoria_id' => $categorias['Premium']->id,
                'titulo' => 'Mole negro oaxaqueno',
                'descripcion' => 'Salsa compleja con chiles, especias, chocolate y tecnica tradicional.',
                'ingredientes' => "Chile chilhuacle\nChile pasilla\nAjonjoli\nChocolate\nPlatano macho\nTortilla\nPollo",
                'pasos' => "Tuesta chiles y especias con cuidado.\nMuele todo con caldo.\nFrie la pasta lentamente.\nCocina con pollo hasta lograr una salsa brillante.",
                'tiempo_preparacion' => 160,
                'imagen' => $imagen,
                'es_premium' => true,
                'precio' => 149.00,
            ],
            [
                'usuario_id' => $usuarios['alex']->id,
                'categoria_id' => $categorias['Premium']->id,
                'titulo' => 'Cheesecake de frutos rojos',
                'descripcion' => 'Cheesecake cremoso con costra de galleta y salsa brillante de frutos rojos.',
                'ingredientes' => "Queso crema\nGalletas\nMantequilla\nAzucar\nHuevos\nFrutos rojos\nVainilla",
                'pasos' => "Prepara la base de galleta.\nBate queso, azucar y huevos.\nHornea a baja temperatura.\nCubre con salsa de frutos rojos.",
                'tiempo_preparacion' => 75,
                'imagen' => $imagen,
                'es_premium' => true,
                'precio' => 89.00,
            ],
        ])->mapWithKeys(function (array $receta) {
            $modelo = Receta::updateOrCreate(['titulo' => $receta['titulo']], $receta);

            return [$receta['titulo'] => $modelo];
        });

        foreach ([
            ['usuario_id' => $usuarios['david']->id, 'receta_id' => $recetas['Chilaquiles verdes con pollo']->id],
            ['usuario_id' => $usuarios['maria']->id, 'receta_id' => $recetas['Tacos de carne asada']->id],
            ['usuario_id' => $usuarios['denis']->id, 'receta_id' => $recetas['Ramen casero especial']->id],
            ['usuario_id' => $usuarios['alary']->id, 'receta_id' => $recetas['Bowl de quinoa y verduras']->id],
        ] as $favorito) {
            Favorito::updateOrCreate($favorito, $favorito);
        }

        foreach ([
            ['usuario_id' => $usuarios['david']->id, 'receta_id' => $recetas['Chilaquiles verdes con pollo']->id, 'contenido' => 'La salsa verde quedo buenisima.'],
            ['usuario_id' => $usuarios['maria']->id, 'receta_id' => $recetas['Tacos de carne asada']->id, 'contenido' => 'Tip: calienta bien la tortilla antes de servir.'],
            ['usuario_id' => $usuarios['alary']->id, 'receta_id' => $recetas['Pasta cremosa con champinones']->id, 'contenido' => 'Rapida y perfecta para cenar.'],
        ] as $comentario) {
            Comentario::updateOrCreate($comentario, $comentario);
        }

        $comprasEntregadas = collect([
            [
                'usuario' => 'denis',
                'receta' => 'Ramen casero especial',
                'precio' => 79.00,
                'ultimos4' => '4242',
                'referencia' => 'CHF-SEED-0001',
                'calificacion' => 5,
                'comentario' => 'Excelente receta. El caldo queda profundo, los pasos son claros y el resultado se siente de restaurante.',
            ],
            [
                'usuario' => 'maria',
                'receta' => 'Cheesecake de frutos rojos',
                'precio' => 89.00,
                'ultimos4' => '5128',
                'referencia' => 'CHF-SEED-0004',
                'calificacion' => 4,
                'comentario' => 'Muy buen postre, cremoso y bien explicado. Le bajaria un poco el dulce a la salsa, pero queda delicioso.',
            ],
            [
                'usuario' => 'alex',
                'receta' => 'Mole negro oaxaqueno',
                'precio' => 149.00,
                'ultimos4' => '7741',
                'referencia' => 'CHF-SEED-0005',
                'calificacion' => 2,
                'comentario' => 'La receta tiene buen sabor, pero senti que el proceso es largo y faltan detalles para no quemar los chiles.',
            ],
            [
                'usuario' => 'david',
                'receta' => 'Asado de boda estilo ChefIA',
                'precio' => 99.00,
                'ultimos4' => '9033',
                'referencia' => 'CHF-SEED-0006',
                'calificacion' => 1,
                'comentario' => 'No me funciono bien. La salsa quedo muy intensa y necesitaria instrucciones mas precisas para equilibrarla.',
            ],
        ])->mapWithKeys(function (array $datos) use ($usuarios, $recetas) {
            $compra = Compra::create([
                'usuario_id' => $usuarios[$datos['usuario']]->id,
                'receta_id' => $recetas[$datos['receta']]->id,
                'precio_pagado' => $datos['precio'],
                'metodo_pago' => 'Tarjeta simulada',
                'tarjeta_ultimos4' => $datos['ultimos4'],
                'referencia_pago' => $datos['referencia'],
                'estado' => 'entregado',
                'pagado_at' => now()->subDays(3),
                'enviado_at' => now()->subDays(2),
                'entregado_at' => now()->subDay(),
            ]);

            Resena::updateOrCreate(
                [
                    'usuario_id' => $usuarios[$datos['usuario']]->id,
                    'receta_id' => $recetas[$datos['receta']]->id,
                ],
                [
                    'compra_id' => $compra->id,
                    'calificacion' => $datos['calificacion'],
                    'comentario' => $datos['comentario'],
                ]
            );

            return [$datos['referencia'] => $compra];
        });

        Compra::create([
            'usuario_id' => $usuarios['alary']->id,
            'receta_id' => $recetas['Asado de boda estilo ChefIA']->id,
            'precio_pagado' => 99.00,
            'metodo_pago' => 'Efectivo OXXO',
            'referencia_pago' => 'CHF-SEED-0002',
            'referencia_efectivo' => 'OXXO-SEED-0002',
            'estado' => 'pendiente_pago',
        ]);

        Compra::create([
            'usuario_id' => $usuarios['maria']->id,
            'receta_id' => $recetas['Mole negro oaxaqueno']->id,
            'precio_pagado' => 149.00,
            'metodo_pago' => 'Tarjeta simulada',
            'tarjeta_ultimos4' => '1881',
            'referencia_pago' => 'CHF-SEED-0003',
            'estado' => 'pagado',
            'pagado_at' => now()->subHours(4),
        ]);
        
        $admin->notificaciones()->create([
            'tipo' => 'sistema',
            'titulo' => 'Datos de prueba cargados',
            'mensaje' => 'El recetario quedo listo con 5 usuarios, 8 recetas y pedidos en diferentes estados.',
            'data' => ['url' => '/dashboard'],
        ]);
    }
}
