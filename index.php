<?php

include_once './librerias/config.inc.php';
include_once './librerias/claseBaseDatos.php';

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : null);

switch ($action) {
    case 'probarConexion':
        probarConexion();
        break;
    case 'getRutaVendedor':
        getRutaVendedor();
        break;
    case 'getClientes':
        getClientes();
        break;
    case 'getProductos':
        getProductos();
        break;
    case 'grabarPedidos':
        grabarPedidos();
        break;
    case 'backup':
        backup();
        break;
    case 'getBackups':
        getBackups();
        break;
}

function probarConexion() {
    $base = $_POST['base'];
    $usuario = $_POST['usuario'];
    $clave = $_POST['clave'];

    $objetoBaseDatos = new claseBaseDatos();

    if ($base != _baseDatos || $usuario != _usuario || $clave != _password) {
        echo $objetoBaseDatos->getCadenaJson("Error la autenticacion, revise sus credenciales", true);
        return;
    }

    $objetoBaseDatos->conectarse();

    if ($objetoBaseDatos->getErrorConexionNo()) {
        echo $objetoBaseDatos->getErrorConexionJson();
    } else {
        echo $objetoBaseDatos->getCadenaJson("Conexón Exitosa", true);
        return;
    }
}

function getRutaVendedor() {
    $base = $_POST['base'];
    $usuario = $_POST['usuario'];
    $clave = $_POST['clave'];
    $codigo_vendedor = $_POST['codigo_vendedor'];

    //$codigo_vendedor = '001';

    $objetoBaseDatos = new claseBaseDatos();
    $objetoBaseDatos->conectarse();

    if ($objetoBaseDatos->getErrorConexionNo()) {
        echo $objetoBaseDatos->getErrorConexionJson();
    } else {
        $query = "
            EXEC SP_RUTA_VENDEDOR
            @CODIGO_VENDEDOR = '$codigo_vendedor',
            @OPERACION = 'QV'
        ";

        $result = $objetoBaseDatos->queryJson($query);

        if ($objetoBaseDatos->getError()) {
            echo $objetoBaseDatos->getErrorJson('');
        } else {
            echo $result;
        }
    }
}

function getClientes() {
    $base = $_POST['base'];
    $usuario = $_POST['usuario'];
    $clave = $_POST['clave'];
    $codigo_vendedor = $_POST['codigo_vendedor'];

    //$codigo_vendedor = '001';

    $objetoBaseDatos = new claseBaseDatos();
    $objetoBaseDatos->conectarse();

    if ($objetoBaseDatos->getErrorConexionNo()) {
        echo $objetoBaseDatos->getErrorConexionJson();
    } else {
        $query = "
            EXEC SP_RUTA_VENDEDOR
            @CODIGO_VENDEDOR = '$codigo_vendedor',
            @OPERACION = 'QC'
        ";

        $result = $objetoBaseDatos->queryJson($query);

        if ($objetoBaseDatos->getError()) {
            echo $objetoBaseDatos->getErrorJson('');
        } else {
            echo $result;
        }
    }
}

function getProductos() {
    $base = $_POST['base'];
    $usuario = $_POST['usuario'];
    $clave = $_POST['clave'];
    $codigo_vendedor = $_POST['codigo_vendedor'];

    //$codigo_vendedor = '001';

    $objetoBaseDatos = new claseBaseDatos();
    $objetoBaseDatos->conectarse();

    if ($objetoBaseDatos->getErrorConexionNo()) {
        echo $objetoBaseDatos->getErrorConexionJson();
    } else {
        $query = "
            EXEC SP_RUTA_VENDEDOR            
            @OPERACION = 'QP'
        ";

        $result = $objetoBaseDatos->queryJson($query);

        if ($objetoBaseDatos->getError()) {
            echo $objetoBaseDatos->getErrorJson('');
        } else {
            echo $result;
        }
    }
}

