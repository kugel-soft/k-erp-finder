<?php

namespace Kugel\Utils;

use PHPHtmlParser\Dom;
use GuzzleHttp\Client;

class SefazUtils {
    public static function getConsultaNFe() {
        $data = [
            'contAtivList' => [],
            'contAgendList' => [],
            'informeList' => [],
            'docDiversosList' => [],
            'docNotaTecList' => []
        ];

        $urlPrincipal = 'https://www.nfe.fazenda.gov.br/portal/principal.aspx';
        $urlDocDiversos = 'https://www.nfe.fazenda.gov.br/portal/listaConteudo.aspx?tipoConteudo=Iy/5Qol1YbE=';
        $urlNotasTecnicas = 'https://www.nfe.fazenda.gov.br/portal/listaConteudo.aspx?tipoConteudo=tW+YMyk/50s=';

        $client = new Client();
        $response = $client->request('GET', $urlPrincipal, ['verify' => false]);
        $html = (string) $response->getBody();
        $domCrawler = (new Dom)->load($html);

        /*
        * Contingência ativada e agendada:
        *
        *<div id="divContingencia">
        *    <div>
        *        <table cellspacing="0" rules="all" border="1" id="ctl00_ContentPlaceHolder1_gdvCtgAtiva" style="border-collapse:collapse;">
        *            <caption>Contingência Ativada</caption>
        *            <tr>
        *                <th scope="col">&nbsp;</th>
        *            </tr>
        *            <tr>
        *                <td>
        *                    <a id="ctl00_ContentPlaceHolder1_gdvCtgAtiva_ctl02_hlkSefazAtiva" href="informe.aspx?ehCTG=true&amp;UF=MG">MG</a>
        *                    <br />
        *                    <span id="ctl00_ContentPlaceHolder1_gdvCtgAtiva_ctl02_lblDe">De </span>
        *                    <span id="ctl00_ContentPlaceHolder1_gdvCtgAtiva_ctl02_lblInicioCtgAtiva">03/10/2016 09:22:19</span>
        *                    <span id="ctl00_ContentPlaceHolder1_gdvCtgAtiva_ctl02_lblAte"> até </span>
        *                    <span id="ctl00_ContentPlaceHolder1_gdvCtgAtiva_ctl02_lblFimCtgAtiva">02/10/2017 09:00:00</span>
        *                </td>
        *            </tr>
        *        </table>
        *    </div>
        *    <div>
        *        <table cellspacing="0" rules="all" border="1" id="ctl00_ContentPlaceHolder1_gdvCtgAgendada" style="border-collapse:collapse;">
        *            <caption>Contingência Agendada</caption>
        *            <tr>
        *                <td>Não há agendamentos para o serviço de contingência.</td>
        *            </tr>
        *        </table>
        *    </div>
        *</div>
        */
        $divContingencia = $domCrawler->find('#divContingencia')[0];
        if ($divContingencia) {
            // Primeira tabela, ativada
            $table = $divContingencia->find('table')[0];
            if ($table) {
                $trList = $table->find('tr');
                if (count($trList) > 1) {
                    foreach ($trList as $row) {
                        if (!$row->find('a')[0]) {
                            continue;
                        }

                        $siglaUf = $row->find('a')[0]->text;

                        $periodo = '';
                        foreach ($row->find('span') as $span) {
                            $periodo .= $span->text;
                        }
                        //echo '[siglaUf=' . $siglaUf . '], [periodo=' . utf8_encode($periodo) . ']';
                        array_push($data['contAtivList'], $siglaUf . ' - ' . utf8_encode($periodo));
                    }
                }
            }

            // Segunda tabela, agendada
            $table = $divContingencia->find('table')[1];
            if ($table) {
                $trList = $table->find('tr');
                if (count($trList) > 1) {
                    foreach ($trList as $row) {
                        if (!$row->find('a')[0]) {
                            continue;
                        }

                        $siglaUf = $row->find('a')[0]->text;

                        $periodo = '';
                        foreach ($row->find('span') as $span) {
                            $periodo .= $span->text;
                        }
                        //echo '[siglaUf=' . $siglaUf . '], [periodo=' . utf8_encode($periodo) . ']';
                        array_push($data['contAgendList'], $siglaUf . ' - ' . utf8_encode($periodo));
                    }
                }
            }
        }

        /*
        *
        * Informes:
        *
        *<div id="divInformes">
        *    <div>
        *        <table cellspacing="0" rules="all" border="1" id="ctl00_ContentPlaceHolder1_gdvInformes" style="border-collapse:collapse;">
        *            <tr>
        *                <td>
        *                    <a id="ctl00_ContentPlaceHolder1_gdvInformes_ctl02_hlkDataInforme" href="informe.aspx?ehCTG=false#458">07/07/2017</a>
        *                    <a id="ctl00_ContentPlaceHolder1_gdvInformes_ctl02_hlkTituloInforme" class="linkInformes" href="informe.aspx?ehCTG=false#458">07/07/2017 - Atualizada a Tabela NCM e respectiva Utrib (comércio exterior), com inclusões e exclusões de NCM</a>
        *                    <a id="ctl00_ContentPlaceHolder1_gdvInformes_ctl02_hlkLeiaMais" href="informe.aspx?ehCTG=false#458">...(Leia mais)</a>
        *                </td>
        *            </tr>
        *            <tr>
        *                <td>
        *                    <a id="ctl00_ContentPlaceHolder1_gdvInformes_ctl03_hlkDataInforme" href="informe.aspx?ehCTG=false#457">05/07/2017</a>
        *                    <a id="ctl00_ContentPlaceHolder1_gdvInformes_ctl03_hlkTituloInforme" class="linkInformes" href="informe.aspx?ehCTG=false#457">ATENÇÃO: Exigência do CEST</a>
        *                    <a id="ctl00_ContentPlaceHolder1_gdvInformes_ctl03_hlkLeiaMais" href="informe.aspx?ehCTG=false#457">...(Leia mais)</a>
        *                </td>
        *            </tr>
        *            <tr>
        *                <td>
        *                    <a id="ctl00_ContentPlaceHolder1_gdvInformes_ctl04_hlkDataInforme" href="informe.aspx?ehCTG=false#455">23/06/2017</a>
        *                    <a id="ctl00_ContentPlaceHolder1_gdvInformes_ctl04_hlkTituloInforme" class="linkInformes" href="informe.aspx?ehCTG=false#455">Atenção: Publicada nova versão da NT 2015.003 (versão 1.94)</a>
        *                    <a id="ctl00_ContentPlaceHolder1_gdvInformes_ctl04_hlkLeiaMais" href="informe.aspx?ehCTG=false#455">...(Leia mais)</a>
        *                </td>
        *            </tr>
        *            <tr>
        *                <td>
        *                    <a id="ctl00_ContentPlaceHolder1_gdvInformes_ctl05_hlkDataInforme" href="informe.aspx?ehCTG=false#454">29/05/2017</a>
        *                    <a id="ctl00_ContentPlaceHolder1_gdvInformes_ctl05_hlkTituloInforme" class="linkInformes" href="informe.aspx?ehCTG=false#454">Atenção: Novas versões da NT 2014.002, v1.02b e v1.01c.</a>
        *                    <a id="ctl00_ContentPlaceHolder1_gdvInformes_ctl05_hlkLeiaMais" href="informe.aspx?ehCTG=false#454">...(Leia mais)</a>
        *                </td>
        *            </tr>
        *            <tr>
        *                <td>
        *                    <a id="ctl00_ContentPlaceHolder1_gdvInformes_ctl06_hlkDataInforme" href="informe.aspx?ehCTG=false#453">29/05/2017</a>
        *                    <a id="ctl00_ContentPlaceHolder1_gdvInformes_ctl06_hlkTituloInforme" class="linkInformes" href="informe.aspx?ehCTG=false#453">Atenção: Publicada nova versão da NT 2016.002 (versão 1.20)</a>
        *                    <a id="ctl00_ContentPlaceHolder1_gdvInformes_ctl06_hlkLeiaMais" href="informe.aspx?ehCTG=false#453">...(Leia mais)</a>
        *                </td>
        *            </tr>
        *        </table>
        *    </div>
        *</div>
        */
        $baseURL = 'https://www.nfe.fazenda.gov.br/portal/';
        $divInformes = $domCrawler->find('#divInformes')[0];
        if ($divInformes) {
            $table = $divInformes->find('table')[0];
            if ($table) {
                $trList = $table->find('tr');
                if (count($trList) > 0) {
                    foreach ($trList as $row) {
                        $dataInforme = $row->find('a')[0]->text;
                        $urlInforme = $row->find('a')[0]->getAttribute('href');
                        $tituloInforme = utf8_encode($row->find('a')[1]->text);

                        $item = array(
                            'texto' => 'Em ' . $dataInforme . ',  ' . $tituloInforme,
                            'endereco' => $baseURL . $urlInforme
                        );

                        array_push($data['informeList'], $item);
                    }
                }
            }
        }

        $response = $client->request('GET', $urlDocDiversos, ['verify' => false]);
        $html = (string) $response->getBody();
        $domCrawler = (new Dom)->load($html);

        /*
        *
        * Documentos Diversos
        *
        *<div id="conteudoDinamico">
        *    <?xml version="1.0" encoding="iso-8859-15"?>
        *    <html xmlns="http://www.w3.org/1999/xhtml">
        *    <head>
        *        <title></title>
        *        <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
        *    </head>
        *    <body>
        *        <div class="divTituloPrincipal" xmlns="">
        *            <label class="tituloPrincipal">Diversos</label>
        *        </div>
        *        <div class="indentacaoConteudo" xmlns="">
        *            <p>
        *                <a target="_blank" href="&#xD;&#xA;                exibirArquivo.aspx?conteudo=BYRQ4VAVk74=">
        *                    <span class="tituloConteudo">Tabela NCM e respectiva Utrib (comércio exterior) Vig. 01-07-2017 NT 2016.003 v.1.10 e NT 2016.001 v.1.30</span>
        *                </a>
        *                <br />
        *                Tabela NCM e respectiva Utrib (comércio exterior) Vig. 01-07-2017 NT 2016.003 v.1.10 e NT 2016.001 v.1.30
        *            </p>
        *            <p>
        *                <a target="_blank" href="&#xD;&#xA;                exibirArquivo.aspx?conteudo=RY8TPoGpyMY=">
        *                    <span class="tituloConteudo">Tabela Unidades de Medida Comercial 05.07.2016</span>
        *                </a>
        *                <br />
        *                Tabela Padrão de Unidades de Medidas Comerciais<br />
        *            </p>
        *            <p>
        *                <a target="_blank" href="&#xD;&#xA;                exibirArquivo.aspx?conteudo=4gvTCOtnEPo=">
        *                    <span class="tituloConteudo">Tabela de País relacionada a versão 1.40 da NT2015/002</span>
        *                </a>
        *                <br />
        *                Tabela de País relacionada a versão 1.40 da NT2015/002 <br />
        *            </p>
        *            <p>
        *                <a target="_blank" href="&#xD;&#xA;                exibirArquivo.aspx?conteudo=q45CSx9EjV8=">
        *                    <span class="tituloConteudo">Tabela de CFOP relacionada a versão 1.40 da NT2015/002</span>
        *                </a>
        *                <br />
        *                Tabela de CFOP relacionada a versão 1.40 da NT2015/002<br />
        *            </p>
        *            <p>
        *                <a target="_blank" href="&#xD;&#xA;                exibirArquivo.aspx?conteudo=2Cm8MVJiCVc=">
        *                    <span class="tituloConteudo">Tabela de Códigos de Produtos da ANP</span>
        *                </a>
        *                <br />
        *                Tabela de Códigos de Produtos da ANP<br />
        *            </p>
        *            <p>
        *                <a target="_blank" href="&#xD;&#xA;                exibirArquivo.aspx?conteudo=0InwUEoJIEI=">
        *                    <span class="tituloConteudo">DTB 2014 Municipio</span>
        *                </a>
        *                <br />
        *                Divisão Territorial Brasileira: Código e Nome UF, Código e Nome Município
        *            </p>
        *            <p>
        *                <a target="_blank" href="&#xD;&#xA;                exibirArquivo.aspx?conteudo=IjF66WihatE=">
        *                    <span class="tituloConteudo">Consumo Indevido do Ambiente de Autorização </span>
        *                </a>
        *                <br />
        *                Aplicação Cliente / Consumo Indevido do Ambiente de Autorização
        *            </p>
        *        </div>
        *    </body>
        *    </html>
        *</div>
        */
        $conteudoDinamico = $domCrawler->find('#conteudoDinamico')[0];
        if ($conteudoDinamico) {
            $indentacaoConteudo = $conteudoDinamico->find('.indentacaoConteudo')[0];
            if ($indentacaoConteudo) {
                foreach ($indentacaoConteudo->find('p') as $p) {
                    $text = $p->find('span')[0]->text;
                    array_push($data['docDiversosList'], $text);
                }
            }
        }

        $response = $client->request('GET', $urlNotasTecnicas, ['verify' => false]);
        $html = (string) $response->getBody();
        $domCrawler = (new Dom)->load($html);

        /*
        *
        * Notas Técnicas
        *
        <div id="conteudoDinamico">
            <?xml version="1.0" encoding="iso-8859-15"?>
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <title></title>
                <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
            </head>
            <body>
                <div class="divTituloPrincipal" xmlns="">
                    <label class="tituloPrincipal">Notas Técnicas</label>
                </div>
                <div class="indentacaoConteudo" xmlns="">
                    <p class="tituloSessao">2016</p>
                    <div class="indentacaoNormal">
                        <p>
                            <a target="_blank" href="&#xD;&#xA;                exibirArquivo.aspx?conteudo=9P2YLr5imvY=">
                                <span class="tituloConteudo">Nota Técnica 2016.003.v.1.10</span>
                            </a>
                            <br />
                            Nova Tabela de NCM - vigência a partir de 01/07/2017<br />
                        </p>
                        <p>
                            <a target="_blank" href="&#xD;&#xA;                exibirArquivo.aspx?conteudo=fhylYsTkC5I=">
                                <span class="tituloConteudo">Nota Técnica 2016.003</span>
                            </a>
                            <br />
                            Nova Tabela de NCM - vigência a partir de 01/01/2017<br />
                        </p>
                    </div>
                </div>
        */
        $conteudoDinamico = $domCrawler->find('#conteudoDinamico')[0];
        if ($conteudoDinamico) {
            $indentacaoConteudo = $conteudoDinamico->find('.indentacaoConteudo')[0];
            if ($indentacaoConteudo) {
                foreach ($indentacaoConteudo->find('.tituloSessao') as $pAno) {
                    $ano = $pAno->text;

                    $divIdentNormal = $indentacaoConteudo->find('.indentacaoNormal')[0];
                    if ($divIdentNormal) {
                        foreach ($divIdentNormal->find('p') as $p) {
                            $txt = $p->find('span')[0]->text;
                            $cont = $p->text;
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
        $response = $client->request('GET', $urlPrincipal, ['verify' => false]);
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
                    $titulo = trim($a->text);
                    $data = trim($span->text);
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
