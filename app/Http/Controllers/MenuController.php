<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Menú público (sin login): link fijo /menu y QR de cada mesa /menu/mesa/{token_qr}.
 */
class MenuController extends Controller
{
    /**
     * Láminas del menú: bodegones de dominio público (public/img/menu) que ilustran cada sección.
     * Se reparten en orden entre las categorías; la portada usa la de quesos.
     * El crédito de cada una se muestra en el pie del menú.
     */
    public const LAMINAS = [
        ['archivo' => 'jamon.jpg', 'alt' => 'Pintura de un jamón sobre un plato, con pan y una botella', 'autor' => 'Philippe Rousseau', 'anio' => '1870', 'fuente' => 'https://www.metmuseum.org/art/collection/search/437513'],
        ['archivo' => 'pan.jpg', 'alt' => 'Pintura de un pan, un vaso de vino y un jarro de barro', 'autor' => 'Georg Flegel', 'anio' => '1625', 'fuente' => 'https://www.metmuseum.org/art/collection/search/436305'],
        ['archivo' => 'pastel.jpg', 'alt' => 'Pintura de un pastel de carne en una mesa con copas', 'autor' => 'Pieter Claesz', 'anio' => '1625', 'fuente' => 'https://www.artic.edu/artworks/21682'],
        ['archivo' => 'mesa.jpg', 'alt' => 'Pintura de una mesa servida con una copa grande, pan y limón', 'autor' => 'Pieter Claesz', 'anio' => '1647', 'fuente' => 'https://commons.wikimedia.org/wiki/File:Still_Life_with_Herring,_Wine_and_Bread_LACMA_M.2009.106.19_(1_of_2).jpg'],
        ['archivo' => 'botella.jpg', 'alt' => 'Pintura de una botella de vino, una copa y pan', 'autor' => 'Claude Monet', 'anio' => '1862', 'fuente' => 'https://commons.wikimedia.org/wiki/File:Still_Life_with_Bottle,_Carafe,_Bread,_and_Wine_A26263.jpg'],
        ['archivo' => 'copa.jpg', 'alt' => 'Pintura de una copa dorada con vino y platos de plata', 'autor' => 'Willem Claesz. Heda', 'anio' => '1647', 'fuente' => 'https://www.artic.edu/artworks/257286'],
    ];

    public const LAMINA_PORTADA = ['archivo' => 'quesos.jpg', 'alt' => 'Pintura de quesos, pan, un jarro y una copa sobre una mesa', 'autor' => 'Clara Peeters', 'anio' => '1615', 'fuente' => 'https://commons.wikimedia.org/wiki/File:Clara_Peeters_-_Still_Life_with_Cheeses,_Almonds_and_Pretzels.jpg'];

    public function index(Request $request): View
    {
        return $this->mostrar($request);
    }

    public function mesa(Request $request, string $tokenQr): View
    {
        // Si el token no existe, 404
        $mesa = Mesa::where('token_qr', $tokenQr)->firstOrFail();

        return $this->mostrar($request, $mesa);
    }

    /**
     * Categorías activas con sus productos, aplicando el filtro y la búsqueda.
     */
    private function mostrar(Request $request, ?Mesa $mesa = null): View
    {
        $buscar = $request->string('buscar')->trim()->toString();
        $categoriaId = $request->integer('categoria') ?: null;

        $categorias = Categoria::activas()->ordenadas()->get();

        $secciones = Categoria::activas()
            ->ordenadas()
            ->when($categoriaId, fn ($query) => $query->whereKey($categoriaId))
            ->with(['productos' => function ($query) use ($buscar) {
                $query->with('vinoSugerido')
                    ->when($buscar !== '', function ($query) use ($buscar) {
                        $query->where(function ($query) use ($buscar) {
                            $query->where('nombre', 'like', "%{$buscar}%")
                                ->orWhere('descripcion', 'like', "%{$buscar}%");
                        });
                    })
                    // Primero los disponibles, los agotados al final
                    ->orderByDesc('disponible')
                    ->orderBy('nombre');
            }])
            ->get()
            ->filter(fn (Categoria $categoria) => $categoria->productos->isNotEmpty());

        // Lámina de cada categoría según su posición en el menú completo,
        // para que no cambie cuando se filtra
        $laminas = $categorias->values()
            ->mapWithKeys(fn (Categoria $categoria, int $indice) => [
                $categoria->id => self::LAMINAS[$indice % count(self::LAMINAS)],
            ]);

        // Secciones donde todo es vino: se imprimen en papel color vino
        $seccionesDeVino = $secciones
            ->filter(fn (Categoria $categoria) => $categoria->productos->every('es_vino'))
            ->pluck('id')
            ->all();

        $portada = self::LAMINA_PORTADA;
        $creditos = collect([self::LAMINA_PORTADA, ...self::LAMINAS]);

        return view('menu.index', compact(
            'mesa', 'categorias', 'secciones', 'buscar', 'categoriaId',
            'laminas', 'seccionesDeVino', 'portada', 'creditos'
        ));
    }
}
