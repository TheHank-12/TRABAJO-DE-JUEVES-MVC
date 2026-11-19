
<?php
class Libro
{
    private $db;

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function obtenerCategorias()
    {
        return $this->db->query(
            "SELECT * FROM categorias ORDER BY nombre"
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerEditoriales()
    {
        return $this->db->query(
            "SELECT * FROM editoriales ORDER BY nombre"
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerAutores()
    {
        return $this->db->query(
            "SELECT * FROM autores ORDER BY nombre"
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerProveedores()
    {
        return $this->db->query(
            "SELECT id_proveedor, razon_social
             FROM proveedores
             ORDER BY razon_social"
        )->fetch_all(MYSQLI_ASSOC);
    }

    private function consultaLibros($where = '')
    {
        return "SELECT
                    l.*,
                    c.nombre AS categoria,
                    a.nombre AS autor,
                    a.nacionalidad AS autor_nacionalidad,
                    e.nombre AS editorial,
                    p.razon_social AS proveedor,
                    COALESCE(AVG(r.puntuacion), 0) AS promedio_estrellas,
                    COUNT(DISTINCT r.id_resena) AS total_reseñas
                FROM libros l
                INNER JOIN categorias c
                    ON c.id_categoria = l.id_categoria
                INNER JOIN autores a
                    ON a.id_autor = l.id_autor
                INNER JOIN editoriales e
                    ON e.id_editorial = l.id_editorial
                INNER JOIN proveedores p
                    ON p.id_proveedor = l.id_proveedor
                LEFT JOIN resenas r
                    ON r.id_libro = l.id_libro
                $where";
    }

    public function obtenerMejorValorados()
    {
        $sql = $this->consultaLibros(
            "WHERE l.estado = 'DISPONIBLE'
             AND l.stock > 0
             GROUP BY l.id_libro
             HAVING promedio_estrellas > 0
             ORDER BY promedio_estrellas DESC,
                      total_reseñas DESC
             LIMIT 4"
        );

        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    private function filtros(
        &$sql,
        &$types,
        &$params,
        $categoria,
        $editorial,
        $busqueda,
        $disponibles
    ) {
        $sql .= $disponibles
            ? " WHERE l.estado = 'DISPONIBLE' AND l.stock > 0"
            : " WHERE 1=1";

        if ($categoria > 0) {
            $sql .= " AND l.id_categoria = ?";
            $types .= 'i';
            $params[] = $categoria;
        }

        if ($editorial > 0) {
            $sql .= " AND l.id_editorial = ?";
            $types .= 'i';
            $params[] = $editorial;
        }

        if ($busqueda !== '') {
            $sql .= " AND (
                l.titulo LIKE ?
                OR a.nombre LIKE ?
                OR l.isbn LIKE ?
            )";

            $like = '%' . $busqueda . '%';

            $types .= 'sss';
            array_push($params, $like, $like, $like);
        }
    }

    public function contarCatalogo(
        $categoria,
        $editorial,
        $busqueda,
        $disponibles = true
    ) {
        $sql = "SELECT COUNT(*) AS total
                FROM libros l
                INNER JOIN autores a
                    ON a.id_autor = l.id_autor";

        $types = '';
        $params = [];

        $this->filtros(
            $sql,
            $types,
            $params,
            $categoria,
            $editorial,
            $busqueda,
            $disponibles
        );

        $s = $this->db->prepare($sql);

        if ($params) {
            $s->bind_param($types, ...$params);
        }

        $s->execute();

        $n = (int) $s->get_result()->fetch_assoc()['total'];

        $s->close();

        return $n;
    }

    public function obtenerCatalogo(
        $categoria,
        $editorial,
        $orden,
        $busqueda,
        $limite = 12,
        $offset = 0,
        $disponibles = true
    ) {
        $ordenes = [
            'precio_asc' => 'l.precio_venta ASC',
            'precio_desc' => 'l.precio_venta DESC',
            'rating' => 'promedio_estrellas DESC, total_reseñas DESC',
            'titulo_asc' => 'l.titulo ASC',
            'recientes' => 'l.id_libro DESC'
        ];

        $order = $ordenes[$orden] ?? $ordenes['recientes'];

        $sql = $this->consultaLibros();

        $types = '';
        $params = [];

        $this->filtros(
            $sql,
            $types,
            $params,
            $categoria,
            $editorial,
            $busqueda,
            $disponibles
        );

        $sql .= " GROUP BY l.id_libro
                  ORDER BY $order
                  LIMIT ? OFFSET ?";

        $types .= 'ii';

        $params[] = (int) $limite;
        $params[] = (int) $offset;

        $s = $this->db->prepare($sql);

        $s->bind_param($types, ...$params);

        $s->execute();

        $rows = $s->get_result()->fetch_all(MYSQLI_ASSOC);

        $s->close();

        return $rows;
    }

    public function listarAdministracion($busqueda = '')
    {
        $sql = $this->consultaLibros();

        $types = '';
        $params = [];

        if ($busqueda !== '') {
            $sql .= " WHERE
                        l.titulo LIKE ?
                        OR l.isbn LIKE ?
                        OR a.nombre LIKE ?
                        OR c.nombre LIKE ?";

            $like = '%' . $busqueda . '%';

            $types = 'ssss';

            $params = [
                $like,
                $like,
                $like,
                $like
            ];
        }

        $sql .= " GROUP BY l.id_libro
                  ORDER BY l.id_libro DESC";

        $s = $this->db->prepare($sql);

        if ($params) {
            $s->bind_param($types, ...$params);
        }

        $s->execute();

        $r = $s->get_result()->fetch_all(MYSQLI_ASSOC);

        $s->close();

        return $r;
    }

    public function obtenerPorId($id)
    {
        $sql = $this->consultaLibros(
            "WHERE l.id_libro = ?
             GROUP BY l.id_libro"
        );

        $s = $this->db->prepare($sql);

        $s->bind_param('i', $id);

        $s->execute();

        $r = $s->get_result()->fetch_assoc();

        $s->close();

        return $r ?: null;
    }

    public function guardarResena(
        $idLibro,
        $idUsuario,
        $puntuacion,
        $comentario
    ) {
        $comentario = trim((string) $comentario);

        $puntuacion = max(1, min(5, (int) $puntuacion));

        if ($comentario === '') {
            throw new InvalidArgumentException(
                'Escriba un comentario antes de publicar.'
            );
        }

        $s = $this->db->prepare(
            "INSERT INTO resenas
                (id_libro, id_usuario, puntuacion, comentario)
             VALUES (?, ?, ?, ?)"
        );

        $s->bind_param(
            'iiis',
            $idLibro,
            $idUsuario,
            $puntuacion,
            $comentario
        );

        $s->execute();

        $s->close();
    }

    public function obtenerResenas($id)
    {
        $s = $this->db->prepare(
            "SELECT r.*, u.nombre, u.apellido
             FROM resenas r
             INNER JOIN usuarios u
                ON u.id_usuario = r.id_usuario
             WHERE r.id_libro = ?
             ORDER BY r.fecha DESC"
        );

        $s->bind_param('i', $id);

        $s->execute();

        $r = $s->get_result()->fetch_all(MYSQLI_ASSOC);

        $s->close();

        return $r;
    }

    public function guardar($d, $id = 0)
    {
        $campos = [
            'id_categoria',
            'id_autor',
            'id_editorial',
            'id_proveedor'
        ];

        $ids = [];

        foreach ($campos as $f) {
            $ids[] = (int) ($d[$f] ?? 0);

            if (end($ids) < 1) {
                throw new InvalidArgumentException(
                    'Seleccione categoría, autor, editorial y proveedor.'
                );
            }
        }

        $titulo = trim((string) ($d['titulo'] ?? ''));
        $isbn = trim((string) ($d['isbn'] ?? ''));

        $venta = (float) ($d['precio_venta'] ?? 0);
        $alquiler = (float) ($d['precio_alquiler'] ?? 0);
        $stock = (int) ($d['stock'] ?? 0);

        $fecha = trim((string) ($d['fecha_publicacion'] ?? ''));

        $estado = (string) ($d['estado'] ?? 'DISPONIBLE');

        $portada = (string) (
            $d['portada'] ?? 'uploads/portadas/no-cover.png.jpg'
        );

        if ($titulo === '' || $isbn === '') {
            throw new InvalidArgumentException(
                'El título y el ISBN son obligatorios.'
            );
        }

        if ($venta < 0 || $alquiler < 0 || $stock < 0) {
            throw new InvalidArgumentException(
                'Los precios y el stock no pueden ser negativos.'
            );
        }

        if (!in_array(
            $estado,
            ['DISPONIBLE', 'AGOTADO', 'DESCONTINUADO'],
            true
        )) {
            throw new InvalidArgumentException('Estado no válido.');
        }

        $s = $this->db->prepare(
            "SELECT id_libro
             FROM libros
             WHERE isbn = ?
             AND id_libro <> ?
             LIMIT 1"
        );

        $s->bind_param('si', $isbn, $id);

        $s->execute();

        $dup = $s->get_result()->fetch_assoc();

        $s->close();

        if ($dup) {
            throw new InvalidArgumentException(
                'El ISBN ya está registrado para otro libro.'
            );
        }

        $fechaSql = $fecha !== '' ? $fecha : null;

        $this->db->begin_transaction();

        try {
            if ($id > 0) {
                $old = $this->obtenerPorId($id);

                if (!$old) {
                    throw new RuntimeException('Libro no encontrado.');
                }

                $sql = "UPDATE libros SET
                            id_categoria = ?,
                            id_autor = ?,
                            id_editorial = ?,
                            id_proveedor = ?,
                            titulo = ?,
                            isbn = ?,
                            precio_venta = ?,
                            precio_alquiler = ?,
                            stock = ?,
                            portada = ?,
                            fecha_publicacion = ?,
                            estado = ?
                        WHERE id_libro = ?";

                $s = $this->db->prepare($sql);

                $s->bind_param(
                    'iiiissddisssi',
                    $ids[0],
                    $ids[1],
                    $ids[2],
                    $ids[3],
                    $titulo,
                    $isbn,
                    $venta,
                    $alquiler,
                    $stock,
                    $portada,
                    $fechaSql,
                    $estado,
                    $id
                );

                $s->execute();
                $s->close();

                $newId = $id;
                $anterior = (int) $old['stock'];

            } else {
                $sql = "INSERT INTO libros (
                            id_categoria,
                            id_autor,
                            id_editorial,
                            id_proveedor,
                            titulo,
                            isbn,
                            precio_venta,
                            precio_alquiler,
                            stock,
                            portada,
                            fecha_publicacion,
                            estado
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $s = $this->db->prepare($sql);

                $s->bind_param(
                    'iiiissddisss',
                    $ids[0],
                    $ids[1],
                    $ids[2],
                    $ids[3],
                    $titulo,
                    $isbn,
                    $venta,
                    $alquiler,
                    $stock,
                    $portada,
                    $fechaSql,
                    $estado
                );

                $s->execute();

                $newId = $s->insert_id;

                $s->close();

                $anterior = 0;
            }

            if ($stock !== $anterior) {
                $dif = $stock - $anterior;

                $tipo = $dif > 0 ? 'ENTRADA' : 'AJUSTE';

                $cant = abs($dif);

                $motivo = $id > 0
                    ? 'Ajuste de stock desde MVC'
                    : 'Carga inicial desde MVC';

                $k = $this->db->prepare(
                    "INSERT INTO kardex (
                        id_libro,
                        tipo_movimiento,
                        cantidad,
                        stock_anterior,
                        stock_actual,
                        motivo
                    ) VALUES (?, ?, ?, ?, ?, ?)"
                );

                $k->bind_param(
                    'isiiis',
                    $newId,
                    $tipo,
                    $cant,
                    $anterior,
                    $stock,
                    $motivo
                );

                $k->execute();

                $k->close();
            }

            $this->db->commit();

            return $newId;

        } catch (Throwable $e) {
            $this->db->rollback();

            throw $e;
        }
    }

    public function eliminar($id)
    {
        $s = $this->db->prepare(
            "SELECT COUNT(*) AS total
             FROM alquileres
             WHERE id_libro = ?
             AND estado IN ('En Alquiler', 'Con Demora')"
        );

        $s->bind_param('i', $id);

        $s->execute();

        $n = (int) $s->get_result()->fetch_assoc()['total'];

        $s->close();

        if ($n > 0) {
            throw new RuntimeException(
                'No se puede eliminar: el libro tiene alquileres activos.'
            );
        }

        $s = $this->db->prepare(
            "DELETE FROM libros WHERE id_libro = ?"
        );

        $s->bind_param('i', $id);

        $s->execute();

        $ok = $s->affected_rows > 0;

        $s->close();

        return $ok;
    }
}
