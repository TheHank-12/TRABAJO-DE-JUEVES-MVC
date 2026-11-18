
<?php

class Libro
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = Database::conectar();
    }

    // Obtener todas las categorías
    public function obtenerCategorias()
    {
        $sql = "SELECT * FROM categorias ORDER BY nombre ASC";
        $resultado = $this->conexion->query($sql);

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener todas las editoriales
    public function obtenerEditoriales()
    {
        $sql = "SELECT * FROM editoriales ORDER BY nombre ASC";
        $resultado = $this->conexion->query($sql);

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener los libros mejor valorados
    public function obtenerMejorValorados()
    {
        $sql = "SELECT l.*, 
                       c.nombre AS categoria,
                       a.nombre AS autor,
                       COALESCE(AVG(r.puntuacion), 0) AS promedio_estrellas,
                       COUNT(r.id_resena) AS total_reseñas
                FROM libros l
                INNER JOIN categorias c
                    ON l.id_categoria = c.id_categoria
                INNER JOIN autores a
                    ON l.id_autor = a.id_autor
                LEFT JOIN resenas r
                    ON l.id_libro = r.id_libro
                WHERE l.estado = 'DISPONIBLE'
                GROUP BY l.id_libro
                HAVING promedio_estrellas > 0
                ORDER BY promedio_estrellas DESC,
                         total_reseñas DESC
                LIMIT 4";

        $resultado = $this->conexion->query($sql);

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener catálogo con filtros y ordenamiento
    public function obtenerCatalogo($categoria, $editorial, $orden, $busqueda)
    {
        // Evitar que el usuario introduzca criterios SQL
        $ordenamientos = [
            'precio_asc'  => 'l.precio_venta ASC',
            'precio_desc' => 'l.precio_venta DESC',
            'rating'      => 'promedio_estrellas DESC',
            'titulo_asc'  => 'l.titulo ASC',
            'recientes'   => 'l.id_libro DESC'
        ];

        $orderBy = $ordenamientos[$orden] ?? $ordenamientos['recientes'];

        $sql = "SELECT l.*,
                       c.nombre AS categoria,
                       a.nombre AS autor,
                       e.nombre AS editorial,
                       COALESCE(AVG(r.puntuacion), 0) AS promedio_estrellas,
                       COUNT(r.id_resena) AS total_reseñas
                FROM libros l
                INNER JOIN categorias c
                    ON l.id_categoria = c.id_categoria
                INNER JOIN autores a
                    ON l.id_autor = a.id_autor
                INNER JOIN editoriales e
                    ON l.id_editorial = e.id_editorial
                LEFT JOIN resenas r
                    ON l.id_libro = r.id_libro
                WHERE l.estado = 'DISPONIBLE'";

        $parametros = [];
        $tipos = "";

        if ($categoria > 0) {
            $sql .= " AND l.id_categoria = ?";
            $tipos .= "i";
            $parametros[] = $categoria;
        }

        if ($editorial > 0) {
            $sql .= " AND l.id_editorial = ?";
            $tipos .= "i";
            $parametros[] = $editorial;
        }

        if ($busqueda !== '') {
            $sql .= " AND (
                        l.titulo LIKE ?
                        OR a.nombre LIKE ?
                        OR l.isbn LIKE ?
                     )";

            $busqueda = "%" . $busqueda . "%";

            $tipos .= "sss";
            $parametros[] = $busqueda;
            $parametros[] = $busqueda;
            $parametros[] = $busqueda;
        }

        $sql .= " GROUP BY l.id_libro ORDER BY $orderBy";

        $stmt = $this->conexion->prepare($sql);

        if (!empty($parametros)) {
            $stmt->bind_param($tipos, ...$parametros);
        }

        $stmt->execute();

        $resultado = $stmt->get_result();

        $libros = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $libros;
    }
}
