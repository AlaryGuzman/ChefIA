<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Comentario;
use App\Models\Compra;
use App\Models\Favorito;
use App\Models\Receta;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@chefia.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('123456'),
                'role' => 'admin',
            ]
        );

        $usuario = User::updateOrCreate(
            ['email' => 'user@chefia.com'],
            [
                'name' => 'usuario',
                'password' => Hash::make('123456'),
                'role' => 'usuario',
            ]
        );

        $categorias = collect([
            [
                'nombre' => 'Desayunos',
                'descripcion' => 'Ideas faciles para iniciar el dia con energia.',
            ],
            [
                'nombre' => 'Comidas',
                'descripcion' => 'Platos fuertes para la hora principal.',
            ],
            [
                'nombre' => 'Postres',
                'descripcion' => 'Recetas dulces para compartir.',
            ],
            [
                'nombre' => 'Saludable',
                'descripcion' => 'Opciones balanceadas con ingredientes frescos.',
            ],
            [
                'nombre' => 'Rapidas',
                'descripcion' => 'Recetas practicas para cocinar sin complicarse.',
            ],
            [
                'nombre' => 'Premium',
                'descripcion' => 'Recetas especiales con contenido desbloqueable.',
            ],
        ])->mapWithKeys(function (array $categoria) {
            $modelo = Categoria::updateOrCreate(
                ['nombre' => $categoria['nombre']],
                ['descripcion' => $categoria['descripcion']]
            );

            return [$categoria['nombre'] => $modelo];
        });

        $recetas = collect([
            [
                'usuario_id' => $admin->id,
                'categoria_id' => $categorias['Desayunos']->id,
                'titulo' => 'Chilaquiles verdes con pollo',
                'descripcion' => 'Totopos crujientes banados en salsa verde, crema, queso y pollo deshebrado.',
                'ingredientes' => "Totopos\nSalsa verde\nPollo deshebrado\nCrema\nQueso fresco\nCebolla\nCilantro",
                'pasos' => "Calienta la salsa verde.\nAgrega los totopos por un minuto.\nSirve con pollo, crema, queso, cebolla y cilantro.",
                'tiempo_preparacion' => 25,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $usuario->id,
                'categoria_id' => $categorias['Comidas']->id,
                'titulo' => 'Tacos de carne asada',
                'descripcion' => 'Tacos clasicos con carne marinada, cebolla, cilantro y salsa casera.',
                'ingredientes' => "Carne de res\nTortillas de maiz\nCebolla\nCilantro\nLimones\nSalsa roja",
                'pasos' => "Marina la carne con sal, ajo y limon.\nAsa la carne hasta dorar.\nPica y sirve en tortillas calientes con salsa.",
                'tiempo_preparacion' => 35,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $admin->id,
                'categoria_id' => $categorias['Rapidas']->id,
                'titulo' => 'Pasta cremosa con champinones',
                'descripcion' => 'Una pasta rapida, cremosa y perfecta para una comida de entre semana.',
                'ingredientes' => "Pasta\nChampinones\nCrema\nAjo\nQueso parmesano\nPerejil",
                'pasos' => "Cuece la pasta.\nSaltea ajo y champinones.\nAgrega crema y queso.\nMezcla con la pasta y termina con perejil.",
                'tiempo_preparacion' => 20,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $usuario->id,
                'categoria_id' => $categorias['Saludable']->id,
                'titulo' => 'Bowl de quinoa y verduras',
                'descripcion' => 'Bowl fresco con quinoa, garbanzos, aguacate y verduras rostizadas.',
                'ingredientes' => "Quinoa\nGarbanzos\nAguacate\nTomate cherry\nEspinaca\nLimon\nAceite de oliva",
                'pasos' => "Cuece la quinoa.\nRostiza los garbanzos y verduras.\nSirve todo en un bowl con aguacate y aderezo de limon.",
                'tiempo_preparacion' => 30,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $admin->id,
                'categoria_id' => $categorias['Postres']->id,
                'titulo' => 'Pay de limon frio',
                'descripcion' => 'Postre cremoso sin horno con galleta, limon y leche condensada.',
                'ingredientes' => "Galletas maria\nMantequilla\nLeche condensada\nLeche evaporada\nJugo de limon",
                'pasos' => "Prepara la base con galleta y mantequilla.\nLicua las leches con limon.\nVierte sobre la base y refrigera.",
                'tiempo_preparacion' => 18,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $admin->id,
                'categoria_id' => $categorias['Premium']->id,
                'titulo' => 'Ramen casero especial',
                'descripcion' => 'Caldo profundo con fideos, huevo marinado y toppings estilo restaurante.',
                'ingredientes' => "Fideos ramen\nCaldo de pollo\nSalsa de soya\nHuevo\nCebollin\nChampinones\nPanceta",
                'pasos' => "Prepara un caldo concentrado.\nCuece fideos y huevo.\nDora la panceta.\nSirve con toppings y cebollin fresco.",
                'tiempo_preparacion' => 55,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => true,
                'precio' => 79.00,
            ],
            [
                'usuario_id' => $admin->id,
                'categoria_id' => $categorias['Premium']->id,
                'titulo' => 'Asado de boda estilo ChefIA',
                'descripcion' => 'Receta tradicional con salsa intensa de chiles y carne suave.',
                'ingredientes' => "Carne de cerdo\nChile ancho\nChile guajillo\nAjo\nEspecias\nChocolate\nCaldo",
                'pasos' => "Cuece la carne hasta suavizar.\nLicua chiles hidratados con especias.\nFrie la salsa y cocina la carne dentro hasta espesar.",
                'tiempo_preparacion' => 90,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => true,
                'precio' => 99.00,
            ],
            [
                'usuario_id' => $usuario->id,
                'categoria_id' => $categorias['Rapidas']->id,
                'titulo' => 'Ensalada de atun con aguacate',
                'descripcion' => 'Comida fresca y rapida con atun, aguacate, vegetales y limon.',
                'ingredientes' => "Atun\nAguacate\nPepino\nJitomate\nCebolla morada\nLimon\nSal",
                'pasos' => "Pica los vegetales.\nMezcla con atun y aguacate.\nSazona con limon y sal.",
                'tiempo_preparacion' => 10,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => false,
                'precio' => null,
            ],
        ])->mapWithKeys(function (array $receta) {
            $modelo = Receta::updateOrCreate(
                ['titulo' => $receta['titulo']],
                $receta
            );

            return [$receta['titulo'] => $modelo];
        });

        $comentarios = [
            [
                'usuario_id' => $usuario->id,
                'receta_id' => $recetas['Chilaquiles verdes con pollo']->id,
                'contenido' => 'Quedaron muy buenos, la salsa verde le da todo el sabor.',
            ],
            [
                'usuario_id' => $admin->id,
                'receta_id' => $recetas['Tacos de carne asada']->id,
                'contenido' => 'Tip: calienta bien la tortilla antes de servir para que no se rompa.',
            ],
            [
                'usuario_id' => $usuario->id,
                'receta_id' => $recetas['Pay de limon frio']->id,
                'contenido' => 'Perfecto para hacerlo rapido y dejarlo listo en el refri.',
            ],
            [
                'usuario_id' => $admin->id,
                'receta_id' => $recetas['Bowl de quinoa y verduras']->id,
                'contenido' => 'Muy buena opcion para comida ligera.',
            ],
        ];

        foreach ($comentarios as $comentario) {
            Comentario::updateOrCreate($comentario, $comentario);
        }

        $favoritos = [
            ['usuario_id' => $usuario->id, 'receta_id' => $recetas['Chilaquiles verdes con pollo']->id],
            ['usuario_id' => $usuario->id, 'receta_id' => $recetas['Ramen casero especial']->id],
            ['usuario_id' => $admin->id, 'receta_id' => $recetas['Tacos de carne asada']->id],
        ];

        foreach ($favoritos as $favorito) {
            Favorito::updateOrCreate($favorito, $favorito);
        }

        Compra::updateOrCreate(
            [
                'usuario_id' => $usuario->id,
                'receta_id' => $recetas['Ramen casero especial']->id,
            ],
            ['precio_pagado' => 79.00]
        );
    }
}
