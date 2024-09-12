<?php

class GazetteTranscriptParser
{
    public static function parseVote($ret, $term)
    {
        $ret->votes = [];
        foreach ($ret->blocks as $idx => $block) {
            while ($line = array_shift($block)) {
                if (trim($line) === '表決結果名單：') {
                    $vote = new StdClass;
                    $vote->line_no = $ret->block_lines[$idx];
                    $prev_key = null;
                    while ($line = array_shift($block)) {
                        if (preg_match('#^會議名稱：(.*)\s+表決型態：(.*)$#u', trim($line), $matches)) {
                            $vote->{'會議名稱'} = trim($matches[1]);
                            $vote->{'表決型態'} = $matches[2];
                        } else if (preg_match('#^(表決時間|表決議題)：(.*)#u', trim($line), $matches)) {
                            $prev_key = $matches[1];
                            $vote->{$matches[1]} = trim($matches[2]);
                        } else if (preg_match('#^表決結果：出席人數：(\d+)\s*贊成人數：(\d+)\s*反對人數：(\d+)\s*棄權人數：(\d+)#u', trim($line), $matches)) {
                            $vote->{'表決結果'} = [
                                '出席人數' => intval($matches[1]),
                                '贊成人數' => intval($matches[2]),
                                '反對人數' => intval($matches[3]),
                                '棄權人數' => intval($matches[4]),
                            ];
                            $prev_key = '表決結果';
                        } elseif ($prev_key == '表決議題' and strpos($line, '：') === false) {
                            $vote->{$prev_key} .= trim($line);
                        } elseif (preg_match('#^(贊成|反對|棄權)：$#u', trim($line), $matches)) {
                            $prev_key = null;
                            $content = '';
                            while (count($block) and (strpos($block[0], '：') === false)) {
                                $content .= str_replace(' ', '', array_shift($block));
                            }
                            $vote->{$matches[1]} = GazetteParser::parsePeople($content, $term);
                            if ($matches[1] == '棄權') {
                                break;
                            }
                        } else {
                            var_dump($vote);
                            var_dump($line);
                            continue 2;
                            exit;
                        }
                    }
                    $ret->votes[] = $vote;
                }
            }
        }
        return $ret;
    }

    /**
     * matchSectionTitle 檢查這一行是否有符合某一個議程的標題
     * 
     */
    public static function matchSectionTitle($p_dom, $agendas, $blocks = null)
    {
        $title = trim(str_replace('。', '', $p_dom->textContent));
        if ($title == '報告事項') {
            return $title;
        }

        if (is_array($blocks) and count($blocks) and is_array($blocks[0]) and count($blocks[0])  and strpos($blocks[0][0], '會議紀錄') !== false) {
            $titles = [];
            foreach ($blocks[0] as $line) {
                if (preg_match('#^[一二三四五六七八九十]+、(.*)#u', $line, $matches)) {
                    $matches[1] = str_replace('。', '', $matches[1]);
                    $titles[] = $matches[1];
                }
            }
            if (in_array($title, $titles)) {
                return $title;
            }
        }

        if (is_null($agendas)) {
            return false;
        }
        if ($p_dom->getElementsByTagName('b')->length > 0) {
            return false;
        }
        $title = str_replace('立法院', '', $title);
        $title = str_replace('案由：', '', $title);
        foreach ($agendas as $agenda) {
            $agenda->subject = str_replace('。', '', $agenda->subject);
            $agenda->subject = str_replace("\n", "", $agenda->subject);
            $agenda->subject = str_replace('立法院', '', $agenda->subject);
            if (strpos($agenda->subject, $title) === 0) {
                return $title;
            }
        }
        return false;
    }


    protected static $_logs = [];

