<?php

namespace Kugel\Controllers;

use Kugel\Utils\ApontamentoUtil;

class ApontamentosController extends Controller {
    public function viewApontamentos($request, $response) {
        $codFun = $request->getAttribute('codFun');
        $totalCobravel = 0.0;
        $totalNaoCobravel = 0.0;
        $totalGeral = 0.0;
        
        $apontList = [];
        
        if ($codFun != "") {
            $apontList = ApontamentoUtil::getApontamentos($codFun);
            
            if (!empty($apontList)) {
                $apontList = ApontamentoUtil::agruparApontamentos($apontList);
                foreach ($apontList as $os) {
                    $totalCobravel += $os->HORAS_COBRAVEIS;
                    $totalNaoCobravel += $os->HORAS_NAO_COBRAVEIS;
                }
                $totalCobravel = number_format($totalCobravel, 2);
                $totalNaoCobravel = number_format($totalNaoCobravel, 2);
                $totalGeral = number_format($totalCobravel + $totalNaoCobravel, 2);
            }
        }
        
        $url = $this->router->pathFor('apontamentos');
        $funcList = ApontamentoUtil::getFuncionarioList();
            
        return $this->view->render(
            $response,
            'apontamentos.twig',
            compact(
                'url',
                'codFun',
                'funcList',
                'apontList',
                'totalCobravel',
                'totalNaoCobravel',
                'totalGeral'
            )
        );
    }
}