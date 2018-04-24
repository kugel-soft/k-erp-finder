<?php

namespace Kugel\Utils;

use PHPHtmlParser\Dom;
use GuzzleHttp\Client;

class VagasUtils {
    public static function getVagas() {
        $data = [];

        // Vagas a pesquisar
        $vagasInteresse = array(
            'recepcionista',
            'atendente',
            'administrativo',
            'assistente'
        );

        $urlJoinvilleVagas = 'https://www.joinvillevagas.com.br/';
        $client = new Client();
        $response = $client->request('GET', $urlJoinvilleVagas, ['verify' => false]);
        $html = (string) $response->getBody();
        $domCrawler = (new Dom)->load($html);

        $olJobListings = $domCrawler->find('.job_listings')[0];
        if ($olJobListings) {
            $liJobListing = $olJobListings->find('.job_listing');

            foreach ($liJobListing as $li) {
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
                    'nomeVaga' => $nomeVaga,
                    'urlVaga' => $urlVaga,
                    'nomeEmpresa' => $nomeEmpresa,
                    'tipoVaga' => $tipoVaga,
                    'miniTextoVaga' => $miniTextoVaga,
                    'dataPublicacao' => $dataPublicacao,
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

                if ($add) {
                    array_push($data, $vaga);
                }
            }
        }

        $urlSine = 'https://www.sine.com.br/vagas-empregos-em-joinville-sc';
        $client = new Client();
        $response = $client->request('GET', $urlSine, ['verify' => false]);
        $html = (string) $response->getBody();
        $domCrawler = (new Dom)->load($html);

        $divJobs = $domCrawler->find('.jobs')[0];
        if ($divJobs) {
            $divItem = $divJobs->find('.item');
            foreach ($divItem as $job) {
            
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
                    'nomeVaga' => $nomeVaga,
                    'urlVaga' => $urlVaga,
                    'nomeEmpresa' => $nomeEmpresa,
                    'tipoVaga' => $tipoVaga,
                    'miniTextoVaga' => $miniTextoVaga,
                    'dataPublicacao' => $dataPublicacao,
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

                if ($add) {
                    array_push($data, $vaga);
                }
            }
        }
        return $data;
    }
}