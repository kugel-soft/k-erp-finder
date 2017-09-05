<?php

namespace Kugel\Utils;

class HoraUtils {
    public static function getIntervaloHoras($horaInic, $horaFim) {
        $horaFimMenor = $horaFim < $horaInic;

        if ($horaFimMenor) {
            return 0;
        }

        $horaTotal = intval($horaFim) - intval($horaInic);
        $minTotal = (($horaFim - intval($horaFim)) * 100) - (($horaInic - intval($horaInic)) * 100);
        
        //echo "horaTotal: [" . $horaTotal . "], minTotal: [" . $minTotal . "]";

        if ($minTotal < 0) {
            $minTotal += 60;
            $horaTotal--;
        }

        if ($minTotal > 59) {
            $minTotal -= 60;
            $horaTotal++;
        }

        return ($horaTotal * 60 + $minTotal);
    }
    
    public static function parseMinutosToHora($minutos) {
        $totalHoras = 0;
        $totalMinutos = $minutos;

        if ($minutos > 59) {
            $totalHoras = intval($minutos / 60);
        }

        if ($totalHoras > 0) {
            $totalMinutos = $minutos - ($totalHoras * 60);
        }

        return $totalHoras + ((float) $totalMinutos / 100);
    }
    
    public static function parseHoraToHorasCentesimais($horaNormal) {
        $valorString = str_pad(number_format($horaNormal, 4, '.', ''), 7, '0', STR_PAD_LEFT);
        //echo "#valorString: [" . $valorString . "]";
        $hora = intval(substr($valorString, 0, 2));
        //echo "#hora: [" . $hora . "]";
        $minuto = intval(substr($valorString, 3, 2 ) );
        //echo "#minuto: [" . $minuto . "]";
        $segundo = intval(substr($valorString, 5) );
        //echo "#segundo: [" . $segundo . "]";
        $total = ($hora * 3600) + ($minuto * 60) + $segundo;
        $horaCentesimal = (double) $total / 3600;
        return number_format($horaCentesimal, 8, '.', '');
    }
    
    public static function calcularTotalHoras($os) {
        $totalCobravel = 0.0;
        $totalNaoCobravel = 0.0;

        foreach ($os->servicos as $serv) {
            if ($serv->FL_COBRAVEL == "S") {
                $totalCobravel += $serv->TOTAL_COBRAVEL;
                $serv->TOTAL_COBRAVEL = number_format($serv->TOTAL_COBRAVEL, 2);
            }
            else {
                $totalNaoCobravel += $serv->TOTAL_NAO_COBRAVEL;
                $serv->TOTAL_NAO_COBRAVEL = number_format($serv->TOTAL_NAO_COBRAVEL, 2);
            }
        }

        $os->HORAS_COBRAVEIS = number_format($totalCobravel, 2);
        $os->HORAS_NAO_COBRAVEIS = number_format($totalNaoCobravel, 2);
    }
}