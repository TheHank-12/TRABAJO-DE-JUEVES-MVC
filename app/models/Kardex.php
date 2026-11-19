<?php
require_once ROOT_PATH . '/app/models/Database.php';

class Kardex {
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function obtenerLibros() {
        $sql = "SELECT id_libro, titulo, isbn, stock FROM libros ORDER BY titulo ASC";
        $resultado = $this->db->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function listar($filtro_libro = 0, $filtro_tipo = '') {
        $sql = "SELECT k.*, l.titulo AS libro_titulo, l.isbn 
                FROM kardex k 
                INNER JOIN libros l ON k.id_libro = l.id_libro 
                WHERE 1=1";
        
        $tipos = '';
        $parametros = [];

        if ($filtro_libro > 0) {
            $sql .= " AND k.id_libro = ?";
            $tipos .= 'i';
            $parametros[] = $filtro_libro;
        }

        if (!empty($filtro_tipo)) {
            $sql .= " AND k.tipo_movimiento = ?";
            $tipos .= 's';
            $parametros[] = $filtro_tipo;
        }

        $sql .= " ORDER BY k.fecha_movimiento DESC, k.id_kardex DESC";

        $stmt = $this->db->prepare($sql);
        if (!empty($parametros)) {
            $stmt->bind_param($tipos, ...$parametros);
        }
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function registrarMovimiento($id_libro, $tipo_movimiento, $cantidad, $motivo) {
        // 1. Obtener stock actual del libro
        $stmtLib = $this->db->prepare("SELECT stock, titulo FROM libros WHERE id_libro = ? LIMIT 1");
        $stmtLib->bind_param('i', $id_libro);
        $stmtLib->execute();
        $resLib = $stmtLib->get_result();

        if ($resLib->num_rows !== 1) {
            return ['exito' => false, 'mensaje' => 'El libro seleccionado no existe.'];
        }

        $libroData = $resLib->fetch_assoc();
        $stock_previo = (int)$libroData['stock'];

        if (strpos($tipo_movimiento, 'SALIDA') !== false && $cantidad > $stock_previo) {
            return [
                'exito' => false,
                'mensaje' => "No se puede realizar una salida de $cantidad unidades. El stock actual es de sólo $stock_previo unidades."
            ];
        }

        // 2. Calcular nuevo stock
        $stock_nuevo = $stock_previo;
        if ($tipo_movimiento === 'ENTRADA' || $tipo_movimiento === 'DEVOLUCION') {
            $stock_nuevo = $stock_previo + $cantidad;
        } elseif (strpos($tipo_movimiento, 'SALIDA') !== false) {
            $stock_nuevo = $stock_previo - $cantidad;
        } elseif ($tipo_movimiento === 'AJUSTE') {
            $stock_nuevo = $cantidad;
        }

        $estado_libro = $stock_nuevo > 0 ? 'DISPONIBLE' : 'AGOTADO';

        // 3. Ejecutar transacción
        $this->db->begin_transaction();

        try {
            $stmtKardex = $this->db->prepare(
                "INSERT INTO kardex (id_libro, tipo_movimiento, cantidad, stock_anterior, stock_actual, motivo) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmtKardex->bind_param('isiiis', $id_libro, $tipo_movimiento, $cantidad, $stock_previo, $stock_nuevo, $motivo);
            $stmtKardex->execute();

            $stmtStock = $this->db->prepare("UPDATE libros SET stock = ?, estado = ? WHERE id_libro = ?");
            $stmtStock->bind_param('isi', $stock_nuevo, $estado_libro, $id_libro);
            $stmtStock->execute();

            $this->db->commit();
            return [
                'exito' => true,
                'mensaje' => "Movimiento de Kárdex registrado y stock actualizado a $stock_nuevo unidades."
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'exito' => false,
                'mensaje' => 'Error al procesar el movimiento: ' . $e->getMessage()
            ];
        }
    }
}