    public static function checkSameTitle($agenda_title, $block_title)
    {
        if ($agenda_title == $block_title) {
            return true;
        }

        $agenda_title = str_replace("\n", "", $agenda_title);
        $agenda_title = str_replace('。', '', $agenda_title);
        $block_title = str_replace('。', '', $block_title);
        if (preg_match('#^(施政質詢|討論事項)?\s*([^─]*)─(.*)─#u', $agenda_title, $matches)) {
            self::$_logs[] = [$matches[1], $matches[2], $block_title];
            if (trim($matches[2]) == $block_title) {
                return true;
            }
        }
        if (strpos($agenda_title, '敬請公決')) {
            $agenda_title = str_replace('立法院', '', $agenda_title);
            $agenda_title = preg_replace('#─([^─]+)─$#u', '', $agenda_title);
            $block_title = str_replace('立法院', '', $block_title);

            self::$_logs[] = ['敬請公決', $agenda_title, $block_title];
            if ($agenda_title == $block_title) {
                return true;
            }
        }
        return false;
    }
    /**
     * filterAgendaBlock 篩選只有這一章節的內容
     * 
     */
    public static function filterAgendaBlock($blocks, $block_lines, $agendas, $hit_agenda)
    {
        if (is_null($agendas)) {
            return [$blocks, $block_lines];
        }
        $start_idx = $end_idx = null;

        $content = $hit_agenda->content;

        $sections = [];
        foreach ($blocks as $idx => $block) {
            if (strpos($block[0], '段落：') !== 0) {
                continue;
            }
            $title = explode('：', $block[0], 2)[1];
            $sections[] = $title;
            if (!self::checkSameTitle($content, $title)) {
                continue;
            }
            $start_idx = $idx;
            break;
        }
        if (is_null($start_idx)) {
            echo json_encode([
                'finding_content' => $content,
                'sections' => $sections,
                'logs' => self::$_logs,
                'hit_agenda' => $hit_agenda,
            ], JSON_UNESCAPED_UNICODE);
            exit;
            return [$blocks, $block_lines];
        }
        for ($i = $start_idx + 1; $i < count($blocks); $i ++) {
            if (strpos($blocks[$i][0], '段落：') === 0) {
                $end_idx = $i;
                break;
            }
        }
        if (is_null($end_idx)) {
            $end_idx = count($blocks);
        }
        $blocks = array_slice($blocks, $start_idx, $end_idx - $start_idx);
        $block_lines = array_slice($block_lines, $start_idx, $end_idx - $start_idx);
        return [$blocks, $block_lines];
    }

    public static function parseNumber($str)
    {
        if ($str == '十') {
            return 10;
        } elseif (preg_match('#^([二三四五六七八九])十([一二三四五六七八九])?$#u', $str, $matches)) {
            $n = self::parseNumber($matches[1]) * 10;
            if (isset($matches[2])) {
                $n += self::parseNumber($matches[2]);
            }
            return $n;
        } 
        $str = str_replace('一', '1', $str);
        $str = str_replace('二', '2', $str);
        $str = str_replace('三', '3', $str);
        $str = str_replace('四', '4', $str);
        $str = str_replace('五', '5', $str);
        $str = str_replace('六', '6', $str);
        $str = str_replace('七', '7', $str);
        $str = str_replace('八', '8', $str);
        $str = str_replace('九', '9', $str);
        $str = str_replace('十', '1', $str);
        $str = str_replace('○', '0', $str);
        return intval($str); 
    }

