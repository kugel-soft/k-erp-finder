<?php

namespace Kugel\Utils;

use KubAT\PhpSimple\HtmlDomParser;

class SefazUtils {
    public static function getConsultaNFe() {
        $data = [
            'contAtivList' => [],
            'contAgendList' => [],
            'informeList' => [],
            'docDiversosList' => [],
            'docNotaTecList' => []
        ];

        $urlPrincipal = 'http://www.nfe.fazenda.gov.br/portal/principal.aspx';
        $urlDocDiversos = 'http://www.nfe.fazenda.gov.br/portal/listaConteudo.aspx?tipoConteudo=Iy/5Qol1YbE=';
        $urlNotasTecnicas = 'http://www.nfe.fazenda.gov.br/portal/listaConteudo.aspx?tipoConteudo=tW+YMyk/50s=';

        $domCrawler = HtmlDomParser::file_get_html( $urlPrincipal );

        $divContingencia = $domCrawler->find('#divContingencia')[0];
        if ($divContingencia) {
            // Primeira tabela, ativada
            $table = $divContingencia->find('table')[0];
            if ($table) {
                $trList = $table->find('tr');
                if (count($trList) > 0) {
                    foreach ($trList as $row) {
                        $as = $row->find('a'); 
                        if (count($as) == 0) {
                            continue;
                        }

                        $siglaUf = $row->find('a')[0]->innertext;

                        $periodo = '';
                        foreach ($row->find('span') as $span) {
                            $periodo .= utf8_encode($span->innertext);
                        }
                        array_push($data['contAtivList'], $siglaUf . ' - ' . $periodo);
                    }
                }
            }

            // Segunda tabela, agendada
            $table = $divContingencia->find('table')[1];
            if ($table) {
                $trList = $table->find('tr');
                if (count($trList) > 0) {
                    foreach ($trList as $row) {
                        $as = $row->find('a'); 
                        if (count($as) == 0) {
                            continue;
                        }

                        $siglaUf = $row->find('a')[0]->innertext;

                        $periodo = '';
                        foreach ($row->find('span') as $span) {
                            $periodo .= utf8_encode($span->innertext);
                        }
                        array_push($data['contAgendList'], $siglaUf . ' - ' . $periodo);
                    }
                }
            }
        }

        $baseURL = 'http://www.nfe.fazenda.gov.br/portal/';
        $divInformes = $domCrawler->find('#divInformes')[0];
        if ($divInformes) {
            $table = $divInformes->find('table')[0];
            if ($table) {
                $trList = $table->find('tr');
                if (count($trList) > 0) {
                    foreach ($trList as $row) {
                        $dataInforme = $row->find('a')[0]->innertext;
                        $urlInforme = $row->find('a')[0]->getAttribute('href');
                        $tituloInforme = utf8_encode($row->find('a')[1]->innertext);

                        $item = array(
                            'texto' => 'Em ' . $dataInforme . ',  ' . $tituloInforme,
                            'endereco' => $baseURL . $urlInforme
                        );

                        array_push($data['informeList'], $item);
                    }
                }
            }
        }

        $domCrawler = HtmlDomParser::file_get_html( $urlDocDiversos );

        /*
        *
        * Documentos Diversos
        *
        */
        $conteudoDinamico = $domCrawler->find('#conteudoDinamico')[0];
        if ($conteudoDinamico) {
            $indentacaoConteudo = $conteudoDinamico->find('.indentacaoConteudo')[0];
            if ($indentacaoConteudo) {
                foreach ($indentacaoConteudo->find('p') as $p) {
                    $text = utf8_encode($p->find('span')[0]->innertext);
                    array_push($data['docDiversosList'], $text);
                }
            }
        }

        $domCrawler = HtmlDomParser::file_get_html( $urlNotasTecnicas );

        /*
        *
        * Notas Técnicas
        */
        $conteudoDinamico = $domCrawler->find('#conteudoDinamico')[0];
        if ($conteudoDinamico) {
            $indentacaoConteudo = $conteudoDinamico->find('.indentacaoConteudo')[0];
            if ($indentacaoConteudo) {
                foreach ($indentacaoConteudo->find('.tituloSessao') as $pAno) {
                    $ano = $pAno->innertext;

                    $divIdentNormal = $indentacaoConteudo->find('.indentacaoNormal')[0];
                    if ($divIdentNormal) {
                        foreach ($divIdentNormal->find('p') as $p) {
                            $txt = utf8_encode($p->find('span')[0]->innertext);

                            $contTmp = str_replace('<br />', '', $p->innertext);
                            $contTmp = utf8_encode($contTmp);
                            while ( strpos($contTmp, '>') !== FALSE ) {
                                $contTmp = trim(substr($contTmp, strpos($contTmp, '>')+1));
                                //echo 'while: [' . $contTmp . ']<br><br>';
                            }
                            $cont = $contTmp;
                            array_push($data['docNotaTecList'], $ano . ' - ' . $txt . ' - ' .$cont);
                        }
                    }

                    break;
                }
            }
        }

        return $data;
    }

    public static function getConsultaMDFe() {
        $data = [
            'avisos' => [],
            'noticias' => []
        ];

        $url = 'https://mdfe-portal.sefaz.rs.gov.br';

        $client = new Client();
        $response = $client->request('GET', $url, ['verify' => false]);
        $html = (string) $response->getBody();
        $domCrawler = (new Dom)->load($html);

        /*
        * Avisos
        *
        *<!-- Avisos -->
        *<div class="colNoticias colAvisos borda_aviso">
        *    <h2>Avisos</h2><br/>
        *    <p style="text-align: justify;">
        *        <span class="dataNoticia"> 13/09/2018 </span>
        *        <a href="/Site/Noticias"> Ativação das regras de verificação do RNTRC </a>
        *    </p>
        *    <p style="text-align: justify;">
        *        <span class="dataNoticia"> 06/09/2018 </span>
        *        <a href="/Site/Noticias"> Aviso: Emissor Gratuito </a>
        *    </p>
        *    <p style="text-align: justify;">
        *        <span class="dataNoticia"> 18/05/2018 </span>
        *        <a href="/Site/Noticias"> Comunicado importante: Encerramento do Fisco implantado </a>                    
        *    </p>
        *    <p style="text-align: justify;">
        *        <span class="dataNoticia"> 08/02/2018 </span>
        *        <a href="/Site/Noticias"> Manutenção Preventiva da SVRS </a>
        *    </p>
        *    <p style="text-align: justify;">
        *        <span class="dataNoticia"> 24/10/2017 </span>
        *        <a href="/Site/Noticias"> ATENÇÃO: Atualização dos certificados digitais dos ambientes do RS e SVRS de Documentos Fiscais Eletrônicos (NF-e, NFC-e, CT-e, MDF-e, BP-e): </a>
        *    </p>
        *</div>
        */
        $divAvisos = $domCrawler->find('.colAvisos')[0];
        if ($divAvisos) {
            $pList = $divAvisos->find('p');
            foreach ($pList as $p) {
                $a = $p->find('a')[0];
                $span = $p->find('span')[0];
                if ($a && $span) {
                    $titulo = trim($a->innertext);
                    $data = trim($span->innertext);
                    $urlAviso = 'https://mdfe-portal.sefaz.rs.gov.br/Site/Noticias';

                    $item = array(
                        'titulo' => $titulo,
                        'data' => $data,
                        'urlAviso' => $urlAviso
                    );

                    array_push($data['avisos'], $item);
                }
            }
        }

        return $data;
    }
}
