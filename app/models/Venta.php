<?php

class Venta
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = Database::conectar();
    }

    public function registrarVentaCarrito(int $idUsuarioCliente, array $carrito, string $metodoPago, string $numOperacion): array
    {
        $this->conexion->begin_transaction();

        try {
            $totalGeneral = 0.00;
            $itemsProcesar = [];

            foreach ($carrito as $idLibro => $itemCarrito) {
                $idLibro  = (int)$idLibro;
                $cantidad = is_array($itemCarrito) ? (int)($itemCarrito['cantidad'] ?? 0) : (int)$itemCarrito;

                if ($idLibro <= 0 || $cantidad <= 0) {
                    continue;
                }

                $stmtLib = $this->conexion->prepare("SELECT id_libro, titulo, precio_venta, stock FROM libros WHERE id_libro = ? LIMIT 1");
                if (!$stmtLib) {
                    throw new Exception("Error al consultar el libro.");
                }
                $stmtLib->bind_param("i", $idLibro);
                $stmtLib->execute();
                $resLib = $stmtLib->get_result();
                $libro = $resLib ? $resLib->fetch_assoc() : null;
                $stmtLib->close();

                if (!$libro) {
                    throw new Exception("Uno de los libros seleccionados ya no existe en el catálogo.");
                }

                $stockActual = (int)$libro['stock'];
                if ($stockActual < $cantidad) {
                    throw new Exception("Stock insuficiente para '" . $libro['titulo'] . "'. Stock disponible: " . $stockActual . " unidades.");
                }

                $precioU = (float)$libro['precio_venta'];
                $subtotalItem = $precioU * $cantidad;
                $totalGeneral += $subtotalItem;

                $itemsProcesar[] = [
                    'id_libro'        => $idLibro,
                    'titulo'          => $libro['titulo'],
                    'precio_unitario' => $precioU,
                    'cantidad'        => $cantidad,
                    'subtotal'        => $subtotalItem,
                    'stock_actual'    => $stockActual
                ];
            }

            if (empty($itemsProcesar)) {
                throw new Exception("No hay productos válidos en el carrito.");
            }

            $subtotalGeneral = round($totalGeneral / 1.18, 2);
            $igvGeneral      = round($totalGeneral - $subtotalGeneral, 2);
            $numComprobante  = 'BOL-' . date('Ymd') . '-' . rand(10000, 99999);

            $sqlVenta = "INSERT INTO ventas (id_usuario, num_comprobante, subtotal, igv, total, metodo_pago, num_operacion, fecha_venta)
                         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmtVenta = $this->conexion->prepare($sqlVenta);
            if (!$stmtVenta) {
                throw new Exception("Error al preparar el registro de la venta.");
            }
            $stmtVenta->bind_param(
                "isdddss",
                $idUsuarioCliente,
                $numComprobante,
                $subtotalGeneral,
                $igvGeneral,
                $totalGeneral,
                $metodoPago,
                $numOperacion
            );
            if (!$stmtVenta->execute()) {
                $errorMsg = $stmtVenta->error;
                $stmtVenta->close();
                throw new Exception("Error al registrar la venta: " . $errorMsg);
            }
            $idVenta = (int)$this->conexion->insert_id;
            $stmtVenta->close();

            $sqlDet = "INSERT INTO ventas_detalle (id_venta, id_libro, cantidad, precio_unitario, subtotal)
                       VALUES (?, ?, ?, ?, ?)";
            $stmtDet = $this->conexion->prepare($sqlDet);

            $sqlUpd = "UPDATE libros SET stock = ?, estado = ? WHERE id_libro = ?";
            $stmtUpd = $this->conexion->prepare($sqlUpd);

            $sqlKardex = "INSERT INTO kardex (id_libro, tipo_movimiento, cantidad, stock_anterior, stock_actual, motivo)
                          VALUES (?, 'SALIDA_VENTA', ?, ?, ?, ?)";
            $stmtKardex = $this->conexion->prepare($sqlKardex);

            foreach ($itemsProcesar as $item) {
                $idLibro = $item['id_libro'];
                $cant    = $item['cantidad'];
                $pu      = $item['precio_unitario'];
                $sub     = $item['subtotal'];
                $stkAnt  = $item['stock_actual'];
                $stkAct  = $stkAnt - $cant;
                $estadoNuevo = $stkAct > 0 ? 'DISPONIBLE' : 'AGOTADO';
                $motivo = "Venta Carrito Web #" . $numComprobante;

                $stmtDet->bind_param("iiidd", $idVenta, $idLibro, $cant, $pu, $sub);
                if (!$stmtDet->execute()) {
                    throw new Exception("Error al guardar detalle de la venta.");
                }

                $stmtUpd->bind_param("isi", $stkAct, $estadoNuevo, $idLibro);
                if (!$stmtUpd->execute()) {
                    throw new Exception("Error al actualizar el inventario.");
                }

                $stmtKardex->bind_param("iiiis", $idLibro, $cant, $stkAnt, $stkAct, $motivo);
                if (!$stmtKardex->execute()) {
                    throw new Exception("Error al registrar movimiento en Kárdex.");
                }
            }

            $stmtDet->close();
            $stmtUpd->close();
            $stmtKardex->close();

            $this->conexion->commit();

            return [
                'ok'              => true,
                'id_venta'        => $idVenta,
                'num_comprobante' => $numComprobante
            ];
        } catch (Exception $e) {
            $this->conexion->rollback();
            return [
                'ok'    => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function listarVentas(string $busqueda = '', ?int $idUsuarioCliente = null): array
    {
        $sql = "SELECT v.*, u.nombre, u.apellido, u.dni, u.email
                FROM ventas v
                INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                WHERE 1=1";

        $params = [];
        $types = "";

        if ($idUsuarioCliente !== null && $idUsuarioCliente > 0) {
            $sql .= " AND v.id_usuario = ?";
            $params[] = $idUsuarioCliente;
            $types .= "i";
        }

        if ($busqueda !== '') {
            $like = "%" . $busqueda . "%";
            $sql .= " AND (v.num_comprobante LIKE ? OR u.dni LIKE ? OR u.nombre LIKE ? OR u.apellido LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $types .= "ssss";
        }

        $sql .= " ORDER BY v.fecha_venta DESC, v.id_venta DESC";

        $stmt = $this->conexion->prepare($sql);
        if (!$stmt) {
            return [];
        }

        if ($types !== "") {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $ventas = [];
        if ($res) {
            while ($fila = $res->fetch_assoc()) {
                $ventas[] = $fila;
            }
        }
        $stmt->close();

        return $ventas;
    }

    public function obtenerDatosFormularioPos(): array
    {
        $libros = [];
        $resLibros =$this->conexion->query("SELECT id_libro, titulo, precio_venta AS precio, stock FROM libros WHERE stock > 0 ORDER BY titulo ASC");
        if ($resLibros) {
            while ($fila =$resLibros->fetch_assoc()) {
                $libros[] =$fila;
            }
        }

        $clientes = [];
        $resClientes =$this->conexion->query("SELECT id_usuario, nombre, apellido, dni FROM usuarios WHERE estado = 'ACTIVO' ORDER BY CASE WHEN dni = '00000000' THEN 0 ELSE 1 END, nombre ASC");
        if ($resClientes) {
            while ($fila =$resClientes->fetch_assoc()) {
                $clientes[] =$fila;
            }
        }

        return [
            'libros'   => $libros,
            'clientes' => $clientes
        ];
    }

    public function registrarVentaPos(array $datos): array
    {
        $tipoCliente   =$datos['tipo_cliente'] ?? 'EXISTENTE';
        $idLibro       = (int)($datos['id_libro'] ?? 0);
        $cantidad      = (int)($datos['cantidad'] ?? 0);
        $metodoPago    = trim((string)($datos['metodo_pago'] ?? 'EFECTIVO'));
        $numOperacion  = trim((string)($datos['num_operacion'] ?? ''));

        if ($idLibro <= 0 ||$cantidad <= 0) {
            return ['ok' => false, 'error' => 'Debe seleccionar un libro y una cantidad válida.'];
        }

        $this->conexion->begin_transaction();

        try {
            if ($tipoCliente === 'NUEVO') {
                $nuevoDni      = trim((string)($datos['nuevo_dni'] ?? ''));
                $nuevoNombre   = trim((string)($datos['nuevo_nombre'] ?? ''));
                $nuevoApellido = trim((string)($datos['nuevo_apellido'] ?? ''));
                $nuevoTelefono = trim((string)($datos['nuevo_telefono'] ?? ''));

                if ($nuevoDni === '' || $nuevoNombre === '' ||$nuevoApellido === '') {
                    throw new Exception("Complete los datos obligatorios del nuevo cliente (DNI, nombres y apellidos).");
                }

                $stmtCheck =$this->conexion->prepare("SELECT id_usuario FROM usuarios WHERE dni = ? LIMIT 1");
                $stmtCheck->bind_param("s", $nuevoDni);
                $stmtCheck->execute();$resCheck = $stmtCheck->get_result();$existente = $resCheck ? $resCheck->fetch_assoc() : null;
                $stmtCheck->close();

                if ($existente) {
                    $idCliente = (int)$existente['id_usuario'];
                } else {
                    $emailGenerado   =$nuevoDni . "@cliente.com";
                    $passwordDefault = password_hash($nuevoDni, PASSWORD_DEFAULT);

                    $sqlInsU = "INSERT INTO usuarios (id_rol, dni, nombre, apellido, email, password, telefono, estado)
                                VALUES (2, ?, ?, ?, ?, ?, ?, 'ACTIVO')";
                    $stmtInsU = $this->conexion->prepare($sqlInsU);
                    if (!$stmtInsU) {
                        throw new Exception("Error al preparar el registro del cliente.");
                    }
                    $stmtInsU->bind_param("ssssss", $nuevoDni,$nuevoNombre, $nuevoApellido,$emailGenerado, $passwordDefault,$nuevoTelefono);
                    if (!$stmtInsU->execute()) {
                        $err =$stmtInsU->error;
                        $stmtInsU->close();
                        throw new Exception("Error al registrar cliente: " . $err);
                    }
                    $idCliente = (int)$this->conexion->insert_id;
                    $stmtInsU->close();
                }
            } else {
                $idCliente = (int)($datos['id_usuario_cliente'] ?? 0);
            }

            if ($idCliente <= 0) {
                throw new Exception("Cliente no válido para registrar la venta.");
            }

            $stmtLib =$this->conexion->prepare("SELECT stock, precio_venta, titulo FROM libros WHERE id_libro = ? LIMIT 1");
            $stmtLib->bind_param("i", $idLibro);
            $stmtLib->execute();$resLib = $stmtLib->get_result();$libro = $resLib ? $resLib->fetch_assoc() : null;
            $stmtLib->close();

            if (!$libro || (int)$libro['stock'] <$cantidad) {
                throw new Exception("Stock insuficiente para realizar la venta.");
            }

            $precioU        = (float)$libro['precio_venta'];$total          = $precioU * $cantidad;
            $subtotal       = round($total / 1.18, 2);
            $igv            = round($total - $subtotal, 2);$numComprobante = 'BOL-' . date('Ymd') . '-' . rand(1000, 9999);

            $sqlV = "INSERT INTO ventas (id_usuario, num_comprobante, subtotal, igv, total, metodo_pago, num_operacion, fecha_venta)
                     VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmtV = $this->conexion->prepare($sqlV);
            if (!$stmtV) {
                throw new Exception("Error al preparar el registro de la venta.");
            }
            $stmtV->bind_param("isdddss", $idCliente, $numComprobante,$subtotal, $igv,$total, $metodoPago,$numOperacion);
            if (!$stmtV->execute()) {
                $err =$stmtV->error;
                $stmtV->close();
                throw new Exception("Error al registrar la venta: " . $err);
            }
            $idVenta = (int)$this->conexion->insert_id;
            $stmtV->close();

            $sqlD = "INSERT INTO ventas_detalle (id_venta, id_libro, cantidad, precio_unitario, subtotal)
                     VALUES (?, ?, ?, ?, ?)";
            $stmtD = $this->conexion->prepare($sqlD);
            $stmtD->bind_param("iiidd", $idVenta, $idLibro,$cantidad, $precioU,$total);
            if (!$stmtD->execute()) {$stmtD->close();
                throw new Exception("Error al registrar detalle de venta.");
            }
            $stmtD->close();

            $stockAnt    = (int)$libro['stock'];$stockAct    = $stockAnt -$cantidad;
            $estadoNuevo =$stockAct > 0 ? 'DISPONIBLE' : 'AGOTADO';

            $sqlUpd = "UPDATE libros SET stock = ?, estado = ? WHERE id_libro = ?";
            $stmtUpd = $this->conexion->prepare($sqlUpd);
            $stmtUpd->bind_param("isi", $stockAct, $estadoNuevo,$idLibro);
            $stmtUpd->execute();$stmtUpd->close();

            $motivo = "Venta Mostrador #" . $numComprobante;
            $sqlK = "INSERT INTO kardex (id_libro, tipo_movimiento, cantidad, stock_anterior, stock_actual, motivo)
                     VALUES (?, 'SALIDA_VENTA', ?, ?, ?, ?)";
            $stmtK = $this->conexion->prepare($sqlK);
            $stmtK->bind_param("iiiis", $idLibro, $cantidad,$stockAnt, $stockAct,$motivo);
            $stmtK->execute();$stmtK->close();

            $this->conexion->commit();

            return [
                'ok'              => true,
                'id_venta'        => $idVenta,
                'num_comprobante' => $numComprobante
            ];
        } catch (Exception $e) {$this->conexion->rollback();
            return [
                'ok'    => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function obtenerComprobantePorId(int $idVenta): ?array
    {
        $sqlV = "SELECT v.*, u.nombre, u.apellido, u.dni, u.email, u.direccion, u.telefono
                 FROM ventas v
                 INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                 WHERE v.id_venta = ?
                 LIMIT 1";
        $stmtV = $this->conexion->prepare($sqlV);
        if (!$stmtV) {
            return null;
        }
        $stmtV->bind_param("i", $idVenta);
        $stmtV->execute();$resV = $stmtV->get_result();$venta = $resV ? $resV->fetch_assoc() : null;
        $stmtV->close();

        if (!$venta) {
            return null;
        }

        $sqlDet = "SELECT d.*, l.titulo, l.isbn
                   FROM ventas_detalle d
                   INNER JOIN libros l ON d.id_libro = l.id_libro
                   WHERE d.id_venta = ?";
        $stmtDet =$this->conexion->prepare($sqlDet);$detalles = [];
        if ($stmtDet) {$stmtDet->bind_param("i", $idVenta);$stmtDet->execute();
            $resDet =$stmtDet->get_result();
            if ($resDet) {
                while ($fila =$resDet->fetch_assoc()) {
                    $detalles[] =$fila;
                }
            }
            $stmtDet->close();
        }

        return [
            'venta'    => $venta,
            'detalles' => $detalles
        ];
    }
}
