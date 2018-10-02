<?php

namespace Kugel\Utils;

use PHPHtmlParser\Dom;
use GuzzleHttp\Client;

class VagasUtils {
    public static function getVagas() {
        $data = [];
        $isDev = FALSE;
        $adicionado = FALSE;

        // Vagas a pesquisar
        $vagasInteresse = array(
            'recepcionista',
            'atendente',
            'administrativo',
            'assistente',
            'segurança do trabalho'
        );

        // Joinville Vagas - https://www.joinvillevagas.com.br/
        $urlJoinvilleVagas = 'https://www.joinvillevagas.com.br/';
        $client = new Client();
        $response = $client->request('GET', $urlJoinvilleVagas, ['verify' => false]);
        $html = (string) $response->getBody();
        $domCrawler = (new Dom)->load($html);
        $adicionado = FALSE;

        $olJobListings = $domCrawler->find('.job_listings')[0];
        if ($olJobListings) {
            $liJobListing = $olJobListings->find('.job_listing');

            foreach ($liJobListing as $li) {
                if ($isDev && $adicionado) continue;

                $nomeVaga = '';
                $nomeEmpresa = '';
                $tipoVaga = ''; // Efetivo, estágio..
                $miniTextoVaga = '';
                $dataPublicacao = '';
                $urlVaga = '';

                // Nome da vaga e URL
                $h3JobListingTitle = $li->find('.job_listing-title')[0];
                if ($h3JobListingTitle) {
                    $a = $h3JobListingTitle->find('a');
                    if (count($a) > 0) {
                        $nomeVaga = ucfirst(strtolower($a[0]->text));
                        $urlVaga = $a[0]->href;
                    }
                }

                // Nome da empresa
                $divJobListingCompany = $li->find('.job_listing-company')[0];
                if ($divJobListingCompany) {
                    $nomeEmpresa = trim($divJobListingCompany->text);
                }

                // Tipo da vaga
                $divJType = $li->find('.jtype')[0];
                if ($divJType) {
                    $tipoVaga = trim($divJType->text);
                    $tipoVaga = ucfirst(strtolower($tipoVaga));
                }

                // Descrição - Mini texto da vaga
                $divDescription = $li->find('.ti')[1];
                if ($divDescription) {
                    $miniTextoVaga = trim($divDescription->text);
                }

                // Data da publicação
                $divDetails = $li->find('.details')[0];
                if ($divDetails) {
                    $span = $divDetails->find('span')[0];
                    if ($span) {
                        $dataPublicacao = trim($span->text);
                    }
                }

                $vaga = [
                    'nomeVaga' => mb_convert_encoding($nomeVaga, "UTF-8"),
                    'urlVaga' => $urlVaga,
                    'nomeEmpresa' => mb_convert_encoding($nomeEmpresa, "UTF-8"),
                    'tipoVaga' => $tipoVaga,
                    'miniTextoVaga' => mb_convert_encoding($miniTextoVaga, "UTF-8"),
                    'dataPublicacao' => $dataPublicacao,
                    'isRH' => FALSE,
                    'origem' => 'Joinville Vagas',
                ];

                // Filtra as vagas de interesse
                $add = FALSE;

                if (in_array(strtolower($nomeVaga), $vagasInteresse)) {
                    $add = TRUE;
                }

                if (!$add) {
                    foreach ($vagasInteresse as $vg) {
                        if (StringUtils::contains(strtolower($nomeVaga), $vg)) {
                            $add = TRUE;
                            break;
                        }
                    }
                }

                if ($isDev) {
                    $add = TRUE;
                }

                if ($add) {
                    array_push($data, $vaga);
                    $adicionado = TRUE;
                } else {
                    // Vagas de RH para a esposa do Gerson
                    $isAnalista = StringUtils::contains(strtolower($nomeVaga), 'analista');
                    $isVagaPossivel =
                        StringUtils::contains(strtolower($nomeVaga), 'rh') ||
                        StringUtils::contains(strtolower($nomeVaga), 'recursos') ||
                        StringUtils::contains(strtolower($nomeVaga), 'recrutamento');

                    if ($isAnalista && $isVagaPossivel) {
                        $vaga['isRH'] = TRUE;
                        array_push($data, $vaga);
                    }
                }
            }
        }

        // Sine Joinville - https://www.sine.com.br/vagas-empregos-em-joinville-sc
        $urlSine = 'https://www.sine.com.br/vagas-empregos-em-joinville-sc';
        $client = new Client();
        $response = $client->request('GET', $urlSine, ['verify' => false]);
        $html = (string) $response->getBody();
        $domCrawler = (new Dom)->load($html);
        $adicionado = FALSE;

        $divJobs = $domCrawler->find('.jobs')[0];
        if ($divJobs) {
            $divItem = $divJobs->find('.item');
            foreach ($divItem as $job) {
                if ($isDev && $adicionado) continue;

                $nomeVaga = '';
                $nomeEmpresa = 'Não informado';
                $tipoVaga = 'Efetivo'; // Efetivo, estágio..
                $miniTextoVaga = '';
                $dataPublicacao = 'Não informado';
                $urlVaga = '';
                $valorSalario = '';

                // URL da vaga
                $a = $job->find('a')[0];
                if ($a) {
                    $urlVaga = 'https://www.sine.com.br' . $a->href;
                    $nomeVaga = trim($a->title);
                }

                // Valor do salário
                $p1 = $job->find('p')[0];
                if ($p1) {
                    $span = $p1->find('span')[0];
                    if ($span) {
                        $txt = trim($span->text);
                        if (StringUtils::startsWith($txt, 'R')) {
                            $valorSalario = $txt;
                        }
                    }
                }

                // Descrição da vaga
                $idx = 1;
                if ($valorSalario != '') {
                    $idx = 2;
                }
                $p2 = $job->find('p')[$idx];
                if ($p2) {
                    if ($valorSalario != '') {
                        $miniTextoVaga = 'Salário: ' . $valorSalario . '. ';
                    }
                    $tmp = trim(preg_replace("/\r\n|\r|\n/", '', $p2->text));
                    $miniTextoVaga .= $tmp;
                }

                $vaga = [
                    'nomeVaga' => mb_convert_encoding($nomeVaga, "UTF-8"),
                    'urlVaga' => $urlVaga,
                    'nomeEmpresa' => mb_convert_encoding($nomeEmpresa, "UTF-8"),
                    'tipoVaga' => $tipoVaga,
                    'miniTextoVaga' => mb_convert_encoding($miniTextoVaga, "UTF-8"),
                    'dataPublicacao' => $dataPublicacao,
                    'isRH' => FALSE,
                    'origem' => 'SINE Joinville',
                ];

                // Filtra as vagas de interesse
                $add = FALSE;

                if (in_array(strtolower($nomeVaga), $vagasInteresse)) {
                    $add = TRUE;
                }

                if (!$add) {
                    foreach ($vagasInteresse as $vg) {
                        if (StringUtils::contains(strtolower($nomeVaga), $vg)) {
                            $add = TRUE;
                            break;
                        }
                    }
                }

                if ($isDev) {
                    $add = TRUE;
                }

                if ($add) {
                    array_push($data, $vaga);
                    $adicionado = TRUE;
                } else {
                    // Vagas de RH para a esposa do Gerson
                    $isAnalista = StringUtils::contains(strtolower($nomeVaga), 'analista');
                    $isVagaPossivel =
                        StringUtils::contains(strtolower($nomeVaga), 'rh') ||
                        StringUtils::contains(strtolower($nomeVaga), 'recursos') ||
                        StringUtils::contains(strtolower($nomeVaga), 'recrutamento');

                    if ($isAnalista && $isVagaPossivel) {
                        $vaga['isRH'] = TRUE;
                        array_push($data, $vaga);
                    }
                }
            }
        }

        // Indeed - https://www.indeed.com.br/empregos?q=&l=Joinville%2C+SC
        $urlIndeed = 'https://www.indeed.com.br/empregos?q=&l=Joinville%2C+SC';
        $urlB = 'https://www.indeed.com.br';
        $client = new Client();
        $response = $client->request('GET', $urlIndeed, ['verify' => false]);
        $html = (string) $response->getBody();
        $domCrawler = (new Dom)->load($html);
        $adicionado = FALSE;

        $divJobs = $domCrawler->find('.row');
        foreach ($divJobs as $job) {
            $r = $job->getAttribute('class');
            if (!StringUtils::contains($r, 'result')) continue;
            if ($isDev && $adicionado) continue;

            $nomeVaga = '';
            $nomeEmpresa = 'Não informado';
            $tipoVaga = 'Efetivo'; // Efetivo, estágio..
            $miniTextoVaga = '';
            $dataPublicacao = 'Não informado';
            $urlVaga = '';
            $valorSalario = '';

            // Nome da vaga
            $h2JobTitle = $job->find('h2[class=jobtitle]')[0];
            if ($h2JobTitle) {
                $aEnd = $h2JobTitle->find('a')[0];
                if ($aEnd) {
                    $nomeVaga = ucfirst(strtolower(trim($aEnd->text)));

                    // URL da vaga
                    $urlVaga = $urlB . $aEnd->href;
                    $urlTmp = $aEnd->href;
                    $posCorte = strpos($urlTmp, '?');
                    if ($posCorte !== FALSE) {
                        $urlVaga = $urlB . substr($urlTmp, 0, $posCorte);
                    }
                }
            } else {
                $jobtitle = $job->find('.jobtitle')[0];
                if ($jobtitle) {
                    $nomeVaga = ucfirst(strtolower(trim($jobtitle->text)));

                    // URL da vaga
                    $urlVaga = $urlB . $jobtitle->href;
                    $urlTmp = $jobtitle->href;
                    $posCorte = strpos($urlTmp, '?');
                    if ($posCorte !== FALSE) {
                        $urlVaga = $urlB . substr($urlTmp, 0, $posCorte);
                    }
                }
            }

            // Nome da empresa
            $spanCompany = $job->find('span[class=company]')[0];
            if ($spanCompany) {
                $aComp = $spanCompany->find('a')[0];
                if ($aComp) {
                    $nomeEmpresa = ucfirst(strtolower(trim($aComp->text)));
                } else {
                    $nomeEmpresa = ucfirst(strtolower(trim($spanCompany->text)));
                }
            }

            // Valor do salário
            $spanNoWrap = $job->find('.no-wrap')[0];
            if ($spanNoWrap) {
                $valorSalario = trim($spanNoWrap->text);
            }

            // Descrição da vaga
            $spanSummary = $job->find('.summary')[0];
            if ($spanSummary) {
                $miniTextoVaga = trim($spanSummary->text);
            }

            // Data da publicação

            $vaga = [
                'nomeVaga' => mb_convert_encoding($nomeVaga, "UTF-8"),
                'urlVaga' => $urlVaga,
                'nomeEmpresa' => mb_convert_encoding($nomeEmpresa, "UTF-8"),
                'tipoVaga' => $tipoVaga,
                'miniTextoVaga' => mb_convert_encoding($miniTextoVaga, "UTF-8"),
                'dataPublicacao' => $dataPublicacao,
                'isRH' => FALSE,
                'origem' => 'Indeed',
            ];

            // Filtra as vagas de interesse
            $add = FALSE;

            if (in_array(strtolower($nomeVaga), $vagasInteresse)) {
                $add = TRUE;
            }

            if (!$add) {
                foreach ($vagasInteresse as $vg) {
                    if (StringUtils::contains(strtolower($nomeVaga), $vg)) {
                        $add = TRUE;
                        break;
                    }
                }
            }

            if ($isDev) {
                $add = TRUE;
            }

            if ($add) {
                array_push($data, $vaga);
                $adicionado = TRUE;
            } else {
                // Vagas de RH para a esposa do Gerson
                if (StringUtils::contains(strtolower($nomeVaga), 'rh')) {
                    $vaga['isRH'] = TRUE;
                }
                if (StringUtils::contains(strtolower($nomeVaga), 'recursos')) {
                    $vaga['isRH'] = TRUE;
                }
                if (StringUtils::contains(strtolower($nomeVaga), 'humanos')) {
                    $vaga['isRH'] = TRUE;
                }
                if ($vaga['isRH']) {
                    array_push($data, $vaga);
                }
            }
        }

        // Infojobs - https://www.infojobs.com.br/empregos-em-joinville,-sc.aspx
        $urlInfo = 'https://www.infojobs.com.br/empregos-em-joinville,-sc.aspx';
        $client = new Client();
        $response = $client->request('GET', $urlInfo, ['verify' => false]);
        $html = (string) $response->getBody();
        $domCrawler = (new Dom)->load($html);
        $adicionado = FALSE;

        $divJobs = $domCrawler->find('.element-vaga.unstyled');
        foreach ($divJobs as $job) {
            if ($isDev && $adicionado) continue;

            $nomeVaga = '';
            $nomeEmpresa = 'Não informado';
            $tipoVaga = 'Efetivo'; // Efetivo, estágio..
            $miniTextoVaga = '';
            $dataPublicacao = 'Não informado';
            $urlVaga = '';
            $valorSalario = '';

            // Nome da vaga
            $divVaga = $job->find('.vaga')[0];
            if ($divVaga) {
                $aVagaTitle = $divVaga->find('.vagaTitle')[0];
                if ($aVagaTitle) {
                    $h2 = $aVagaTitle->find('h2')[0];
                    if ($h2) {
                        $nomeVaga = trim($h2->text);
                    }
                }
            }

            $vaga = [
                'nomeVaga' => mb_convert_encoding($nomeVaga, "UTF-8"),
                'urlVaga' => $urlVaga,
                'nomeEmpresa' => mb_convert_encoding($nomeEmpresa, "UTF-8"),
                'tipoVaga' => $tipoVaga,
                'miniTextoVaga' => mb_convert_encoding($miniTextoVaga, "UTF-8"),
                'dataPublicacao' => $dataPublicacao,
                'isRH' => FALSE,
                'origem' => 'Indeed',
            ];

            // Filtra as vagas de interesse
            $add = FALSE;

            if (in_array(strtolower($nomeVaga), $vagasInteresse) || TRUE) {
                $add = TRUE;
            }

            if (!$add) {
                foreach ($vagasInteresse as $vg) {
                    if (StringUtils::contains(strtolower($nomeVaga), $vg)) {
                        $add = TRUE;
                        break;
                    }
                }
            }

            if ($isDev) {
                $add = TRUE;
            }

            if ($add) {
                array_push($data, $vaga);
                $adicionado = TRUE;
            } else {
                // Vagas de RH para a esposa do Gerson
                if (StringUtils::contains(strtolower($nomeVaga), 'rh')) {
                    $vaga['isRH'] = TRUE;
                }
                if (StringUtils::contains(strtolower($nomeVaga), 'recursos')) {
                    $vaga['isRH'] = TRUE;
                }
                if (StringUtils::contains(strtolower($nomeVaga), 'humanos')) {
                    $vaga['isRH'] = TRUE;
                }
                if ($vaga['isRH']) {
                    array_push($data, $vaga);
                }
            }
        }

        // RH Brasil - https://www.rhbrasil.com.br/site/vagas_unidade.php?cd_empresa=1&vagas=170&titulo=VAGAS+EM:+JOINVILLE

        // Manager - https://www.manager.com.br/empregos-cidade-joinville-sc-123-4

        return $data;
    }
}