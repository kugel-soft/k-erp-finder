<?php

namespace Kugel\Utils;

use PHPHtmlParser\Dom;
use GuzzleHttp\Client;

class ESocialUtils {
    public static function getNoticias() {
        $data = [];

        $urlPrincipal = 'http://portal.esocial.gov.br/noticias/todas-noticias';
        $client = new Client();
        $response = $client->request('GET', $urlPrincipal, ['verify' => false]);
        $html = (string) $response->getBody();
        $domCrawler = (new Dom)->load($html);

        $divContentCore = $domCrawler->find('#content-core')[0];
        if ($divContentCore) {
            $divNewsList = $divContentCore->find('.tileItem');
            foreach ($divNewsList as $newsContent) {
                // declaração de variaveis
                $title_content = "";
                $url_content = "";
                $url_text_content = "";
                $description_content = "";
                $publicado_em_content = "";
                $publicado_as_content = "";

                // Title
                $subtitle = $newsContent->find('.subtitle');
                if (count($subtitle) > 0) {
                    $title_content = $subtitle[0]->text;
                }

                // URL e texto URL
                $link = $newsContent->find('.tileHeadline');
                if (count($link) > 0) {
                    $a = $link->find('a');
                    if (count($a) > 0) {
                        $url_content = $a[0]->href;
                        $url_text_content = $a[0]->text;
                    }
                }

                // descrição
                $description = $newsContent->find('.description');
                if (count($description) > 0) {
                    $description_content = $description[0]->text;
                }

                $documentByLine = $newsContent->find('.documentByLine');
                if (count($documentByLine) > 0) {
                    $summ = $documentByLine->find('.summary-view-icon');

                    // publicado_em
                    if (count($summ) > 0) {
                        $publicado_em_content = trim($summ[0]->text);
                    }

                    // publicado_as
                    if (count($summ) > 1) {
                        $publicado_as_content = trim($summ[1]->text);
                    }
                }

                $news = [
                    'title'       => $title_content,
                    'url'         => $url_content,
                    'url_text'    => $url_text_content,
                    'description' => $description_content,
                    'when'        => $publicado_em_content,
                    'at'          => $publicado_as_content,
                ];

                array_push($data, $news);
            }
        }
        else {
            return "NOT OK";
        }

        return $data;
    }
}