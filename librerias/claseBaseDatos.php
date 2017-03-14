<?php

//include_once 'conf.inc.php';
error_reporting(0);

class claseBaseDatos {

    private $mssql;
    private $baseDatos;
    private $servidor;
    private $usuario;
    private $password;
    private $numRows;

    //public function conectarse($servidor, $usuario, $password, $baseDatos) {
    public function conectarse() {
        //$this->mysqli = new mysqli($this->servidor, $this->usuario, $this->password, $this->baseDatos);        
//        $this->mysqli = @new mysqli(_servidor, _usuario, _password, _baseDatos);
//        return $this->mysqli;
        $this->servidor = _servidor;
        $this->usuario = _usuario;
        $this->baseDatos = _baseDatos;
        $this->password = _password;

        $this->mssql = odbc_connect("Driver={SQL Server};Server=$this->servidor;Database=$this->baseDatos;", $this->usuario, $this->password);

        return $this->mssql;
    }

    public function conectarseInterfaz() {
        $this->servidor = _servidorI;
        $this->usuario = _usuarioI;
        $this->baseDatos = _baseDatosI;
        $this->password = _passwordI;

        $this->mssql = odbc_connect("Driver={SQL Server};Server=$this->servidor;Database=$this->baseDatos;", $this->usuario, $this->password);

        return $this->mssql;
    }

    public function getMySqli() {
        return $this->mssql;
    }

    public function autocommit($false) {
//        $this->mssql->autocommit(FALSE);
        odbc_autocommit($this->mssql, false);
    }

    public function commit() {
//        $this->mssql->commit();
        odbc_commit($this->mssql);
    }

    public function rollback() {
//        $this->mssql->rollback();
        odbc_rollback($this->mssql);
    }

    public function getErrorConexionNo() {
//        return $this->mysqli->connect_errno;
        return odbc_error();
    }

    public function getErrorConexion() {
        return odbc_error();
    }

    function convertArrayKeysToUtf8(array $array) {
        $convertedArray = array();
        foreach ($array as $key => $value) {
            if (!mb_check_encoding($key, 'UTF-8'))
                $key = utf8_encode($key);
            if (is_array($value))
                $value = $this->convertArrayKeysToUtf8($value);

            $convertedArray[$key] = $value;
        }
        return $convertedArray;
    }

    public function getErrorConexionJson() {                
        $o = array(
            "success" => false,            
            "message" => array("reason" => odbc_error() . ' - ' . utf8_decode(odbc_errormsg()))
        );

        return json_encode($o);
    }

    public function getErrorNo() {
        return odbc_error();
    }

    public function getError() {
        return odbc_error();
    }

    public function getNumRows() {
        return $this->numRows;
    }

    public function getErrorJson($query = 'Se realizo la transaccion correctamente') {
//        $o = array(
//            "success" => false,
//            "message" => odbc_error() . ' - ' . odbc_errormsg() . ' - query: ' . $query
//        );

        $o = array(
            "success" => false,
            "message" => array("reason" => odbc_error() . ' - ' . utf8_decode(odbc_errormsg()) . ' - query: ' . $query,
                "codigo" => 'jpablos')
        );
        return json_encode($o);
    }

    public function fetchArray($result) {
        return mysqli_fetch_assoc($result);
    }

    public function query($query) {
        //odbc_exec($this->mssql, "SET NAMES 'UTF8'");
        //odbc_exec($this->mssql, "SET client_encoding='UTF-8'");         
        $result = odbc_exec($this->mssql, $query);
        $this->numRows = odbc_num_rows($result);        
        if (odbc_error()) {
            return "";
        } else {
            $registros = array();
            while ($row = odbc_fetch_array($result)) {
                $registros[] = array_map('utf8_encode', $row);
                //$registros[] = $row;                
            }

            //$registros = $this->convertArrayKeysToUtf8($registros);            
            return $registros;
        }

        //strpos($haystack, $row)
    }

    public function queryJson($query, $mensaje = 'Se realizo la transaccion correctamente') {
        //$result = mysqli_query($this->mssql, $query);
        //$result = $this->query($query);
        $result = odbc_exec($this->mssql, $query);

        if (odbc_error()) {
            return "";
        } else {
            $registros = array();

            while ($row = odbc_fetch_array($result)) {
                $registros[] = array_map('utf8_encode', $row);
            }

            //$registros = $this->convertArrayKeysToUtf8($registros);

            $o = array(
                "success" => true,
                "total" => count($registros),
                "totalFiltro" => count($registros),
                "data" => $registros,
                "message" => array("reason" => $mensaje)
            );

            return json_encode($o);
        }
    }

