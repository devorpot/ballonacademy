<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function index()
    {
        // Solo renderiza la lista (los posts están mockeados en el componente Vue)
        return Inertia::render('Frontend/Blog/Index');
    }

    public function show($slug)
    {
        // Para demo: puedes usar el slug como título
        $post = [
            'title' => ucwords(str_replace('-', ' ', $slug)),
            'image' => 'https://picsum.photos/seed/' . $slug . '/900/400',
            'content' => 'Este es un artículo de ejemplo sobre la técnica de globoflexia. 
                          Aquí puedes extender el contenido completo con tips, técnicas y ejemplos.'
        ];

        return Inertia::render('Frontend/Blog/Show', [
            'post' => $post,
        ]);
    }
}
