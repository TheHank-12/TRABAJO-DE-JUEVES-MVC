
<?php
class Catalogo
{
    private $db;

    private $config = [
        'categorias' => [
            'tabla' => 'categorias',
            'pk' => 'id_categoria',
            'campos' => ['nombre', 'descripcion'],
            'etiquetas' => [
                'nombre' => 'Nombre',
                'descripcion' => 'Descripción'
            ]
        ],

        'autores' => [
            'tabla' => 'autores',
            'pk' => 'id_autor',
            'campos' => ['nombre', 'nacionalidad'],
            'etiquetas' => [
                'nombre' => 'Nombre',
                'nacionalidad' => 'Nacionalidad'
            ]
        ],

        'editoriales' => [
            'tabla' => 'editoriales',
            'pk' => 'id_editorial',
            'campos' => ['nombre', 'contacto'],
            'etiquetas' => [
                'nombre' => 'Nombre',
                'contacto' => 'Contacto / correo'
            ]
        ]
    ];

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function configuracion($tipo)
    {
        return $this->config[$tipo] ?? null;
    }

    public function listar($tipo, $busqueda = '')
    {
        $c = $this->configuracion($tipo);

        if (!$c) {
            return [];
        }

        if ($tipo === 'categorias') {
            $extra = '(SELECT COUNT(*)
                       FROM libros l
                       WHERE l.id_categoria = t.id_categoria)
                       total_libros';

        } elseif ($tipo === 'autores') {
            $extra = '(SELECT COUNT(*)
                       FROM libros l
                       WHERE l.id_autor = t.id_autor)
                       total_libros';

        } else {
            $extra = '(SELECT COUNT(*)
                       FROM libros l
                       WHERE l.id_editorial = t.id_editorial)
                       total_libros';
        }

        $sql = "SELECT t.*, $extra
                FROM {$c['tabla']} t";

        if ($busqueda !== '') {
            $sql .= " WHERE ";

            $parts = [];

            foreach ($c['campos'] as $f) {
                $parts[] = "t.$f LIKE ?";
            }

            $sql .= implode(' OR ', $parts);
        }

        $sql .= " ORDER BY t.{$c['campos'][0]} ASC";

        $stmt = $this->db->prepare($sql);

        if ($busqueda !== '') {
            $like = '%' . $busqueda . '%';

            $types = str_repeat('s', count($c['campos']));

            $values = array_fill(
                0,
                count($c['campos']),
                $like
            );

            $stmt->bind_param($types, ...$values);
        }

        $stmt->execute();

        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $rows;
    }

    public function obtener($tipo, $id)
    {
        $c = $this->configuracion($tipo);

        if (!$c) {
            return null;
        }

        $sql = "SELECT *
                FROM {$c['tabla']}
                WHERE {$c['pk']} = ?";

        $s = $this->db->prepare($sql);

        $s->bind_param('i', $id);

        $s->execute();

        $r = $s->get_result()->fetch_assoc();

        $s->close();

        return $r ?: null;
    }

    public function guardar($tipo, $datos, $id = 0)
    {
        $c = $this->configuracion($tipo);

        if (!$c) {
            throw new InvalidArgumentException(
                'Sección no válida.'
            );
        }

        $vals = [];

        foreach ($c['campos'] as $f) {
            $vals[] = trim((string) ($datos[$f] ?? ''));
        }

        if ($vals[0] === '') {
            throw new InvalidArgumentException(
                'El nombre es obligatorio.'
            );
        }

        if ($id > 0) {
            $sets = implode('=?,', $c['campos']) . '=?';

            $sql = "UPDATE {$c['tabla']}
                    SET $sets
                    WHERE {$c['pk']} = ?";

            $s = $this->db->prepare($sql);

            $types = str_repeat('s', count($vals)) . 'i';

            $vals[] = $id;

            $s->bind_param($types, ...$vals);

        } else {
            $marks = implode(
                ',',
                array_fill(0, count($vals), '?')
            );

            $fields = implode(',', $c['campos']);

            $sql = "INSERT INTO {$c['tabla']}
                    ($fields)
                    VALUES ($marks)";

            $s = $this->db->prepare($sql);

            $types = str_repeat('s', count($vals));

            $s->bind_param($types, ...$vals);
        }

        $s->execute();

        $newId = $id ?: $s->insert_id;

        $s->close();

        return $newId;
    }

    public function eliminar($tipo, $id)
    {
        $c = $this->configuracion($tipo);

        if (!$c) {
            throw new InvalidArgumentException(
                'Sección no válida.'
            );
        }

        $s = $this->db->prepare(
            "DELETE FROM {$c['tabla']}
             WHERE {$c['pk']} = ?"
        );

        $s->bind_param('i', $id);

        $s->execute();

        $affected = $s->affected_rows;

        $s->close();

        return $affected > 0;
    }
}
