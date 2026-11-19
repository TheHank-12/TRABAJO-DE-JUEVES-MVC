
<?php
class CatalogoController extends Controller
{
    private $model;

    private $tipos = [
        'categorias' => 'Categorías',
        'autores' => 'Autores',
        'editoriales' => 'Editoriales'
    ];

    public function __construct()
    {
        $this->model = new Catalogo();
    }

    
    
    private function tipo()
    {
        $tiposPermitidos = [
            'categorias',
            'autores',
            'editoriales'
        ];

        // Primero identificar la sección mediante la URL.
        $ruta = parse_url(
            $_SERVER['REQUEST_URI'] ?? '/',
            PHP_URL_PATH
        );

        $ruta = is_string($ruta) ? $ruta : '/';

        // Quitar la carpeta base del proyecto.
        $base = rtrim(BASE_URL, '/');

        if ($base !== '' && strpos($ruta, $base) === 0) {
            $ruta = substr($ruta, strlen($base));
        }

        // Quitar /public.
        $ruta = preg_replace('#^/?public(?:/|$)#', '/', $ruta);

        $partes = explode('/', trim($ruta, '/'));

        foreach ($partes as $parte) {
            if (in_array($parte, $tiposPermitidos, true)) {
                return $parte;
            }
        }

        // Si no se encontró en la URL, revisar los datos enviados.
        $t = $_POST['tipo'] ?? $_GET['tipo'] ?? '';

        if (in_array($t, $tiposPermitidos, true)) {
            return $t;
        }

        http_response_code(404);
        exit('Sección del catálogo no identificada.');
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

    // Mostrar categorías, autores o editoriales
    public function gestion()
    {
        requerir_personal();

        $tipo = $this->tipo();
        $q = trim((string) ($_GET['q'] ?? ''));

        $this->view('catalogo/gestion_entidad', [
            'titulo_pagina' => $this->tipos[$tipo],
            'tipo' => $tipo,
            'nombre_tipo' => $this->tipos[$tipo],
            'config' => $this->model->configuracion($tipo),
            'registros' => $this->model->listar($tipo, $q),
            'busqueda' => $q,
            'csrf_token' => $this->token()
        ]);
    }

    // Formulario para crear o editar
    public function formulario()
    {
        requerir_personal();

        $tipo = $this->tipo();
        $id = max(0, (int) ($_GET['id'] ?? 0));

        $registro = $id
            ? $this->model->obtener($tipo, $id)
            : null;

        if ($id && !$registro) {
            $_SESSION['mensaje'] = 'Registro no encontrado.';
            $_SESSION['tipo_mensaje'] = 'danger';

            $this->redirect($tipo);
            return;
        }

        $this->view('catalogo/formulario_entidad', [
            'titulo_pagina' => ($id ? 'Editar ' : 'Nueva ') . $this->tipos[$tipo],
            'tipo' => $tipo,
            'nombre_tipo' => $this->tipos[$tipo],
            'config' => $this->model->configuracion($tipo),
            'registro' => $registro ?: [],
            'error' => '',
            'csrf_token' => $this->token(),
            'id' => $id
        ]);
    }

    // Guardar nuevo registro o actualización
    public function guardar()
    {
        requerir_personal();
        $this->validarToken();

        $tipo = $this->tipo();
        $id = max(0, (int) ($_POST['id'] ?? 0));

        try {
            $this->model->guardar($tipo, $_POST, $id);

            $_SESSION['mensaje'] = $this->tipos[$tipo] . ' guardado correctamente.';
            $_SESSION['tipo_mensaje'] = 'success';

            $this->redirect($tipo);

        } catch (Throwable $e) {
            $this->view('catalogo/formulario_entidad', [
                'titulo_pagina' => ($id ? 'Editar ' : 'Nueva ') . $this->tipos[$tipo],
                'tipo' => $tipo,
                'nombre_tipo' => $this->tipos[$tipo],
                'config' => $this->model->configuracion($tipo),
                'registro' => $_POST,
                'error' => $e->getMessage(),
                'csrf_token' => $this->token(),
                'id' => $id
            ]);
        }
    }

    // Eliminar registro: solo administrador
    public function eliminar()
    {
        requerir_admin();
        $this->validarToken();

        $tipo = $this->tipo();
        $id = max(0, (int) ($_POST['id'] ?? 0));

        try {
            $ok = $this->model->eliminar($tipo, $id);

            $_SESSION['mensaje'] = $ok
                ? 'Registro eliminado.'
                : 'No se encontró el registro.';

            $_SESSION['tipo_mensaje'] = $ok ? 'success' : 'warning';

        } catch (Throwable $e) {
            $_SESSION['mensaje'] = 'No se puede eliminar: hay libros asociados o existen registros relacionados.';
            $_SESSION['tipo_mensaje'] = 'danger';
        }

        $this->redirect($tipo);
    }
}
