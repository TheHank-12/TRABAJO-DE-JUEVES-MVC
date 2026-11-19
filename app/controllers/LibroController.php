
<?php
class LibroController extends Controller
{
    private $model;

    public function __construct()
    {
        $this->model = new Libro();
    }

    private function token()
    {
        if (empty($_SESSION['csrf_catalogo'])) {
            $_SESSION['csrf_catalogo'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_catalogo'];
    }

    private function validarToken()
    {
        $actual = (string) ($_SESSION['csrf_catalogo'] ?? '');
        $enviado = (string) ($_POST['csrf_token'] ?? '');

        if ($actual === '' || !hash_equals($actual, $enviado)) {
            http_response_code(419);
            exit('La sesión del formulario venció. Recargue la página e inténtelo nuevamente.');
        }
    }

    private function personal()
    {
        requerir_personal();
    }

    // Catálogo público de libros
    public function catalogo()
    {
        $categoria = max(0, (int) ($_GET['categoria'] ?? 0));
        $editorial = max(0, (int) ($_GET['editorial'] ?? 0));
        $orden = (string) ($_GET['orden'] ?? 'recientes');
        $busqueda = trim((string) ($_GET['q'] ?? ''));
        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));

        $porPagina = 12;

        $total = $this->model->contarCatalogo(
            $categoria,
            $editorial,
            $busqueda,
            true
        );

        $paginas = max(1, (int) ceil($total / $porPagina));
        $pagina = min($pagina, $paginas);

        $this->view('libros/catalogo', [
            'titulo_pagina' => 'Catálogo de Libros',
            'categorias' => $this->model->obtenerCategorias(),
            'editoriales' => $this->model->obtenerEditoriales(),
            'librosTop' => $this->model->obtenerMejorValorados(),
            'libros' => $this->model->obtenerCatalogo(
                $categoria,
                $editorial,
                $orden,
                $busqueda,
                $porPagina,
                ($pagina - 1) * $porPagina,
                true
            ),
            'categoria_id' => $categoria,
            'editorial_id' => $editorial,
            'orden' => $orden,
            'busqueda' => $busqueda,
            'pagina' => $pagina,
            'paginas' => $paginas,
            'totalLibros' => $total
        ]);
    }

    // Gestión administrativa de libros
    public function gestion()
    {
        $this->personal();

        $q = trim((string) ($_GET['q'] ?? ''));

        $this->view('libros/gestion', [
            'titulo_pagina' => 'Gestión de Libros',
            'libros' => $this->model->listarAdministracion($q),
            'busqueda' => $q,
            'csrf_token' => $this->token()
        ]);
    }

    // Detalle público de un libro
    public function detalle()
    {
        $id = max(0, (int) ($_GET['id'] ?? 0));
        $libro = $this->model->obtenerPorId($id);

        if (!$libro) {
            http_response_code(404);

            $this->view('libros/detalle', [
                'titulo_pagina' => 'Libro no encontrado',
                'libro' => null,
                'resenas' => [],
                'csrf_token' => $this->token()
            ]);

            return;
        }

        $this->view('libros/detalle', [
            'titulo_pagina' => $libro['titulo'],
            'libro' => $libro,
            'resenas' => $this->model->obtenerResenas($id),
            'csrf_token' => $this->token()
        ]);
    }

    // Formulario para registrar o editar un libro
    public function formulario()
    {
        $this->personal();

        $id = max(0, (int) ($_GET['id'] ?? 0));
        $libro = $id ? $this->model->obtenerPorId($id) : null;

        if ($id && !$libro) {
            $_SESSION['mensaje'] = 'Libro no encontrado.';
            $_SESSION['tipo_mensaje'] = 'danger';

            $this->redirect('public/libros');
            return;
        }

        $datos = $libro ?: [
            'titulo' => '',
            'isbn' => '',
            'id_categoria' => '',
            'id_autor' => '',
            'id_editorial' => '',
            'id_proveedor' => '',
            'precio_venta' => '0.00',
            'precio_alquiler' => '0.00',
            'stock' => 0,
            'portada' => 'uploads/portadas/no-cover.png.jpg',
            'fecha_publicacion' => '',
            'estado' => 'DISPONIBLE'
        ];

        $this->view('libros/formulario', [
            'titulo_pagina' => $id ? 'Editar Libro' : 'Registrar Libro',
            'datos' => $datos,
            'categorias' => $this->model->obtenerCategorias(),
            'autores' => $this->model->obtenerAutores(),
            'editoriales' => $this->model->obtenerEditoriales(),
            'proveedores' => $this->model->obtenerProveedores(),
            'error' => '',
            'csrf_token' => $this->token(),
            'id' => $id
        ]);
    }

