<?php

namespace Kugel\Utils;

class ApontamentoUtil {
    public static function getFuncionarioList() {
        $query = "SELECT COD_FUN, APELIDO FROM DC0073 ORDER BY APELIDO";
        $list = KConnection::selectLista($query);
        return $list;
    }
    
    public static function getApontamentos($codFun) {
        $dataIni = 20170900;
        $dataFim = 20170932;
        $dc0190 = KConnection::findDC0190ById("AK");
        if ($dc0190) {
            $dataIni = $dc0190["DATA_INIC_PER"]-1;
            $dataFim = $dc0190["DATA_FIM_PER"]+1;
        }
        
        $query =
            "SELECT DM0660.NUM_OS                           ".
            "    ,DM0659.NUM_SEQ                            ".
            "    ,DM0659.DESCR_APONT_SERVICO                ".
            "    ,DM0660.DATA                               ".
            "    ,DM0660.HORA_INIC                          ".
            "    ,DM0660.HORA_FIM                           ".
            "    ,DM0658.COD_CLI                            ".
            "    ,DM0658.NOME_SOLIC                         ".
            "    ,DM0658.NOME_PROGRAMA                      ".
            "    ,DM0658.DESCR_SERV_CLI                     ".
            "    ,DC0045.NOME_FANTASIA                      ".
            "    ,DC0414.FL_COBRAVEL                        ".
            "FROM DM0660                                    ".
            "JOIN DM0659 ON (                               ".
            "    DM0659.NUM_OS = DM0660.NUM_OS              ".
            "    AND DM0659.NUM_SEQ = DM0660.NUM_SEQ        ".
            ")                                              ".
            "JOIN DM0658 ON (                               ".
            "    DM0658.NUM_OS = DM0660.NUM_OS              ".
            ")                                              ".
            "JOIN DC0045 ON (                               ".
            "    DC0045.COD_EMPRESA = DM0658.COD_CLI        ".
            ")                                              ".
            "JOIN DC0414 ON (                               ".
            "    DC0414.COD_TIPO_SERV = DM0659.COD_TIPO_SERV".
            ")                                              ".
            "WHERE DATA > " . $dataIni . "                  ".
            "    AND DM0660.DATA < " . $dataFim . "         ".
            "    AND DM0659.COD_FUN = '" . $codFun . "'     ".
            "ORDER BY DATA DESC                             ";
            
        $list = KConnection::selectLista($query);
        return $list;
    }
    
    public static function agruparApontamentos($list) {
        $osMap = array();
        
        foreach ($list as $dm0660) {
            $chave = $dm0660["NUM_OS"];
            
            $linhaRelatorio = null;
            
            if (isset($osMap[$chave])) {
                $linhaRelatorio = $osMap[$chave];
            }
            
            if (!$linhaRelatorio) {
                $linhaRelatorio = new \StdClass;
                $linhaRelatorio->NUM_OS = $dm0660["NUM_OS"];
                $linhaRelatorio->COD_CLI = trim($dm0660["COD_CLI"]);
                $linhaRelatorio->NOME_FANTASIA = trim($dm0660["NOME_FANTASIA"]);
                $linhaRelatorio->NOME_SOLIC = trim($dm0660["NOME_SOLIC"]);
                $linhaRelatorio->NOME_PROGRAMA = trim($dm0660["NOME_PROGRAMA"]);
                $linhaRelatorio->DESCR_SERV_CLI = trim($dm0660["DESCR_SERV_CLI"]);
                $linhaRelatorio->isRateio = intval($dm0660["COD_CLI"]) < 1000;
                
                // Autorizante
                $dm1658 = KConnection::findDM1658ById($linhaRelatorio->NUM_OS);
                if ($dm1658) {
                    $dc0100 = KConnection::findDC0100ById($dm1658["COD_PESSOA"]);
                    if ($dc0100) {
                        $linhaRelatorio->NOME_AUTORIZANTE = $dc0100["NOME_PESSOA"];
                    }
                }
                
                $linhaRelatorio->servicos = array();
                
                $osMap[$chave] = $linhaRelatorio;
            }
            
            // Serviços
            $servico = null;
            
            $keyServico = $dm0660["NUM_OS"] . ";" . $dm0660["NUM_SEQ"];
            
            if (isset($linhaRelatorio->servicos[$keyServico])) {
                $servico = $linhaRelatorio->servicos[$keyServico];
            }
            
            if (!$servico) {
                $dm0659 = KConnection::findDM0659ById($dm0660["NUM_OS"], $dm0660["NUM_SEQ"]);
                
                if ($dm0659) {
                    $servico = new \StdClass;
                    $servico->NUM_SEQ = $dm0659["NUM_SEQ"];
                    $servico->COD_TIPO_SERV = $dm0659["COD_TIPO_SERV"];
                    $servico->DESCR_APONT_SERVICO = $dm0659["DESCR_APONT_SERVICO"];
                    $servico->FL_COBRAVEL = "S";
                    $servico->TOTAL_COBRAVEL = 0.0;
                    $servico->TOTAL_NAO_COBRAVEL = 0.0;
                    
                    $dc0414 = KConnection::findDC0414ById(
                        $dm0659["COD_TIPO_SERV"]
                    );
                    
                    if ($dc0414) {
                        $servico->DESCR_SERV = $dc0414["DESCR_SERV"];
                        $servico->FL_COBRAVEL = $dc0414["FL_COBRAVEL"];
                    }
                    
                    $servico->apontamentos = array();
                    
                    $linhaRelatorio->servicos[$keyServico] = $servico;
                }
            }
            
            // Apontamentos
            $apontamento = new \StdClass;
            $apontamento->DATA = $dm0660["DATA"];
            $apontamento->HORA_INIC = $dm0660["HORA_INIC"];
            $apontamento->HORA_FIM = $dm0660["HORA_FIM"];
            $apontamento->HORAS_COBRAVEIS = 0.0;
            $apontamento->HORAS_NAO_COBRAVEIS = 0.0;
            
            //echo "DATA: " . $apontamento->DATA . "<br>";
            //echo "HORA_INIC: " . $apontamento->HORA_INIC . "<br>";
            //echo "HORA_FIM: " . $apontamento->HORA_FIM . "<br>";
            
            $minutos = HoraUtils::getIntervaloHoras(
                $dm0660["HORA_INIC"],
                $dm0660["HORA_FIM"]
            );
            
            //echo "minutos: " . $minutos . "<br>";
            
            $hora = HoraUtils::parseMinutosToHora($minutos);
            
            //echo "hora: " . $hora . "<br>";
            
            $horasCentesimais = HoraUtils::parseHoraToHorasCentesimais($hora);
            
            //echo "horasCentesimais: " . $horasCentesimais . "<br><br>";

            if ($servico->FL_COBRAVEL == "S") {
                $apontamento->HORAS_COBRAVEIS = $horasCentesimais;
                $servico->TOTAL_COBRAVEL += $apontamento->HORAS_COBRAVEIS;
                //var_dump($apontamento->DATA . " - " . $servico->TOTAL_COBRAVEL . "<br>");
            }
            else {
                $apontamento->HORAS_NAO_COBRAVEIS = $horasCentesimais;
                $servico->TOTAL_NAO_COBRAVEL += $apontamento->HORAS_NAO_COBRAVEIS;
            }

            array_push($servico->apontamentos, $apontamento);
            HoraUtils::calcularTotalHoras($linhaRelatorio);
        }
        
        //die();
        
        return $osMap;
    }
}