    public static function parse($content, $agendas = null, $hit_agenda = null)
    {
        $doc = new DOMDocument;
        // UTF-8
        $content = str_replace('<head>', '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">', $content);
        if ($_GET['html'] ?? false) {
            echo $content;
            exit;
        }

        @$doc->loadHTML($content);
        $has_b_doms = $doc->getElementsByTagName('b')->length > 10;

        $blocks = [];
        $block_lines = [];
        $current_block = [];
        $current_line = 1;
        $persons = [];
        $idx = 0;
        $p_doms = [];
        foreach ($doc->getElementsByTagName('body') as $body_dom) {
            foreach ($body_dom->childNodes as $child) {
                if ($child->nodeName == 'p') {
                    $p_doms[] = $child;
                }
            }
        }
        $section = null;
        $section_no = null;
        while (count($p_doms)) {
            $p_dom = array_shift($p_doms);
            $idx ++;

            $line = trim($p_dom->textContent);
            if (trim($line) == '') {
                continue;
            }

            if (strpos($p_dom->getAttribute('class'), '(標題)')) {
                $blocks[] = $current_block;
                $block_lines[] = $current_line;
                $current_line = $idx;
                $line = str_replace(' ', '', $line);
                $line = str_replace('　', '', $line);
                $blocks[] = ['段落：' . $line];
                $block_lines[] = $current_line;
                $current_line = $idx;
                $section = $line;
                $section_no = null;
                $current_block = [];
                continue;
            }

            if ($title = self::matchSectionTitle($p_dom, $agendas, $blocks)) {
                $blocks[] = $current_block;
                $block_lines[] = $current_line;
                $current_line = $idx;
                $blocks[] = ['段落：' . $title];
                $block_lines[] = $current_line;
                $current_line = $idx;
                $section = $line;
                $section_no = null;
                $current_block = [];
                continue;
            }

            // 如果是「第x案：」開頭，並且接下來五行內有「案由：xxx」，用案由去檢查
            if (preg_match('#^第.*案：#u', $line)) {
                for ($i = 0; $i < 5; $i ++) {
                    if (!preg_match('#^案由：#u', trim($p_doms[$i]->textContent))) {
                        continue;
                    }
                    $title = explode('：', $p_doms[$i]->textContent, 2)[1];
                    if ($title = self::matchSectionTitle($p_doms[$i], $agendas)) {
                        $blocks[] = $current_block;
                        $block_lines[] = $current_line;
                        $current_line = $idx;
                        $blocks[] = ['段落：' . $title];
                        $block_lines[] = $current_line;
                        $section = $title;
                        $current_block = [$line];
                        for ($j = 0; $j <= $i; $j ++) {
                            $idx ++;
                            $p_dom = array_shift($p_doms);
                            $line = trim($p_dom->textContent);
                            $current_block[] = $line;
                        }
                        $current_line = $idx;
                        continue 2;
                    }
                }

            }

            // 處理開頭是「國是論壇」
            if (!count($blocks) and $line == '國是論壇') {
                $blocks[] = ['段落：' . $line];
                $block_lines[] = $current_line;
                $current_line = $idx;
                $current_block[] = $line;
                while (count($p_doms)) {
                    $idx ++;
                    $p_dom = array_shift($p_doms);
                    $line = trim($p_dom->textContent);
                    $current_block[] = $line;
                    if (strpos($line, '：') !== false) {
                        break;
                    }
                }
                continue;
            }

            if (in_array($section, ['報告事項', '質詢事項', '討論事項'])) {
                if (preg_match('#^([一二三四五六七八九十○]+)、(.*)#u', $line, $matches)) {
                    $number = self::parseNumber($matches[1]);
                    if (is_null($section_no)) {
                        $section_no = $number - 1;
                    }
                    if ($number == $section_no + 1) {
                        $section_no = $number;
                        if ($current_block) {
                            $blocks[] = $current_block;
                            $block_lines[] = $current_line;
                            $current_line = $idx;
                            $current_block = [];
                        }
                        if ($section == '討論事項') {
                            $matches[2] = str_replace('（', '(', $matches[2]);
                            $matches[2] = str_replace('）', ')', $matches[2]);
                            $matches[2] = str_replace('［', '[', $matches[2]);
                            $matches[2] = str_replace('］', ']', $matches[2]);
                            $matches[2] = preg_replace('#\([^)]+\)\s*$#', '', $matches[2]);
                            $matches[2] = preg_replace('#\[[^]]+\]\s*$#', '', $matches[2]);
                            $current_block[] = '段落：' . $matches[2];
                        } else {
                            $current_block[] = '項目：' . $line;
                            continue;
                        }
                        $blocks[] = $current_block;
                        $block_lines[] = $current_line;
                        $current_line = $idx;
                        $current_block = [$line];
                        continue;
                    }
                } else {
                   /* if (strpos($line, '（以上質詢事項') === 0) {
                        $blocks[] = $current_block;
                        $block_lines[] = $current_line;
                        $current_line = $idx;
                        $current_block = [];
                        $section = null;
                    }
                    $current_block[] = $line;
                    continue;*/
                }
            }

            if (preg_match('#^立法院.*議事錄$#', $line)) {
                $blocks[] = $current_block;
                $block_lines[] = $current_line;
                $current_line = $idx;
                $current_block = [];
                $current_block[] = "段落：議事錄：$line";
                $current_block[] = $line;
                while (count($p_doms)) {
                    $p_dom = array_shift($p_doms);
                    $idx ++;
                    $line = trim($p_dom->textContent);
                    $line = str_replace('　', '  ', $line);
                    $line = trim($line, "\n");
                    $current_block[] = $line;
                    if (strpos($line, '散會') === 0) {
                        break;
                    }
                    if (strpos($line, '議事錄確定') !== false) {
                        break;
                    }
                }
                continue;
            }
            if ($has_b_doms) {
                $b_dom = $p_dom->getElementsByTagName('b')->item(0);
                if (!$b_dom or strpos($p_dom->textContent, '：') === false) {
                    $current_block[] = $line;
                    continue;
                }
                $text = $b_dom->textContent;
            } elseif (!preg_match('#^(.*)：(.*)$#u', $line, $matches)) {
                $current_block[] = $line;
                continue;
            } else {
                if (strpos($line, '，')) {
                    $current_block[] = $line;
                    continue;
                }
                $text = explode('：', $line)[0];
                if (in_array($text, [
                    '程序委員會意見', '表決結果名單', '會議名稱', '表決時間', '表決議題',
                    '表決結果', '贊成', '反對', '棄權', '說明',
                ])) {
                    $current_block[] = $line;
                    continue;
                }
            }
            $person = str_replace('：', '', $text);
            if (!array_key_Exists($person, $persons)) {
                $persons[$person] = 0;
            }
            $persons[$person] ++;
            $blocks[] = $current_block;
            $block_lines[] = $current_line;

            if (preg_match('#（(\d+)時(\d+)分）#', $line, $matches)) {
                $blocks[] = ['段落：質詢：' . $person . '：' . $matches[1] . ':' . $matches[2]];
                $block_lines[] = $current_line;
            } else if (preg_match('#（(\d+)時）#', $line, $matches)) {
                $blocks[] = ['段落：質詢：' . $person . '：' . $matches[1] . ':00'];
                $block_lines[] = $current_line;
            } elseif (strpos($line, '（發言結束）') !== false) {
                $line = str_replace('（發言結束）', '', $line);
                $blocks[] = ['段落：質詢結束'];
                $block_lines[] = $current_line;
            }
            $current_line = $idx;
            $current_block = [$line];
        }
        $blocks[] = $current_block;
        $block_lines[] = $current_line;
        $current_line = $idx;

        list($blocks, $block_lines) = self::filterAgendaBlock($blocks, $block_lines, $agendas, $hit_agenda);

        $ret = new StdClass;
        $ret->blocks = $blocks;
        $ret->block_lines = $block_lines;
        $ret->person_count = $persons;
        $ret->persons = array_keys($persons);

        while (count($blocks[0])) {
            $line = array_shift($blocks[0]);
            $line = str_replace('　', '  ', $line);
            if (trim($line) == '') {
                continue;
            }
            if (trim($line) == '委員會紀錄') {
                $ret->type = trim($line);
                continue;
            } else if (trim($line) == '國是論壇') {
                $ret->type = $ret->title = trim($line);
                continue;
            } elseif (trim($line) == '院會紀錄') {
                $ret->type = trim($line);
                continue;
            }
            if (strpos($line, '立法院第') === 0) {
                $ret->title = $line;
                $first_line = $line;
                $block_tmp = [];
                $origin_block = json_decode(json_encode($blocks[0]));
                while (trim($blocks[0][0]) != '') {
                    $text = $blocks[0][0];
                    $text = str_replace(' ', '', $text);
                    $text = str_replace('　', '', $text);
                    if (strpos($text, '時間') === 0) {
                        break;
                    }
                    $line = array_shift($blocks[0]);
                    $ret->title .= $line;
                    $block_tmp[] = $line; 
                }
                if (strlen($ret->title) > 5000) {
                    $ret->title = $first_line;
                    foreach ($block_tmp as $blk) {
                        array_push($blocks[0], $blk);
                    }
                    break;
                }
                continue;
            }
            $columns = array('時間', '地點', '主席');
            mb_internal_encoding('UTF-8');
            foreach ($columns as $c) {
                if (strpos(preg_replace('/[ 　]/u', '', $line), $c) === 0) {
                    $c_len = mb_strlen($c);
                    for ($i = 0; $i < mb_strlen($line); $i ++) {
                        if (in_array(mb_substr($line, $i, 1), array(' ', '　'))) {
                            continue;
                        }
                        $c_len --;
                        if ($c_len == 0) {
                            $ret->{$c} = ltrim(mb_substr($line, $i + 1));
                            break;
                        }
                    }
                    continue 2;
                }
            }
            if (property_exists($ret, '時間') and preg_match('#中華民國(\d+)年(\d+)月(\d+)日#', $ret->{'時間'}, $matches)) {
                $ret->date = sprintf('%d-%02d-%02d', $matches[1] + 1911, $matches[2], $matches[3]);
            }
        }
        if (property_exists($ret, 'title') and preg_match('#第(\d+)屆#', $ret->title, $matches)) {
            $term = intval($matches[1]);
            return self::parseVote($ret, $term);
        }
        return $ret;
    }

    public static function trimString($str)
    {
        $str = str_replace('　', '', $str);
        $str = str_replace("\r", '', $str);
        $str = str_replace("\n", '', $str);
        $str = str_replace(' ', '', $str);
        $str = str_replace('‧', '', $str);
        $str = str_replace('．', '', $str);
        $str = str_replace('&nbsp;', '', $str);
        return $str;
    }

    public static function filterBlockByTitle($blocks, $title)
    {
        if ('質詢事項' === $title) { 
            while (count($blocks->blocks)) {
                $first_blocks = $blocks->blocks[0];
                foreach ($first_blocks as &$line) {
                    if (self::trimString($line) != '質詢事項') {
                        array_shift($blocks->blocks[0]);
                        continue;
                    }
                    break 2;
                }
                array_shift($blocks->blocks);
                array_shift($blocks->block_lines);
            }
            return $blocks;
        } else {
            echo $title . "\n";
            print_r($blocks);
            exit;
        }
    }
}
