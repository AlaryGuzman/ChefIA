<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Comentario;
use App\Models\Compra;
use App\Models\Favorito;
use App\Models\Receta;
use App\Models\Resena;
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

        $editor = User::updateOrCreate(
            ['email' => 'editor@chefia.com'],
            [
                'name' => 'editor',
                'password' => Hash::make('123456'),
                'role' => 'admin',
            ]
        );

        $usuariosExtra = collect([
            ['name' => 'ana', 'email' => 'ana@chefia.com'],
            ['name' => 'luis', 'email' => 'luis@chefia.com'],
            ['name' => 'carlos', 'email' => 'carlos@chefia.com'],
            ['name' => 'sofia', 'email' => 'sofia@chefia.com'],
            ['name' => 'diego', 'email' => 'diego@chefia.com'],
        ])->mapWithKeys(function (array $datos) {
            $modelo = User::updateOrCreate(
                ['email' => $datos['email']],
                [
                    'name' => $datos['name'],
                    'password' => Hash::make('123456'),
                    'role' => 'usuario',
                ]
            );

            return [$datos['name'] => $modelo];
        });

        collect([
            ['name' => 'maria', 'email' => 'maria@chefia.com'],
            ['name' => 'david', 'email' => 'david@chefia.com'],
            ['name' => 'alex', 'email' => 'alex@chefia.com'],
            ['name' => 'denis', 'email' => 'denis@chefia.com'],
            ['name' => 'alary', 'email' => 'alary@chefia.com'],
        ])->each(function (array $datos) {
            User::updateOrCreate(
                ['email' => $datos['email']],
                [
                    'name' => $datos['name'],
                    'password' => Hash::make('123456'),
                    'role' => 'usuario',
                ]
            );
        });

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
            [
                'usuario_id' => $usuariosExtra['ana']->id,
                'categoria_id' => $categorias['Desayunos']->id,
                'titulo' => 'Hot cakes de avena y platano',
                'descripcion' => 'Desayuno suave y rapido con avena, platano maduro y canela.',
                'ingredientes' => "Avena\nPlatano\nHuevo\nCanela\nLeche\nMiel",
                'pasos' => "Licua avena, platano, huevo y leche.\nCocina porciones pequenas en sarten caliente.\nSirve con miel y canela.",
                'tiempo_preparacion' => 15,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $usuariosExtra['luis']->id,
                'categoria_id' => $categorias['Comidas']->id,
                'titulo' => 'Pozole rojo familiar',
                'descripcion' => 'Pozole rojo con maiz cacahuazintle, carne suave y guarniciones frescas.',
                'ingredientes' => "Maiz pozolero\nCarne de cerdo\nChile guajillo\nAjo\nRabano\nLechuga\nOregano",
                'pasos' => "Cuece maiz y carne hasta suavizar.\nLicua chiles con ajo.\nAgrega la salsa al caldo y hierve.\nSirve con guarniciones.",
                'tiempo_preparacion' => 85,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $usuariosExtra['ana']->id,
                'categoria_id' => $categorias['Saludable']->id,
                'titulo' => 'Salmon en salsa de mango',
                'descripcion' => 'Filete dorado con salsa fresca de mango, limon y chile suave.',
                'ingredientes' => "Salmon\nMango\nLimon\nChile serrano\nCilantro\nAjo\nAceite de oliva",
                'pasos' => "Dora el salmon con sal y ajo.\nPica mango, cilantro y chile.\nMezcla con limon.\nSirve la salsa sobre el pescado.",
                'tiempo_preparacion' => 28,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $usuariosExtra['carlos']->id,
                'categoria_id' => $categorias['Rapidas']->id,
                'titulo' => 'Tostadas de tinga express',
                'descripcion' => 'Tinga de pollo con chipotle, cebolla y tostadas crujientes.',
                'ingredientes' => "Pollo deshebrado\nJitomate\nChipotle\nCebolla\nTostadas\nCrema\nQueso",
                'pasos' => "Sofrie cebolla.\nLicua jitomate con chipotle.\nCocina la salsa y agrega pollo.\nMonta las tostadas con crema y queso.",
                'tiempo_preparacion' => 22,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $usuariosExtra['sofia']->id,
                'categoria_id' => $categorias['Postres']->id,
                'titulo' => 'Gelatina mosaico cremosa',
                'descripcion' => 'Postre colorido con cubos de gelatina y base cremosa de vainilla.',
                'ingredientes' => "Gelatinas de sabores\nGrenetina\nLeche evaporada\nLeche condensada\nVainilla",
                'pasos' => "Prepara gelatinas de colores y corta cubos.\nMezcla leches con grenetina hidratada.\nIntegra los cubos y refrigera.",
                'tiempo_preparacion' => 30,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $editor->id,
                'categoria_id' => $categorias['Premium']->id,
                'titulo' => 'Birria de res estilo Jalisco',
                'descripcion' => 'Birria profunda con adobo de chiles secos, carne jugosa y consome especiado.',
                'ingredientes' => "Carne de res\nChile guajillo\nChile ancho\nVinagre\nAjo\nComino\nLaurel",
                'pasos' => "Tuesta e hidrata los chiles.\nLicua con especias y vinagre.\nMarina la carne.\nCocina lento hasta suavizar y sirve con consome.",
                'tiempo_preparacion' => 140,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => true,
                'precio' => 119.00,
            ],
            [
                'usuario_id' => $admin->id,
                'categoria_id' => $categorias['Premium']->id,
                'titulo' => 'Mole negro oaxaqueno',
                'descripcion' => 'Salsa compleja con chiles, especias, chocolate y tecnica tradicional.',
                'ingredientes' => "Chile chilhuacle\nChile pasilla\nAjonjoli\nChocolate\nPlatano macho\nTortilla\nPollo",
                'pasos' => "Tuesta chiles y especias con cuidado.\nMuele todo con caldo.\nFrie la pasta lentamente.\nCocina con pollo hasta lograr una salsa brillante.",
                'tiempo_preparacion' => 160,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => true,
                'precio' => 149.00,
            ],
            [
                'usuario_id' => $usuariosExtra['diego']->id,
                'categoria_id' => $categorias['Premium']->id,
                'titulo' => 'Cheesecake de frutos rojos',
                'descripcion' => 'Cheesecake cremoso con costra de galleta y salsa brillante de frutos rojos.',
                'ingredientes' => "Queso crema\nGalletas\nMantequilla\nAzucar\nHuevos\nFrutos rojos\nVainilla",
                'pasos' => "Prepara la base de galleta.\nBate queso, azucar y huevos.\nHornea a baja temperatura.\nCubre con salsa de frutos rojos.",
                'tiempo_preparacion' => 75,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => true,
                'precio' => 89.00,
            ],
            [
                'usuario_id' => $usuariosExtra['ana']->id,
                'categoria_id' => $categorias['Desayunos']->id,
                'titulo' => 'Molletes crujientes con pico de gallo',
                'descripcion' => 'Bolillo dorado con frijoles, queso fundido y pico de gallo fresco.',
                'ingredientes' => "Bolillo\nFrijoles refritos\nQueso manchego\nJitomate\nCebolla\nCilantro\nChile serrano",
                'pasos' => "Abre el bolillo y unta frijoles.\nAgrega queso y hornea hasta gratinar.\nMezcla pico de gallo y sirve encima.",
                'tiempo_preparacion' => 16,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $usuariosExtra['luis']->id,
                'categoria_id' => $categorias['Rapidas']->id,
                'titulo' => 'Quesadillas de flor de calabaza',
                'descripcion' => 'Quesadillas suaves con flor de calabaza, epazote y queso Oaxaca.',
                'ingredientes' => "Tortillas\nFlor de calabaza\nQueso Oaxaca\nEpazote\nCebolla\nAceite",
                'pasos' => "Sofrie cebolla con flor de calabaza.\nAgrega epazote.\nRellena tortillas con queso y cocina hasta fundir.",
                'tiempo_preparacion' => 18,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $usuariosExtra['carlos']->id,
                'categoria_id' => $categorias['Comidas']->id,
                'titulo' => 'Arroz rojo con verduras',
                'descripcion' => 'Arroz casero con jitomate, zanahoria, chicharos y caldo ligero.',
                'ingredientes' => "Arroz\nJitomate\nZanahoria\nChicharos\nAjo\nCebolla\nCaldo",
                'pasos' => "Lava el arroz y sofrie.\nLicua jitomate con ajo y cebolla.\nAgrega caldo y verduras.\nCocina tapado hasta secar.",
                'tiempo_preparacion' => 32,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $usuariosExtra['sofia']->id,
                'categoria_id' => $categorias['Saludable']->id,
                'titulo' => 'Wrap integral de pollo',
                'descripcion' => 'Wrap ligero con pollo, vegetales crujientes y aderezo de yogur.',
                'ingredientes' => "Tortilla integral\nPechuga de pollo\nLechuga\nPepino\nZanahoria\nYogur\nLimon",
                'pasos' => "Dora el pollo en tiras.\nMezcla yogur con limon.\nRellena la tortilla con vegetales, pollo y aderezo.",
                'tiempo_preparacion' => 24,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $usuariosExtra['diego']->id,
                'categoria_id' => $categorias['Postres']->id,
                'titulo' => 'Brownies de cacao intenso',
                'descripcion' => 'Brownies humedos con cacao, nuez y centro ligeramente cremoso.',
                'ingredientes' => "Cacao\nHarina\nAzucar\nHuevos\nMantequilla\nNuez\nVainilla",
                'pasos' => "Derrite mantequilla y mezcla con cacao.\nAgrega huevos, azucar y harina.\nHornea hasta que el centro quede humedo.",
                'tiempo_preparacion' => 40,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => false,
                'precio' => null,
            ],
            [
                'usuario_id' => $editor->id,
                'categoria_id' => $categorias['Premium']->id,
                'titulo' => 'Cochinita pibil lenta',
                'descripcion' => 'Cerdo marinado en achiote, naranja agria y especias con coccion lenta.',
                'ingredientes' => "Carne de cerdo\nAchiote\nNaranja agria\nAjo\nLaurel\nCebolla morada\nHabanero",
                'pasos' => "Licua achiote con naranja y especias.\nMarina la carne.\nEnvuelve y cocina lento hasta deshebrar.\nSirve con cebolla encurtida.",
                'tiempo_preparacion' => 180,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => true,
                'precio' => 129.00,
            ],
            [
                'usuario_id' => $admin->id,
                'categoria_id' => $categorias['Premium']->id,
                'titulo' => 'Risotto de hongos al parmesano',
                'descripcion' => 'Arroz cremoso con hongos salteados, caldo caliente y queso parmesano.',
                'ingredientes' => "Arroz arborio\nHongos\nCaldo\nVino blanco\nParmesano\nMantequilla\nCebolla",
                'pasos' => "Sofrie cebolla y arroz.\nAgrega vino y caldo poco a poco.\nSaltea hongos.\nTermina con mantequilla y parmesano.",
                'tiempo_preparacion' => 48,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => true,
                'precio' => 109.00,
            ],
            [
                'usuario_id' => $usuariosExtra['ana']->id,
                'categoria_id' => $categorias['Premium']->id,
                'titulo' => 'Tarta fina de manzana',
                'descripcion' => 'Postre elegante con masa crujiente, manzana laminada y brillo de miel.',
                'ingredientes' => "Masa hojaldre\nManzanas\nAzucar\nCanela\nMiel\nMantequilla\nLimon",
                'pasos' => "Extiende la masa.\nAcomoda manzana laminada.\nBarniza con mantequilla, azucar y canela.\nHornea y termina con miel.",
                'tiempo_preparacion' => 62,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => true,
                'precio' => 69.00,
            ],
            [
                'usuario_id' => $usuariosExtra['luis']->id,
                'categoria_id' => $categorias['Premium']->id,
                'titulo' => 'Costillas glaseadas con tamarindo',
                'descripcion' => 'Costillas suaves con glaseado dulce, acido y picante de tamarindo.',
                'ingredientes' => "Costillas de cerdo\nTamarindo\nPiloncillo\nChile de arbol\nAjo\nSoya\nVinagre",
                'pasos' => "Cocina las costillas hasta suavizar.\nReduce tamarindo con piloncillo, chile y soya.\nBarniza y hornea hasta caramelizar.",
                'tiempo_preparacion' => 120,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => true,
                'precio' => 139.00,
            ],
            [
                'usuario_id' => $usuariosExtra['carlos']->id,
                'categoria_id' => $categorias['Premium']->id,
                'titulo' => 'Enchiladas suizas gratinadas',
                'descripcion' => 'Enchiladas cremosas con salsa verde, pollo y gratinado dorado.',
                'ingredientes' => "Tortillas\nPollo deshebrado\nTomatillo\nCrema\nQueso manchego\nCilantro\nCebolla",
                'pasos' => "Prepara salsa verde cremosa.\nRellena tortillas con pollo.\nBaña con salsa, cubre con queso y gratina.",
                'tiempo_preparacion' => 52,
                'imagen' => '/img/fondo-login.webp',
                'es_premium' => true,
                'precio' => 95.00,
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
            [
                'usuario_id' => $usuariosExtra['ana']->id,
                'receta_id' => $recetas['Tacos de carne asada']->id,
                'contenido' => 'Los hice para cenar y quedaron con mucho sabor.',
            ],
            [
                'usuario_id' => $usuariosExtra['luis']->id,
                'receta_id' => $recetas['Pasta cremosa con champinones']->id,
                'contenido' => 'Excelente para salir del apuro entre semana.',
            ],
            [
                'usuario_id' => $usuariosExtra['ana']->id,
                'receta_id' => $recetas['Salmon en salsa de mango']->id,
                'contenido' => 'La salsa de mango levanta muchisimo el plato.',
            ],
            [
                'usuario_id' => $usuariosExtra['carlos']->id,
                'receta_id' => $recetas['Ramen casero especial']->id,
                'contenido' => 'Vale la pena comprarla, el caldo queda intenso.',
            ],
            [
                'usuario_id' => $usuariosExtra['sofia']->id,
                'receta_id' => $recetas['Birria de res estilo Jalisco']->id,
                'contenido' => 'La deje lenta y el consome salio buenisimo.',
            ],
            [
                'usuario_id' => $usuariosExtra['diego']->id,
                'receta_id' => $recetas['Gelatina mosaico cremosa']->id,
                'contenido' => 'Queda muy vistosa para reuniones familiares.',
            ],
            [
                'usuario_id' => $editor->id,
                'receta_id' => $recetas['Mole negro oaxaqueno']->id,
                'contenido' => 'Receta larga, pero perfecta para probar una tecnica mas seria.',
            ],
            [
                'usuario_id' => $admin->id,
                'receta_id' => $recetas['Molletes crujientes con pico de gallo']->id,
                'contenido' => 'Buenisimos para desayunar sin complicarse.',
            ],
            [
                'usuario_id' => $usuario->id,
                'receta_id' => $recetas['Quesadillas de flor de calabaza']->id,
                'contenido' => 'El epazote le da un aroma muy casero.',
            ],
            [
                'usuario_id' => $usuariosExtra['sofia']->id,
                'receta_id' => $recetas['Risotto de hongos al parmesano']->id,
                'contenido' => 'Queda muy cremoso si agregas el caldo poco a poco.',
            ],
            [
                'usuario_id' => $usuariosExtra['diego']->id,
                'receta_id' => $recetas['Costillas glaseadas con tamarindo']->id,
                'contenido' => 'La salsa de tamarindo queda intensa y brillante.',
            ],
        ];

        foreach ($comentarios as $comentario) {
            Comentario::updateOrCreate($comentario, $comentario);
        }

        $favoritos = [
            ['usuario_id' => $usuario->id, 'receta_id' => $recetas['Chilaquiles verdes con pollo']->id],
            ['usuario_id' => $usuario->id, 'receta_id' => $recetas['Ramen casero especial']->id],
            ['usuario_id' => $admin->id, 'receta_id' => $recetas['Tacos de carne asada']->id],
            ['usuario_id' => $usuariosExtra['ana']->id, 'receta_id' => $recetas['Chilaquiles verdes con pollo']->id],
            ['usuario_id' => $usuariosExtra['ana']->id, 'receta_id' => $recetas['Pay de limon frio']->id],
            ['usuario_id' => $usuariosExtra['ana']->id, 'receta_id' => $recetas['Cheesecake de frutos rojos']->id],
            ['usuario_id' => $usuariosExtra['luis']->id, 'receta_id' => $recetas['Tacos de carne asada']->id],
            ['usuario_id' => $usuariosExtra['luis']->id, 'receta_id' => $recetas['Pozole rojo familiar']->id],
            ['usuario_id' => $usuariosExtra['luis']->id, 'receta_id' => $recetas['Birria de res estilo Jalisco']->id],
            ['usuario_id' => $usuariosExtra['ana']->id, 'receta_id' => $recetas['Salmon en salsa de mango']->id],
            ['usuario_id' => $usuariosExtra['ana']->id, 'receta_id' => $recetas['Bowl de quinoa y verduras']->id],
            ['usuario_id' => $usuariosExtra['ana']->id, 'receta_id' => $recetas['Mole negro oaxaqueno']->id],
            ['usuario_id' => $usuariosExtra['carlos']->id, 'receta_id' => $recetas['Pasta cremosa con champinones']->id],
            ['usuario_id' => $usuariosExtra['carlos']->id, 'receta_id' => $recetas['Tostadas de tinga express']->id],
            ['usuario_id' => $usuariosExtra['carlos']->id, 'receta_id' => $recetas['Ramen casero especial']->id],
            ['usuario_id' => $usuariosExtra['sofia']->id, 'receta_id' => $recetas['Gelatina mosaico cremosa']->id],
            ['usuario_id' => $usuariosExtra['sofia']->id, 'receta_id' => $recetas['Asado de boda estilo ChefIA']->id],
            ['usuario_id' => $usuariosExtra['diego']->id, 'receta_id' => $recetas['Hot cakes de avena y platano']->id],
            ['usuario_id' => $usuariosExtra['diego']->id, 'receta_id' => $recetas['Ensalada de atun con aguacate']->id],
            ['usuario_id' => $editor->id, 'receta_id' => $recetas['Salmon en salsa de mango']->id],
            ['usuario_id' => $editor->id, 'receta_id' => $recetas['Pozole rojo familiar']->id],
            ['usuario_id' => $admin->id, 'receta_id' => $recetas['Molletes crujientes con pico de gallo']->id],
            ['usuario_id' => $usuario->id, 'receta_id' => $recetas['Quesadillas de flor de calabaza']->id],
            ['usuario_id' => $usuariosExtra['ana']->id, 'receta_id' => $recetas['Arroz rojo con verduras']->id],
            ['usuario_id' => $usuariosExtra['luis']->id, 'receta_id' => $recetas['Wrap integral de pollo']->id],
            ['usuario_id' => $usuariosExtra['carlos']->id, 'receta_id' => $recetas['Brownies de cacao intenso']->id],
            ['usuario_id' => $usuariosExtra['sofia']->id, 'receta_id' => $recetas['Cochinita pibil lenta']->id],
            ['usuario_id' => $usuariosExtra['diego']->id, 'receta_id' => $recetas['Risotto de hongos al parmesano']->id],
            ['usuario_id' => $editor->id, 'receta_id' => $recetas['Tarta fina de manzana']->id],
            ['usuario_id' => $admin->id, 'receta_id' => $recetas['Costillas glaseadas con tamarindo']->id],
            ['usuario_id' => $usuario->id, 'receta_id' => $recetas['Enchiladas suizas gratinadas']->id],
        ];

        foreach ($favoritos as $favorito) {
            Favorito::updateOrCreate($favorito, $favorito);
        }

        $compras = [
            ['usuario_id' => $usuario->id, 'receta_id' => $recetas['Ramen casero especial']->id, 'precio_pagado' => 79.00, 'tarjeta_ultimos4' => '4242', 'estado' => 'entregado'],
            ['usuario_id' => $usuario->id, 'receta_id' => $recetas['Asado de boda estilo ChefIA']->id, 'precio_pagado' => 99.00, 'tarjeta_ultimos4' => '4242', 'estado' => 'enviado'],
            ['usuario_id' => $usuariosExtra['ana']->id, 'receta_id' => $recetas['Cheesecake de frutos rojos']->id, 'precio_pagado' => 89.00, 'tarjeta_ultimos4' => '1881', 'estado' => 'pagado'],
            ['usuario_id' => $usuariosExtra['luis']->id, 'receta_id' => $recetas['Birria de res estilo Jalisco']->id, 'precio_pagado' => 119.00, 'tarjeta_ultimos4' => null, 'estado' => 'pendiente_pago', 'metodo_pago' => 'Efectivo OXXO'],
            ['usuario_id' => $usuariosExtra['ana']->id, 'receta_id' => $recetas['Mole negro oaxaqueno']->id, 'precio_pagado' => 149.00, 'tarjeta_ultimos4' => '1881', 'estado' => 'entregado'],
            ['usuario_id' => $usuariosExtra['carlos']->id, 'receta_id' => $recetas['Ramen casero especial']->id, 'precio_pagado' => 79.00, 'tarjeta_ultimos4' => '9021', 'estado' => 'cancelado'],
            ['usuario_id' => $usuariosExtra['sofia']->id, 'receta_id' => $recetas['Asado de boda estilo ChefIA']->id, 'precio_pagado' => 99.00, 'tarjeta_ultimos4' => '6610', 'estado' => 'pagado'],
        ];

        foreach ($compras as $compra) {
            $estado = $compra['estado'];
            Compra::updateOrCreate(
                [
                    'usuario_id' => $compra['usuario_id'],
                    'receta_id' => $compra['receta_id'],
                ],
                [
                    'precio_pagado' => $compra['precio_pagado'],
                    'metodo_pago' => $compra['metodo_pago'] ?? 'Tarjeta simulada',
                    'estado' => $estado,
                    'tarjeta_ultimos4' => $compra['tarjeta_ultimos4'],
                    'referencia_pago' => 'CHF-SEED-' . $compra['usuario_id'] . '-' . $compra['receta_id'],
                    'referencia_efectivo' => ($compra['metodo_pago'] ?? '') === 'Efectivo OXXO'
                        ? 'OXXO-SEED-' . $compra['usuario_id'] . '-' . $compra['receta_id']
                        : null,
                    'referencia_reembolso' => $estado === 'cancelado'
                        ? 'RMB-SEED-' . $compra['usuario_id'] . '-' . $compra['receta_id']
                        : null,
                    'motivo_cancelacion' => $estado === 'cancelado'
                        ? 'Pedido cancelado por administracion. Se genero reembolso.'
                        : null,
                    'pagado_at' => in_array($estado, ['pagado', 'enviado', 'entregado', 'cancelado'], true) ? now()->subDays(2) : null,
                    'enviado_at' => in_array($estado, ['enviado', 'entregado'], true) ? now()->subDay() : null,
                    'entregado_at' => $estado === 'entregado' ? now() : null,
                    'cancelado_at' => $estado === 'cancelado' ? now() : null,
                ]
            );
        }

        $resenas = [
            ['usuario_id' => $usuario->id, 'receta_id' => $recetas['Ramen casero especial']->id, 'calificacion' => 5, 'comentario' => 'El caldo queda profundo y la receta se entiende muy bien.'],
            ['usuario_id' => $usuariosExtra['ana']->id, 'receta_id' => $recetas['Cheesecake de frutos rojos']->id, 'calificacion' => 4, 'comentario' => 'Muy rico, solo cuidaria explicar mejor el horneado.'],
            ['usuario_id' => $usuariosExtra['ana']->id, 'receta_id' => $recetas['Mole negro oaxaqueno']->id, 'calificacion' => 5, 'comentario' => 'Completa y con tecnica, excelente para receta premium.'],
            ['usuario_id' => $usuariosExtra['sofia']->id, 'receta_id' => $recetas['Asado de boda estilo ChefIA']->id, 'calificacion' => 3, 'comentario' => 'Buen sabor, aunque los pasos podrian ser mas detallados.'],
        ];

        foreach ($resenas as $resena) {
            $compra = Compra::where('usuario_id', $resena['usuario_id'])
                ->where('receta_id', $resena['receta_id'])
                ->whereIn('estado', ['pagado', 'enviado', 'entregado'])
                ->first();

            Resena::updateOrCreate(
                [
                    'usuario_id' => $resena['usuario_id'],
                    'receta_id' => $resena['receta_id'],
                ],
                [
                    'compra_id' => $compra?->id,
                    'calificacion' => $resena['calificacion'],
                    'comentario' => $resena['comentario'],
                ]
            );
        }
    }
}