function grabarPedidos() {
    $base = $_POST['base'];
    $usuario = $_POST['usuario'];
    $clave = $_POST['clave'];
    $codigo_ruta = $_POST['codigo_ruta'];
    $nombre_ruta = utf8_decode($_POST['nombre_ruta']);
    $codigo_vendedor = $_POST['codigo_vendedor'];
    $nombre_vendedor = utf8_decode($_POST['nombre_vendedor']);
    $id_pedido_fono = $_POST['id_pedido_fono'];
    $codigo_vendedor = $_POST['codigo_vendedor'];
    $codigo_cliente = $_POST['codigo_cliente'];
    $nombre_cliente = utf8_decode($_POST['nombre_cliente']);
    $observacion = utf8_decode($_POST['observacion']);
    $fecha_pedido = $_POST['fecha_pedido'];
    $subtotal = $_POST['subtotal'];
    $iva = $_POST['iva'];
    $total = $_POST['total'];
    $detalle_pedido = $_POST['detalle_pedido'];

    $observacion = trim(str_replace("'", "''", $observacion));

    $objetoBaseDatos = new claseBaseDatos();
    $objetoBaseDatos->conectarse();

    if ($objetoBaseDatos->getErrorConexionNo()) {
        echo $objetoBaseDatos->getErrorConexionJson();
    } else {
        $error = 'N';
        $objetoBaseDatos->autocommit(false);

        $query = "
            EXEC SP_PEDIDO_CABECERA    
            @id_pedido_fono = '$id_pedido_fono',
            @codigo_ruta = '$codigo_ruta',
            @nombre_ruta = '$nombre_ruta',
            @CODIGO_VENDEDOR = '$codigo_vendedor',
            @nombre_vendedor = '$nombre_vendedor',    
            @codigo_cliente = '$codigo_cliente',
            @nombre_cliente = '$nombre_cliente',
            @observacion = '$observacion', 
            @fecha_pedido = '$fecha_pedido',     
            @subtotal = '$subtotal',
            @iva = '$iva',
            @total = '$total',
            @OPERACION = 'I'
        ";

        $result = $objetoBaseDatos->query($query);

        if ($objetoBaseDatos->getError()) {
            $error = 'S';
        }

        if ($error == 'N') {
            $id = $result[0]['id'];

            $records = json_decode(stripslashes($detalle_pedido));

            $pedido_detalle = 1;
            foreach ($records as $record) {
                $codigo_producto = $record->codigo_producto;

                $query = "
                    EXEC SP_PEDIDO_DETALLE    
                    @id_pedido_cabecera = '$id',
                    @pedido_detalle = '$pedido_detalle',
                    @codigo_producto = '$codigo_producto',
                    @nombre_producto = '$record->nombre_producto',
                    @precio = '$record->precio',  
                    @precio_iva = '$record->precio_iva',    
                    @cantidad = '$record->cantidad',
                    @subtotal = '$record->subtotal',
                    @total = '$record->total',                    
                    @OPERACION = 'I'
                ";

                $result = $objetoBaseDatos->queryJson($query);

                if ($objetoBaseDatos->getError()) {
                    $error = 'S';
                    break;
                }

                $pedido_detalle++;
            }
        }

//        if ($error == 'N') {
//            $query = "
//                EXEC SP_PEDIDO_CABECERA    
//                @id = '$id',
//                @OPERACION = 'UC'
//            ";
//
//            $result = $objetoBaseDatos->queryJson($query);
//
//            if ($objetoBaseDatos->getError()) {
//                $error = 'S';
//            }
//        }

        if ($error == 'S') {
            echo $objetoBaseDatos->getErrorJson($query);
            $objetoBaseDatos->rollback();
        } else {
            $objetoBaseDatos->commit();
            echo $result;
            
            $query = "
                EXEC SP_PEDIDO_CABECERA    
                @id = '$id',
                @OPERACION = 'UC'
            ";            
            
            $result = $objetoBaseDatos->query($query);

            if ($objetoBaseDatos->getError()) {
                $error = 'S';
                $objetoBaseDatos->rollback();
                echo $objetoBaseDatos->getErrorJson($query);
            } else {
                $objetoBaseDatos->commit();
            }
        }

//        if ($objetoBaseDatos->getError()) {
//            echo $objetoBaseDatos->getErrorJson('');
//        } else {
//            echo $result;
//        }
    }
}

function backup() {
    $base = $_POST['base'];
    $usuario = $_POST['usuario'];
    $clave = $_POST['clave'];
    $codigoVendedor = $_POST['codigoVendedor'];
    $data = $_POST['data'];

    $objetoBaseDatos = new claseBaseDatos();
    $objetoBaseDatos->conectarse();

    if ($objetoBaseDatos->getErrorConexionNo()) {
        echo $objetoBaseDatos->getErrorConexionJson();
    } else {
        $hoy = date("Ymd_His");
        $nombreArchivo = $codigoVendedor . '_' . $hoy . '.json';
        $ruta = './upload/' . $nombreArchivo;
        $mensaje = '';

        if (file_exists($ruta)) {
            $mensaje = "El Archivo $nombre_archivo se ha modificado";
        } else {
            $mensaje = "El Archivo $nombre_archivo se ha creado";
        }

        if ($archivo = fopen($ruta, "a")) {
            if (fwrite($archivo, $data)) {
                $mensaje = "Se creado el Backup correctamente";
            } else {
                $mensaje = "Ha habido un problema al crear el archivo";
            }

            fclose($archivo);
        }

        echo $objetoBaseDatos->getCadenaJson($mensaje, true);
        return;
    }
}

function getBackups() {
    $dir = "./upload/";
//$dir = "../../../../images/iconos/";
//$dir = "/images/";
    $backup = array();
    $d = dir($dir);
    while ($name = $d->read()) {
        if (!preg_match('/\.(json)$/', $name)) {
            continue;
        }
        $size = filesize($dir . $name);
        $lastmod = filemtime($dir . $name) * 1000;
        $backup[] = array('name' => $name, 'size' => $size,
            'lastmod' => $lastmod, 'url' => "./upload/" . $name);
    }
    $d->close();
    $o = array('success' => true, 'data' => $backup);
    echo json_encode($o);
}

//$objetoBaseDatos = new claseBaseDatos();
//
//$objetoBaseDatos->conectarse();
//
//if ($objetoBaseDatos->getErrorConexionNo()) {
//    echo $objetoBaseDatos->getErrorConexionJson();
//} else {
//    $query = "
//            select *
//            from tb_ruta_vendedor            
//            ";
//
//    $result = $objetoBaseDatos->queryJson($query);
//
//    if ($objetoBaseDatos->getError()) {       
//        echo $objetoBaseDatos->getErrorJson('');
//    } else {
//        echo $result;
//    }
//}
?>