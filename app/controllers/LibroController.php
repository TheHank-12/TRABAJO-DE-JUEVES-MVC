
<?php

class LibroController extends Controller
{
    private $libroModel;

    public function __construct()
    {
        $this->libroModel = new Libro();
    }

    // Mostrar catálogo de libros
    public function catalogo()
    {
        // Obtener filtros enviados por GET
        $categoria = isset($_GET['categoria'])
            ? (int) $_GET['categoria']
            : 0;

        $editorial = isset($_GET['editorial'])
            ? (int) $_GET['editorial']
            : 0;

        $orden = isset($_GET['orden'])
            ? $_GET['orden']
            : 'recientes';

        $busqueda = isset($_GET['q'])
            ? trim($_GET['q'])
            : '';

        // Obtener información desde el modelo
        $categorias = $this->libroModel->obtenerCategorias();

        $editoriales = $this->libroModel->obtenerEditoriales();

        $librosTop = $this->libroModel->obtenerMejorValorados();

        $libros = $this->libroModel->obtenerCatalogo(
            $categoria,
            $editorial,
            $orden,
            $busqueda
        );

        // Enviar información a la vista
        $this->view('libros/catalogo', [
            'titulo_pagina' => 'Catálogo de Libros',
            'categorias' => $categorias,
            'editoriales' => $editoriales,
            'librosTop' => $librosTop,
            'libros' => $libros,
            'categoria_id' => $categoria,
            'editorial_id' => $editorial,
            'orden' => $orden,
            'busqueda' => $busqueda
        ]);
    }
}
