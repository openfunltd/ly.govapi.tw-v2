<?php

class GazetteAgendaDocController extends MiniEngine_Controller
{
    public function showAction($lcidc_id, $type)
    {
        // $type: html: openoffice, tikahtml, tikahtml, txt: text, parsed: parsed
        $filename = 'LCIDC01_' . $lcidc_id . '.doc';
        if ($type == 'tikahtml' or $type == 'parsed') {
            $url = 'https://lydata.ronny-s3.click/agenda-tikahtml/' . urlencode($filename) . ".html";
        } elseif ($type == 'txt') {
            $url = 'https://lydata.ronny-s3.click/agenda-txt/' . urlencode($filename);
        } elseif ($type == 'html') {
            $url = 'https://lydata.ronny-s3.click/agenda-html/' . urlencode($filename) . ".html";
        } else {
            throw new Exception('Invalid type: ' . $type);
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $content = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if ($info['http_code'] != 200) {
            throw new Exception('Failed to fetch content: ' . $url);
        }

        if ($type == 'parsed') {
            $content = GazetteTranscriptParser::parse($content);
            return $this->cors_json($content);
        }

        if ($type == 'tikahtml') {
            header('Content-Type: text/html');
            echo $content;
            exit;
        }

        if ($type == 'html') {
            $content = preg_replace_callback('#<img ([^>]*)src="([^"]*)"#', function($matches) use ($agenda_id) {                $attr = $matches[1];
            $src = $matches[2];
            if (!preg_match('#pic://(.*)\.([^.]*)$#', $src, $matches)) {
                return $matches[0];
            }
            $src = sprintf("https://lydata.ronny-s3.click/agenda-pic/%s.%s", $matches[1], $matches[2]);
            return "<img $attr src=\"$src\"";
            }, $content);
            header('Content-Type: text/html');
            echo $content;
            exit;
        }

        if ($type == 'txt') {
            header('Content-Type: text/plain');
            echo $content;
            exit;
        }
    }
}