    public function queryJsonOnly($query, $mensaje = 'Se realizo la transaccion correctamente') {
        //$result = mysqli_query($this->mssql, $query);
        //$result = $this->query($query);
        $result = odbc_exec($this->mssql, $query);

        if (odbc_error()) {
            return "";
        } else {
            $registros = array();

            while ($row = odbc_fetch_array($result)) {
                $registros[] = array_map('utf8_encode', $row);
            }

            //$registros = $this->convertArrayKeysToUtf8($registros);

            $o = array(
                "data" => $registros
            );

            return json_encode($o);
        }
    }

    public function multiQueryJson($multiQuery) {
        $count = count($multiQuery);

        $o = array();

        for ($i = 0; $i < $count; $i++) {
            $query = $multiQuery[$i];
            echo $query;
            $registros = $this->query($query);

            if (odbc_error()) {
                return "";
            } else {

                $o1[] = array(
                    "query" . $i => array(array(
                            "total" => count($registros),
                            "totalFiltro" => count($registros),
                            "data" => $registros)
                        ));
            }
        }

        $o = array(
            "success" => true,
            "querys" => $o1
        );

        return json_encode($o);
    }

    public function queryPaginacionJson($multiQuery) {
        $count = count($multiQuery);

        for ($i = 0; $i < $count; $i++) {
            $query = $multiQuery[$i];
            $registros = $this->query($query);

            if (odbc_error()) {
                return "";
            } else {
                if ($i == 0) {
                    $totalRegistros = $registros[0]['contador'];
                }
            }
        }

        $o = array(
            "success" => true,
            "total" => $totalRegistros,
            "totalFiltro" => count($registros),
            "data" => $registros
        );

        return json_encode($o);
    }

    public function exec($query, $mensaje = 'Se realizo la transaccion correctamente', $id = 0) {
        //odbc_exec($this->mssql, "SET NAMES 'UTF8'");
        //odbc_exec($this->mssql, "SET client_encoding='UTF-8'");
        $result = odbc_exec($this->mssql, $query);

        $registros = array();

        while ($row = odbc_fetch_array($result)) {
            $registros[] = array_map('utf8_encode', $row);
        }

        //echo $query;
        if (odbc_error()) {
            return "";
        } else {
            $o = array(
                "success" => true,
                "id" => $id,
                "data" => $registros,
                "message" => array("reason" => $mensaje)
            );

            return json_encode($o);
        }
    }

    public function getCadenaJson($mensaje = 'Mensaje Json', $success = true) {
        $o = array(
            "success" => $success,
            "message" => array("reason" => $mensaje)
        );

        return json_encode($o);
    }

    public function execSP($query, $tipo = 'QM', $mensaje = 'Se realizo la transaccion correctamente') {
        $contador = 1;
        $i = 0;
        $registros = array();
        $totalRegistros = 0;

        do {
            if ($tipo == 'QM') {
                
            }
        } while (odbc_next_result($result));

//        if ($this->mssql->multi_query($query)) {
//            echo 'if';
//            do {
//                echo 'if do';
//                if ($result = $this->mssql->store_result()) {
//                    echo '<br>dfd' . $i . 'fdfasd<br>';
//                    if ($tipo == 'QM') {
//                        while ($rows = $this->fetchArray($result)) {
//                            $r[] = $rows;
//                        }
//                        $registros = $r;
//
//                        $o1[] = array(
//                            "query" . $i => array(array(
//                                    "total" => count($registros),
//                                    "totalFiltro" => count($registros),
//                                    "data" => $registros)
//                                ));
//                        $r = array();
//                    } else {
//                        while ($rows = $this->fetchArray($result)) {
//                            $r[] = $rows;
//                        }
//                        if ($contador == 1) {
//                            $totalRegistros = $r[0]['contador'];
//                        } else {
//                            $registros = $r;
//                        }
//
//                        $r = array();
//                    }
//
//                    $result->free();
//                }
//                $contador++;
//                $i++;
//            } while ($this->mssql->more_results() && $this->mssql->next_result());

        if ($tipo == 'QM') {
            $o = array(
                "success" => true,
                "querys" => $o1
            );
        } else {
            $o = array(
                "success" => true,
                "total" => $totalRegistros,
                "totalFiltro" => count($registros),
                "data" => $registros,
                "message" => array("reason" => $mensaje)
            );
        }

        return json_encode($o);
//        }
    }

    public function getFields($baseDatos, $tabla) {
        $query = "select column_name as name
                  from information_schema.columns
                  where table_catalog = '$baseDatos'
                  and table_name = '$tabla'
                  order by ordinal_position";

        $registros = $this->query($query);

        if ($this->mysqli->errno) {
            return "";
        } else {
            return json_encode($registros);
        }
    }

    public function decodeUnicode($str) {
    //    $str = utf8_decode(preg_replace_callback('/u([0-9a-fA-F]{4})/', function ($match) {
    //                        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
    //                    }, $str));

        return $str;
    }
    
}

?>
