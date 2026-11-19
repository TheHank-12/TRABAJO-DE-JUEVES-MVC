<?php

class Alquiler
{
    private $conexion;
    private const MONTO_DIARIO_MORA = 5.00;

    public function __construct()
    {
        $this->conexion = Database::conectar();
    }

    public function listarAlquileres(string $estado = '', string $busqueda = ''): array
    {
        $sql = "SELECT a.*, u.nombre AS usuario_nombre, u.apellido AS usuario_apellido, u.dni AS usuario_dni,
                       l.titulo AS libro_titulo, l.isbn AS libro_isbn,
                       m.id_multa, m.monto_total AS monto_multa, m.estado AS estado_multa
                FROM alquileres a
                INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
                INNER JOIN libros l ON a.id_libro = l.id_libro
                LEFT JOIN multas m ON a.id_alquiler = m.id_alquiler
                WHERE 1=1";

        $params = [];
        $types = "";

        if ($estado !== '') {
            $sql .= " AND a.estado = ?";
            $params[] = $estado;
            $types .= "s";
        }

        if ($busqueda !== '') {
            $like = "%" . $busqueda . "%";
            $sql .= " AND (u.dni LIKE ? OR u.nombre LIKE ? OR u.apellido LIKE ? OR l.titulo LIKE ?)";
            $params = array_merge($params, [$like, $like, $like, $like]);
            $types .= "ssss";
        }

        $sql .= " ORDER BY a.id_alquiler DESC";

        $stmt = $this->conexion->prepare($sql);
        if (!$stmt) {
            return [];
        }

        if ($types !== "") {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $alquileres = [];
        if ($res) {
            while ($fila = $res->fetch_assoc()) {
                $alquileres[] = $fila;
            }
        }
        $stmt->close();

        return $alquileres;
    }

    public function obtenerDatosFormularioCrear(): array
    {
        $clientes = [];
        $resClientes = $this->conexion->query("SELECT id_usuario, dni, nombre, apellido FROM usuarios WHERE estado = 'ACTIVO' AND id_rol = 2 ORDER BY nombre ASC");
        if ($resClientes) {
            while ($fila = $resClientes->fetch_assoc()) {
                $clientes[] = $fila;
            }
        }

        $libros = [];
        $resLibros = $this->conexion->query("SELECT id_libro, titulo, precio_alquiler, stock, isbn FROM libros WHERE estado = 'DISPONIBLE' AND stock > 0 ORDER BY titulo ASC");
        if ($resLibros) {
            while ($fila = $resLibros->fetch_assoc()) {
                $libros[] = $fila;
            }
        }

        return [
            'clientes' => $clientes,
            'libros'   => $libros
        ];
    }

    public function registrarAlquiler(array $datos): array
    {
        $tipoCliente  = $datos['tipo_cliente'] ?? 'EXISTENTE';
        $idLibro      = (int)($datos['id_libro'] ?? 0);
        $fechaInicio  = trim((string)($datos['fecha_inicio'] ?? ''));
        $diasAlquiler = (int)($datos['dias_alquiler'] ?? 0);
        $metodoPago   = trim((string)($datos['metodo_pago'] ?? 'EFECTIVO'));
        $numOperacion = trim((string)($datos['num_operacion'] ?? ''));

        $this->conexion->begin_transaction();

        try {
            if ($tipoCliente === 'NUEVO') {
                $nuevoDni      = trim((string)($datos['nuevo_dni'] ?? ''));
                $nuevoNombre   = trim((string)($datos['nuevo_nombre'] ?? ''));
                $nuevoApellido = trim((string)($datos['nuevo_apellido'] ?? ''));
                $nuevoTelefono = trim((string)($datos['nuevo_telefono'] ?? ''));

                if ($nuevoDni === '' || $nuevoNombre === '' || $nuevoApellido === '') {
                    throw new Exception("Por favor, complete los datos obligatorios del nuevo cliente (DNI, Nombre y Apellido).");
                }

                $stmtCheck = $this->conexion->prepare("SELECT id_usuario FROM usuarios WHERE dni = ? LIMIT 1");
                $stmtCheck->bind_param("s", $nuevoDni);
                $stmtCheck->execute();
                $resCheck = $stmtCheck->get_result();
                $existente = $resCheck ? $resCheck->fetch_assoc() : null;
                $stmtCheck->close();

                if ($existente) {
                    $idUsuario = (int)$existente['id_usuario'];
                } else {
                    $emailGenerado = $nuevoDni . "@cliente.com";
                    $passHash      = password_hash($nuevoDni, PASSWORD_DEFAULT);

                    $sqlInsCli = "INSERT INTO usuarios (id_rol, dni, nombre, apellido, email, password, telefono, estado)
                                  VALUES (2, ?, ?, ?, ?, ?, ?, 'ACTIVO')";
                    $stmtInsCli = $this->conexion->prepare($sqlInsCli);
                    if (!$stmtInsCli) {
                        throw new Exception("Error al preparar el registro del nuevo cliente.");
                    }
                    $stmtInsCli->bind_param("ssssss", $nuevoDni, $nuevoNombre, $nuevoApellido, $emailGenerado, $passHash, $nuevoTelefono);
                    if (!$stmtInsCli->execute()) {
                        $err = $stmtInsCli->error;
                        $stmtInsCli->close();
                        throw new Exception("Error al registrar al nuevo cliente: " . $err);
                    }
                    $idUsuario = (int)$this->conexion->insert_id;
                    $stmtInsCli->close();
                }
            } else {
                $idUsuario = (int)($datos['id_usuario'] ?? 0);
            }

            if ($idUsuario <= 0 || $idLibro <= 0 || $diasAlquiler <= 0 || $fechaInicio === '') {
                throw new Exception("Por favor, complete todos los campos obligatorios para el alquiler.");
            }

            $sqlMultas = "SELECT COUNT(*) AS total_pendientes
                          FROM multas m
                          INNER JOIN alquileres a ON m.id_alquiler = a.id_alquiler
                          WHERE a.id_usuario = ? AND m.estado = 'Pendiente'";
            $stmtMultas = $this->conexion->prepare($sqlMultas);
            $stmtMultas->bind_param("i", $idUsuario);
            $stmtMultas->execute();
            $resMultas = $stmtMultas->get_result();
            $dataMulta = $resMultas ? $resMultas->fetch_assoc() : ['total_pendientes' => 0];
            $stmtMultas->close();

            if ((int)($dataMulta['total_pendientes'] ?? 0) > 0) {
                throw new Exception("No se puede registrar el alquiler: El cliente seleccionado registra multas pendientes de pago.");
            }

            $stmtLib = $this->conexion->prepare("SELECT stock, precio_alquiler, titulo FROM libros WHERE id_libro = ? LIMIT 1");
            $stmtLib->bind_param("i", $idLibro);
            $stmtLib->execute();
            $resLib = $stmtLib->get_result();
            $libroData = $resLib ? $resLib->fetch_assoc() : null;
            $stmtLib->close();

            if (!$libroData) {
                throw new Exception("El libro seleccionado no es válido.");
            }

            $stockActual = (int)$libroData['stock'];
            if ($stockActual <= 0) {
                throw new Exception("El libro '" . $libroData['titulo'] . "' no cuenta con stock disponible en este momento.");
            }

            $fechaDevEsperada = date('Y-m-d', strtotime($fechaInicio . " + " . $diasAlquiler . " days"));
            $montoAlquiler    = (float)$libroData['precio_alquiler'] * $diasAlquiler;
            $estadoInicial    = 'En Alquiler';

            $sqlInsert = "INSERT INTO alquileres (id_usuario, id_libro, fecha_inicio, fecha_dev_esperada, monto_alquiler, metodo_pago, num_operacion, estado)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtInsert = $this->conexion->prepare($sqlInsert);
            if (!$stmtInsert) {
                throw new Exception("Error al preparar el registro de alquiler.");
            }
            $stmtInsert->bind_param(
                "iissdsss",
                $idUsuario,
                $idLibro,
                $fechaInicio,
                $fechaDevEsperada,
                $montoAlquiler,
                $metodoPago,
                $numOperacion,
                $estadoInicial
            );
            if (!$stmtInsert->execute()) {
                $err = $stmtInsert->error;
                $stmtInsert->close();
                throw new Exception("Error al procesar el registro de alquiler: " . $err);
            }
            $stmtInsert->close();

            $nuevoStock  = $stockActual - 1;
            $estadoLibro = $nuevoStock > 0 ? 'DISPONIBLE' : 'AGOTADO';

            $stmtUpd = $this->conexion->prepare("UPDATE libros SET stock = ?, estado = ? WHERE id_libro = ?");
            $stmtUpd->bind_param("isi", $nuevoStock, $estadoLibro, $idLibro);
            if (!$stmtUpd->execute()) {
                $stmtUpd->close();
                throw new Exception("Error al actualizar el stock del libro.");
            }
            $stmtUpd->close();

            $motivo = "Alquiler registrado por " . $diasAlquiler . " días";
            $sqlKardex = "INSERT INTO kardex (id_libro, tipo_movimiento, cantidad, stock_anterior, stock_actual, motivo)
                          VALUES (?, 'SALIDA_ALQUILER', 1, ?, ?, ?)";
            $stmtKardex = $this->conexion->prepare($sqlKardex);
            $stmtKardex->bind_param("iiis", $idLibro, $stockActual, $nuevoStock, $motivo);
            if (!$stmtKardex->execute()) {
                $stmtKardex->close();
                throw new Exception("Error al registrar movimiento en Kárdex.");
            }
            $stmtKardex->close();

            $this->conexion->commit();

            return [
                'ok'                 => true,
                'fecha_dev_esperada' => $fechaDevEsperada
            ];
        } catch (Exception $e) {
            $this->conexion->rollback();
            return [
                'ok'    => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function obtenerAlquilerParaDevolucion(int $idAlquiler): ?array
    {
        $sql = "SELECT a.*, u.nombre AS usuario_nombre, u.apellido AS usuario_apellido, u.dni AS usuario_dni,
                       l.titulo AS libro_titulo, l.isbn AS libro_isbn, l.id_libro
                FROM alquileres a
                INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
                INNER JOIN libros l ON a.id_libro = l.id_libro
                WHERE a.id_alquiler = ?
                LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param("i", $idAlquiler);
        $stmt->execute();
        $res = $stmt->get_result();
        $alquiler = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$alquiler) {
            return null;
        }

        $fechaHoy = date('Y-m-d');
        $fechaEsperada = $alquiler['fecha_dev_esperada'];
        $diasDemora = 0;
        $totalMulta = 0.00;

        if ($fechaHoy > $fechaEsperada) {
            $diff = strtotime($fechaHoy) - strtotime($fechaEsperada);
            $diasDemora = (int)floor($diff / 86400);
            if ($diasDemora > 0) {
                $totalMulta = $diasDemora * self::MONTO_DIARIO_MORA;
            }
        }

        return [
            'alquiler'         => $alquiler,
            'fecha_hoy'        => $fechaHoy,
            'dias_demora'      => $diasDemora,
            'monto_diario_mora'=> self::MONTO_DIARIO_MORA,
            'total_multa'      => $totalMulta
        ];
    }

    public function procesarDevolucion(int $idAlquiler, string $fechaDevReal, bool $cobrarMultaAhora): array
    {
        $datosPrev = $this->obtenerAlquilerParaDevolucion($idAlquiler);
        if ($datosPrev === null) {
            return ['ok' => false, 'error' => 'Alquiler no encontrado.'];
        }

        $alquiler = $datosPrev['alquiler'];
        $fechaEsperada = $alquiler['fecha_dev_esperada'];
        $idLibro = (int)$alquiler['id_libro'];

        $diasDemoraCalc = 0;
        if ($fechaDevReal > $fechaEsperada) {
            $diff = strtotime($fechaDevReal) - strtotime($fechaEsperada);
            $diasDemoraCalc = (int)floor($diff / 86400);
        }

        $montoMultaFinal = $diasDemoraCalc * self::MONTO_DIARIO_MORA;

        $this->conexion->begin_transaction();

        try {
            $estadoAlq = 'Devuelto';
            $stmtUpdAlq = $this->conexion->prepare("UPDATE alquileres SET fecha_dev_real = ?, estado = ? WHERE id_alquiler = ?");
            $stmtUpdAlq->bind_param("ssi", $fechaDevReal, $estadoAlq, $idAlquiler);
            if (!$stmtUpdAlq->execute()) {
                $stmtUpdAlq->close();
                throw new Exception("Error al actualizar el alquiler.");
            }
            $stmtUpdAlq->close();

            $stmtStk = $this->conexion->prepare("SELECT stock FROM libros WHERE id_libro = ? LIMIT 1");
            $stmtStk->bind_param("i", $idLibro);
            $stmtStk->execute();
            $resStk = $stmtStk->get_result();
            $stkVal = $resStk ? $resStk->fetch_assoc() : null;
            $stmtStk->close();

            $stockAnt = (int)($stkVal['stock'] ?? 0);
            $stockAct = $stockAnt + 1;
            $estadoDisp = 'DISPONIBLE';

            $stmtUpdLib =$this->conexion->prepare("UPDATE libros SET stock = ?, estado = ? WHERE id_libro = ?");
            $stmtUpdLib->bind_param("isi", $stockAct, $estadoDisp,$idLibro);
            if (!$stmtUpdLib->execute()) {$stmtUpdLib->close();
                throw new Exception("Error al actualizar el stock del libro.");
            }
            $stmtUpdLib->close();

            $motivo = "Devolución de alquiler Nº " . $idAlquiler;
            $sqlKardex = "INSERT INTO kardex (id_libro, tipo_movimiento, cantidad, stock_anterior, stock_actual, motivo)
                          VALUES (?, 'DEVOLUCION', 1, ?, ?, ?)";
            $stmtK =$this->conexion->prepare($sqlKardex);$stmtK->bind_param("iiis", $idLibro,$stockAnt, $stockAct,$motivo);
            if (!$stmtK->execute()) {$stmtK->close();
                throw new Exception("Error al registrar devolución en Kárdex.");
            }
            $stmtK->close();

            if ($diasDemoraCalc > 0) {
                $estadoMulta =$cobrarMultaAhora ? 'Pagada' : 'Pendiente';
                $fechaPagoVal =$cobrarMultaAhora ? date('Y-m-d H:i:s') : null;
                $montoDiario = self::MONTO_DIARIO_MORA;

                $sqlMulta = "INSERT INTO multas (id_alquiler, dias_atraso, monto_diario, monto_total, estado, fecha_pago)
                             VALUES (?, ?, ?, ?, ?, ?)";
                $stmtM =$this->conexion->prepare($sqlMulta);$stmtM->bind_param("iiddss", $idAlquiler,$diasDemoraCalc, $montoDiario,$montoMultaFinal, $estadoMulta,$fechaPagoVal);
                if (!$stmtM->execute()) {$stmtM->close();
                    throw new Exception("Error al registrar la multa por mora.");
                }
                $stmtM->close();
            }

            $this->conexion->commit();
            return ['ok' => true];
        } catch (Exception $e) {$this->conexion->rollback();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function listarMultas(): array
    {
        $sql = "SELECT m.*, a.fecha_dev_esperada, a.fecha_dev_real,
                       u.nombre AS usuario_nombre, u.apellido AS usuario_apellido, u.dni AS usuario_dni,
                       l.titulo AS libro_titulo
                FROM multas m
                INNER JOIN alquileres a ON m.id_alquiler = a.id_alquiler
                INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
                INNER JOIN libros l ON a.id_libro = l.id_libro
                ORDER BY m.id_multa DESC";

        $res =$this->conexion->query($sql);$multas = [];
        if ($res) {
            while ($fila =$res->fetch_assoc()) {
                $multas[] =$fila;
            }
        }
        return $multas;
    }

    public function cobrarMulta(int $idMulta): array
    {
        $stmtM =$this->conexion->prepare("SELECT m.*, a.id_alquiler FROM multas m INNER JOIN alquileres a ON m.id_alquiler = a.id_alquiler WHERE m.id_multa = ? LIMIT 1");
        $stmtM->bind_param("i", $idMulta);
        $stmtM->execute();$resM = $stmtM->get_result();$mData = $resM ? $resM->fetch_assoc() : null;
        $stmtM->close();

        if (!$mData) {
            return ['ok' => false, 'error' => 'Multa no encontrada.'];
        }

        $idAlq = (int)$mData['id_alquiler'];$fechaPago = date('Y-m-d H:i:s');

        $this->conexion->begin_transaction();

        try {
            $stmtUpdM =$this->conexion->prepare("UPDATE multas SET estado = 'Pagada', fecha_pago = ? WHERE id_multa = ?");
            $stmtUpdM->bind_param("si", $fechaPago,$idMulta);
            if (!$stmtUpdM->execute()) {$stmtUpdM->close();
                throw new Exception("Error al actualizar la multa.");
            }
            $stmtUpdM->close();

            $stmtUpdA =$this->conexion->prepare("UPDATE alquileres SET estado = 'Devuelto' WHERE id_alquiler = ?");
            $stmtUpdA->bind_param("i", $idAlq);
            if (!$stmtUpdA->execute()) {$stmtUpdA->close();
                throw new Exception("Error al actualizar el estado del alquiler.");
            }
            $stmtUpdA->close();

            $this->conexion->commit();
            return ['ok' => true];
        } catch (Exception $e) {$this->conexion->rollback();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