    // Guardar un libro nuevo o actualizado
    public function guardar()
    {
        $this->personal();
        $this->validarToken();

        $id = max(0, (int) ($_POST['id_libro'] ?? 0));
        $d = $_POST;

        $d['portada'] = trim(
            (string) ($_POST['portada_actual'] ?? 'uploads/portadas/no-cover.png.jpg')
        );

        // Procesar portada si se cargó una imagen
        if (
            isset($_FILES['portada']) &&
            ($_FILES['portada']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
        ) {
            $f = $_FILES['portada'];

            if ($f['error'] !== UPLOAD_ERR_OK) {
                throw new RuntimeException('No se pudo cargar la portada.');
            }

            if ($f['size'] > 3 * 1024 * 1024) {
                throw new InvalidArgumentException('La portada no debe superar 3 MB.');
            }

            $info = @getimagesize($f['tmp_name']);
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));

            if (!$info || !in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                throw new InvalidArgumentException('La portada debe ser JPG, PNG o WEBP.');
            }

            $dir = ROOT_PATH . '/public/uploads/portadas/';

            if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
                throw new RuntimeException('No se pudo preparar la carpeta de portadas.');
            }

            $nombre = 'libro_' . bin2hex(random_bytes(8)) . '.' . $ext;

            if (!move_uploaded_file($f['tmp_name'], $dir . $nombre)) {
                throw new RuntimeException('No se pudo guardar la portada.');
            }

            $d['portada'] = 'uploads/portadas/' . $nombre;
        }

        try {
            $this->model->guardar($d, $id);

            $_SESSION['mensaje'] = $id
                ? 'Libro actualizado correctamente.'
                : 'Libro registrado correctamente.';

            $_SESSION['tipo_mensaje'] = 'success';

            $this->redirect('public/libros');

        } catch (Throwable $e) {
            $this->view('libros/formulario', [
                'titulo_pagina' => $id ? 'Editar Libro' : 'Registrar Libro',
                'datos' => array_merge($d, ['id_libro' => $id]),
                'categorias' => $this->model->obtenerCategorias(),
                'autores' => $this->model->obtenerAutores(),
                'editoriales' => $this->model->obtenerEditoriales(),
                'proveedores' => $this->model->obtenerProveedores(),
                'error' => $e->getMessage(),
                'csrf_token' => $this->token(),
                'id' => $id
            ]);
        }
    }

    // Registrar reseña de un libro
    public function resena()
    {
        requerir_autenticacion();

        $this->token();
        $this->validarToken();

        $id = max(0, (int) ($_POST['id_libro'] ?? 0));

        try {
            if (!$this->model->obtenerPorId($id)) {
                throw new InvalidArgumentException('El libro no existe.');
            }

            $this->model->guardarResena(
                $id,
                (int) $_SESSION['usuario_id'],
                (int) ($_POST['puntuacion'] ?? 5),
                $_POST['comentario'] ?? ''
            );

            $_SESSION['mensaje'] = 'Gracias. Tu reseña fue registrada.';
            $_SESSION['tipo_mensaje'] = 'success';

        } catch (Throwable $e) {
            $_SESSION['mensaje'] = $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'danger';
        }

        $this->redirect('libros/detalle?id=' . $id);
    }

    // Eliminar libro: solo administrador
    public function eliminar()
    {
        requerir_admin();
        $this->validarToken();

        $id = max(0, (int) ($_POST['id_libro'] ?? 0));

        try {
            $ok = $this->model->eliminar($id);

            $_SESSION['mensaje'] = $ok
                ? 'Libro eliminado.'
                : 'No se encontró el libro.';

            $_SESSION['tipo_mensaje'] = $ok ? 'success' : 'warning';

        } catch (Throwable $e) {
            $_SESSION['mensaje'] = $e->getMessage();
            $_SESSION['tipo_mensaje'] = 'danger';
        }

        $this->redirect('public/libros');
    }
}
