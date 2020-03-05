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
            'avisosList' => [],
            'noticias' => [],
            'documentos' => [],
        ];
        

        $urlAvisos = 'https://dfe-portal.svrs.rs.gov.br/Mdfe/Avisos';
        $urlNoticias = 'https://dfe-portal.svrs.rs.gov.br/Mdfe/Noticias';
        $urlDocumentos = 'https://dfe-portal.svrs.rs.gov.br/Mdfe/Documentos';

        $domCrawler = HtmlDomParser::file_get_html( $urlAvisos );

        $sectionAvisos = $domCrawler->find('#pagedlistItens')[0];
        if ($sectionAvisos) {
            $articleList = $sectionAvisos->find('article');
            foreach ($articleList as $article) {
                $objeto = array(
                    'titulo' => '',
                    'descricao' => '',
                    'data' => '',
                );

                // Título
                $h2Titulo = $article->find('.conteudo-lista__item__titulo')[0];
                if ($h2Titulo) {
                    $a = $h2Titulo->find('a')[0];
                    if ($a) {
                        $objeto['titulo'] = html_entity_decode($a->innertext);
                    }
                }

                // Descrição
                $pDescricaoList = $article->find('.MsoNormal');
                foreach ($pDescricaoList as $pNormal) {
                    $spanSubList = $pNormal->find('span');
                    if (count($spanSubList) > 0) {
                        foreach ($spanSubList as $spanSub) {
                            $spanIn = $spanSub->find('span')[0];
                            if ($spanIn) {
                                $objeto['descricao'] .= $spanIn->innertext;
                            } else {
                                $objeto['descricao'] .= $spanSub->innertext;
                            }
                        }
                    } else {
                        $objeto['descricao'] .= $pNormal->innertext;
                    }
                }

                if (empty($objeto['descricao'])) {
                    $spanDescricao = $article->find('span')[0];
                    if ($spanDescricao) {
                        $objeto['descricao'] = $spanDescricao->innertext;
                    }
                }

                $objeto['descricao'] = str_replace('<o:p>', '', $objeto['descricao']);
                $objeto['descricao'] = str_replace('</o:p>', '', $objeto['descricao']);
                $objeto['descricao'] = str_replace('&nbsp;', ' ', $objeto['descricao']);

                // Data
                $dataEl = $article->find('.conteudo-lista__item__datahora')[0];
                if ($dataEl) {
                    $objeto['data'] = trim($dataEl->innertext);
                }

                array_push($data['avisosList'], $objeto);
            }
        }

        return $data;
    }
}
