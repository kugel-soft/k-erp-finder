<?php

namespace Kugel\Utils;

class KConnection {
    public static function getConnection() {
        $conn = odbc_connect("Driver={Pervasive ODBC Interface};ServerName=192.168.4.59;ServerDSN=Kugel;", "Master", "kugel");
        if (!$conn) {
            return null;
        }
        
        return $conn;
    }
    
    public static function selectLista($query) {
        $conn = KConnection::getConnection();
        if (!$conn) {
            return [];
        }
        
        $result = odbc_exec($conn , $query);
        
        $list = [];
        while ($row = odbc_fetch_array($result)) {
            $list[] = array_map("utf8_encode", $row);
        }
        
        odbc_close($conn);
        
        return $list;
    }
    
    // Tabelas
    // DC0100
    public static function findDC0100ById($codPessoa) {
        $query = "SELECT * FROM DC0100 WHERE COD_PESSOA = '" . $codPessoa . "'";
        $list = KConnection::selectLista($query);
        if (!empty($list)) {
            return $list[0];
        }
        return null;
    }
    
    // DC0190
    public static function findDC0190ById($codModulo) {
        $query = "SELECT * FROM DC0190 WHERE COD_MODULO = '" . $codModulo . "'";
        $list = KConnection::selectLista($query);
        if (!empty($list)) {
            return $list[0];
        }
        return null;
    }
    
    // DC0414
    public static function findDC0414ById($codTipoServ) {
        $query = "SELECT * FROM DC0414 WHERE COD_TIPO_SERV = '" . $codTipoServ . "'";
        $list = KConnection::selectLista($query);
        if (!empty($list)) {
            return $list[0];
        }
        return null;
    }
    
    // DC1531
    public static function findDC1531ById($codCli, $codEmpresa) {
        $query =
            "SELECT *                                 " .
            "FROM DC1531                              " .
            "WHERE COD_CLI = " . $codCli . "          " .
            "   AND COD_EMPRESA = " . $codEmpresa . " ";
            
        $list = KConnection::selectLista($query);
        if (!empty($list)) {
            return $list[0];
        }
        return null;
    }
    
    // DM0659
    public static function findDM0659ById($numOs, $numSeq) {
        $query =
            "SELECT *                         " .
            "FROM DM0659                      " .
            "WHERE NUM_OS = " . $numOs . "    " .
            "   AND NUM_SEQ = " . $numSeq . " ";
            
        $list = KConnection::selectLista($query);
        if (!empty($list)) {
            return $list[0];
        }
        return null;
    }
    
    // DM1658
    public static function findDM1658ById($numOs) {
        $query = "SELECT * FROM DM1658 WHERE NUM_OS = " . $numOs;
        $list = KConnection::selectLista($query);
        if (!empty($list)) {
            return $list[0];
        }
        return null;
    }
}