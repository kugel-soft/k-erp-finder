<?php

namespace Kugel\Utils;

use KubAT\PhpSimple\HtmlDomParser;

class ESocialUtils {
    public static function getNoticias() {
        $data = [];

        $urlPrincipal = 'http://portal.esocial.gov.br/noticias/todas-noticias';
        $domCrawler = HtmlDomParser::file_get_html( $urlPrincipal );

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

                // URL e texto URL
                $link = $newsContent->find('.tileHeadline');
                if (count($link) > 0) {
                    $a = $link[0]->find('a');
                    if (count($a) > 0) {
                        $url_content = trim($a[0]->href);
                        $url_text_content = trim($a[0]->innertext);
                        $title_content = '[Notícia] ' . trim($a[0]->innertext);
                    }
                }

                // descrição
                $description = $newsContent->find('.description');
                if (count($description) > 0) {
                    $description_content = $description[0]->innertext;
                }

                $documentByLine = $newsContent->find('.documentByLine');
                if (count($documentByLine) > 0) {
                    $summ = $documentByLine[0]->find('.summary-view-icon');

                    // publicado_em
                    if (count($summ) > 0) {
                        $tmpEm = trim($summ[0]->innertext);
                        if (strpos($tmpEm, '</i>') !== FALSE) {
                            $tmpEm = trim(substr($tmpEm, strpos($tmpEm, '</i>')+4));
                        }
                        $publicado_em_content = $tmpEm;
                    }

                    // publicado_as
                    if (count($summ) > 1) {
                        $tmpAs = trim($summ[1]->innertext);
                        if (strpos($tmpAs, '</i>') !== FALSE) {
                            $tmpAs = trim(substr($tmpAs, strpos($tmpAs, '</i>')+4));
                        }
                        $publicado_as_content = $tmpAs;
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

        // Adição dos agendamentos
        $urlPrincipal = 'http://portal.esocial.gov.br/agenda/agenda-1';
        $domCrawler = HtmlDomParser::file_get_html( $urlPrincipal );

        $divContentCore = $domCrawler->find('#content-core')[0];
        if ($divContentCore) {
            $h2List = $divContentCore->find('.tileItem');
            foreach ($h2List as $agenda) {
                // declaração de variaveis
                $title_content = "";
                $url_content = "";
                $url_text_content = "";
                $description_content = "";
                $publicado_em_content = "";
                $publicado_as_content = "";

                // URL e texto URL
                $link = $agenda->find('.tileHeadline');
                if (count($link) > 0) {
                    $a = $link[0]->find('a');
                    if (count($a) > 0) {
                        $url_content = trim($a[0]->href);
                        $url_text_content = trim($a[0]->innertext);
                        $title_content = '[Agenda] ' . trim($a[0]->innertext);
                    }
                }

                // descrição
                // Não tem!

                $documentByLine = $agenda->find('.documentByLine');
                if (count($documentByLine) > 0) {
                    $summ = $documentByLine[0]->find('.summary-view-icon');

                    // publicado_em
                    if (count($summ) > 0) {
                        $tmpEm = trim($summ[0]->innertext);
                        if (strpos($tmpEm, '</i>') !== FALSE) {
                            $tmpEm = trim(substr($tmpEm, strpos($tmpEm, '</i>')+4));
                        }
                        $publicado_em_content = $tmpEm;
                    }

                    // publicado_as
                    if (count($summ) > 1) {
                        $tmpAs = trim($summ[1]->innertext);
                        if (strpos($tmpAs, '</i>') !== FALSE) {
                            $tmpAs = trim(substr($tmpAs, strpos($tmpAs, '</i>')+4));
                        }
                        $publicado_as_content = $tmpAs;
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

        return $data;
    }
}