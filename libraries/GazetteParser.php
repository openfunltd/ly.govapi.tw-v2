<?php

class GazetteParser
{
    public static $_name_list = null;

    public static function getNameList($term)
    {
        if (!$term) {
            throw new Exception("term is null");
        }
        if (is_null(self::$_name_list)) {
            self::$_name_list = new StdClass;
        }
        if (property_exists(self::$_name_list, $term)) {
            return self::$_name_list->{$term};
        }
        self::$_name_list->{$term} = [];
        if ($term == 10) {
            self::$_name_list->{$term}['陳秀寳'] = '陳秀寶';
        }

        $cmd = [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'term' => [
                                'term' => $term,
                            ],
                        ],
                    ],
                ],
            ],
            'fields' => ['name'],
            '_source' => false,
            'size' => 1000,
        ];
        $obj = Elastic::dbQuery("/{prefix}legislator/_search", 'GET', json_encode($cmd));
        foreach ($obj->hits->hits as $hit) {
            $name = $hit->fields->name[0];
            $queryname = str_replace('　', '', $name);
            $queryname = str_replace(' ', '', $queryname);
            $queryname = strtolower($queryname);
            $queryname = str_replace('‧', '', $queryname);
            $queryname = str_replace('．', '', $queryname);
            $queryname = str_replace('·', '', $queryname);
            self::$_name_list->{$term}[$queryname] = $hit->fields->name[0];
        }

        if ($term == 6) {
            self::$_name_list->{$term}[json_decode('"\u5085\ue8e4\u8401"')] = '傅崐萁';
        }

        if ($term == 7) {
            self::$_name_list->{$term}['郭添財'] = '許添財';
            self::$_name_list->{$term}['鐘紹和'] = '鍾紹和';
            self::$_name_list->{$term}['紀國楝'] = '紀國楝';
        }

        if ($term == 8) {
            self::$_name_list->{$term}['(SraKacaw)'] = '鄭天財 Sra Kacaw';
            self::$_name_list->{$term}['鄭天財'] = '鄭天財 Sra Kacaw';
            self::$_name_list->{$term}['吳宜瑧'] = '吳宜臻';
            self::$_name_list->{$term}['陳淑惠'] = '陳淑慧';
            self::$_name_list->{$term}['高金素'] = '高金素梅';
            self::$_name_list->{$term}['潘維綱'] = '潘維剛';
            self::$_name_list->{$term}['邱文'] = '邱文彥';
            self::$_name_list->{$term}['顏寬恆'] = '顏寬恒';
            self::$_name_list->{$term}['楊瓊櫻'] = '楊瓊瓔';
            self::$_name_list->{$term}['陳歐柏'] = '陳歐珀';
            self::$_name_list->{$term}['張碧涵'] = '陳碧涵';
            self::$_name_list->{$term}['詹臣凱'] = '詹凱臣';
            self::$_name_list->{$term}['黃明哲'] = '黃偉哲';
        }

        if ($term == 9) {
            self::$_name_list->{$term}['高金素'] = '高金素梅';
            self::$_name_list->{$term}['周陳秀'] = '周陳秀霞';
            self::$_name_list->{$term}['林麗嬋'] = '林麗蟬';
            self::$_name_list->{$term}['王惠'] = '王惠美';  
            self::$_name_list->{$term}['李秀彥'] = '李彥秀';
            self::$_name_list->{$term}['高潞以用巴魕剌KawloIyunacidal'] = '高潞．以用．巴魕剌Kawlo．Iyun．Pacidal';
            self::$_name_list->{$term}['KolasYotaka'] = '高潞．以用．巴魕剌Kawlo．Iyun．Pacidal';
            self::$_name_list->{$term}['Kolasotaka'] = '高潞．以用．巴魕剌Kawlo．Iyun．Pacidal';
            self::$_name_list->{$term}['KolasYotak'] = '高潞．以用．巴魕剌Kawlo．Iyun．Pacidal';
            self::$_name_list->{$term}['高潞以用巴魕剌KawloIyunPacida'] = '高潞．以用．巴魕剌Kawlo．Iyun．Pacidal';
            self::$_name_list->{$term}['高潞以用巴魕剌KawloIyunPacial'] = '高潞．以用．巴魕剌Kawlo．Iyun．Pacidal';
            self::$_name_list->{$term}['高潞以用巴魕刺KawloIyunPacidal'] = '高潞．以用．巴魕剌Kawlo．Iyun．Pacidal';
            self::$_name_list->{$term}['高潞以用巴魕剌'] = '高潞．以用．巴魕剌Kawlo．Iyun．Pacidal';
            self::$_name_list->{$term}['高潞以用·巴魕剌KawloIyunPacidal'] = '高潞．以用．巴魕剌Kawlo．Iyun．Pacidal';
            self::$_name_list->{$term}['高潞以用'] = '高潞．以用．巴魕剌Kawlo．Iyun．Pacidal';
            self::$_name_list->{$term}['簡東明UliwAljupayare'] = '簡東明Uliw．Qaljupayare';
            self::$_name_list->{$term}['簡東明'] = '簡東明Uliw．Qaljupayare';
            self::$_name_list->{$term}['廖國棟'] = '廖國棟Sufin‧Siluko';
            self::$_name_list->{$term}['Sufin．Siluko'] = '廖國棟Sufin‧Siluko';
            self::$_name_list->{$term}['SufinSiluko廖國棟'] = '廖國棟Sufin‧Siluko';
            self::$_name_list->{$term}['江啓臣'] = '江啟臣';
            self::$_name_list->{$term}['鄭天財'] = '鄭天財 Sra Kacaw';
            self::$_name_list->{$term}['呂玉鈴'] = '呂玉玲';
            self::$_name_list->{$term}['陳潔如'] = '陳怡潔';
            self::$_name_list->{$term}['陳寶清'] = '鄭寶清';
            self::$_name_list->{$term}['陳歐柏'] = '陳歐珀';
            self::$_name_list->{$term}['林政憲'] = '林俊憲';
            self::$_name_list->{$term}['陳賴素'] = '陳賴素美';
            self::$_name_list->{$term}['吳志楊'] = '吳志揚';
            self::$_name_list->{$term}['鍾孔紹'] = '鍾孔炤';
            self::$_name_list->{$term}['鐘孔炤'] = '鍾孔炤';
            self::$_name_list->{$term}['蔡宜津'] = '葉宜津';
            self::$_name_list->{$term}['施義方'] = '施義芳';
            self::$_name_list->{$term}['林俊俋'] = '李俊俋';
            self::$_name_list->{$term}['林福德'] = '林德福';
            self::$_name_list->{$term}['林麗禪'] = '林麗蟬';
            self::$_name_list->{$term}['蔡春米'] = '周春米';
            self::$_name_list->{$term}['徐臻蔚'] = '徐榛蔚';
            self::$_name_list->{$term}['費得泰'] = '費鴻泰';
            self::$_name_list->{$term}['鐘佳濱'] = '鍾佳濱';
            self::$_name_list->{$term}['李俊邑'] = '李俊俋';
            self::$_name_list->{$term}['莊瑞隆'] = '賴瑞隆';
            self::$_name_list->{$term}['呂孫稜'] = '呂孫綾';
            self::$_name_list->{$term}['徐世榮'] = '徐志榮';
            self::$_name_list->{$term}['顏寬恆'] = '顏寬恒';
        }
        if ($term == 10) {
            self::$_name_list->{$term}['王琬諭'] = '王婉諭';
            self::$_name_list->{$term}['王婉瑜'] = '王婉諭';
            self::$_name_list->{$term}['楊瓊櫻'] = '楊瓊瓔';
            self::$_name_list->{$term}['廖國棟'] = '廖國棟Sufin‧Siluko';
            self::$_name_list->{$term}['廖國SufinSiluko'] = '廖國棟Sufin‧Siluko';
            self::$_name_list->{$term}['鄭天財'] = '鄭天財 Sra Kacaw';
            self::$_name_list->{$term}['葉毓蘭'] = '游毓蘭';
            self::$_name_list->{$term}[json_decode('"\u5085\ue82f\u5d10\u8401"')] = '傅崐萁';
            self::$_name_list->{$term}[json_decode('"\u5085\u5d10\ue82f\u8401"')] = '傅崐萁';
            self::$_name_list->{$term}[json_decode('"\u5085\ue8e4\u8401"')] = '傅崐萁';
            self::$_name_list->{$term}['謝衣鳳'] = '謝衣鳯';
            self::$_name_list->{$term}['林楚菌'] = '林楚茵';
            self::$_name_list->{$term}['吳秉數'] = '吳秉叡';
            self::$_name_list->{$term}['莊端雄'] = '莊瑞雄';
            self::$_name_list->{$term}['伍麗華'] = '伍麗華Saidhai‧Tahovecahe';
            self::$_name_list->{$term}['羅美鈴'] = '羅美玲';
            self::$_name_list->{$term}['江啓臣'] = '江啟臣';
            self::$_name_list->{$term}['陳秀寶'] = '陳秀寳';
            self::$_name_list->{$term}['陳秀'] = '陳秀寳';
            self::$_name_list->{$term}[json_decode('"\u9673\u79c0\uf077"')] = '陳秀寳';
            self::$_name_list->{$term}['張蕙禎'] = '湯蕙禎';
            self::$_name_list->{$term}['黃士杰'] = '黃世杰';
            self::$_name_list->{$term}['謝依鳯'] = '謝衣鳯';
            self::$_name_list->{$term}[json_decode('"\u8b1d\u8863\ue93f"')] = '謝衣鳯';
            self::$_name_list->{$term}[json_decode('"\u8b1d\u8863\uebd4"')] = '謝衣鳯';
            self::$_name_list->{$term}['蔡璧如'] = '蔡壁如';
            self::$_name_list->{$term}['莊啟程'] = '莊競程';
            self::$_name_list->{$term}['陳柏維'] = '陳柏惟';
            self::$_name_list->{$term}['高家瑜'] = '高嘉瑜';
            self::$_name_list->{$term}['陳婉惠'] = '陳琬惠';
            self::$_name_list->{$term}['蘇志芬'] = '蘇治芬';
            self::$_name_list->{$term}['蔡副院長其昌'] = '蔡其昌';
            self::$_name_list->{$term}['羅政政'] = '羅致政';
            self::$_name_list->{$term}['傅萁'] = '傅崐萁';
            self::$_name_list->{$term}['傅萁'] = '傅崐萁';
            self::$_name_list->{$term}['陳文明'] = '陳明文';
            self::$_name_list->{$term}['吳怡汀'] = '吳怡玎';
        }
        if ($term == 11) {
            self::$_name_list->{$term}['楊瓊櫻'] = '楊瓊瓔';
            self::$_name_list->{$term}['鄭天財SraKaca'] = '鄭天財 Sra Kacaw';
            self::$_name_list->{$term}['鄭天財'] = '鄭天財 Sra Kacaw';
            self::$_name_list->{$term}['伍麗華SaidhaTahovecah'] = '伍麗華Saidhai‧Tahovecahe';
            self::$_name_list->{$term}['伍麗華'] = '伍麗華Saidhai‧Tahovecahe';
            self::$_name_list->{$term}['張啟楷'] = '張啓楷';
            self::$_name_list->{$term}['羅廷偉'] = '羅廷瑋';
            self::$_name_list->{$term}['游灝'] = '游顥';
        }

        return self::$_name_list->{$term};
    }

    public static function parsePeople($str, $term, $type = null)
    {
        $ostr = $str;
        $str = str_replace('召集', '', $str);
        $str = str_replace('委員', '', $str);
        $str = preg_replace('#（[^）]+）#u', '', $str);
        $str = str_replace('代理', '', $str);
        $str = str_replace('代表', '', $str);
        $str = str_replace('　', '', $str);
        $str = str_replace("\r", '', $str);
        $str = str_replace("\n", '', $str);
        $str = str_replace("\t", '', $str);
        $str = str_replace(json_decode('"\u00a0"'), '', $str);
        $str = str_replace(' ', '', $str);
        $str = str_replace('‧', '', $str);
        $str = str_replace('．', '', $str);
        $str = str_replace('·', '', $str);
        $str = str_replace('、', '', $str);
        $str = str_replace('&nbsp;', '', $str);
        $str = preg_replace('#^：#', '', $str);
        $str = str_replace('(一)會議室', '', $str);
        $str = str_replace('(二)視訊與會', '', $str);
        $str = preg_replace('#^:#', '', $str);
        $hit = [];

        $names = self::getNameList($term);
        if ($type == '提案') {
            $names['聯盟立法院黨團'] = '台灣團結聯盟立法院黨團';
            $names['台聯黨團'] = '台灣團結聯盟立法院黨團';
            $names['台灣團結聯盟黨團'] = '台灣團結聯盟立法院黨團';
            $names['台灣民眾黨黨團'] = '台灣民眾黨立法院黨團';
            $names['台灣民眾黨立法院黨團'] = '台灣民眾黨立法院黨團';
            $names['民眾黨黨團'] = '台灣民眾黨立法院黨團';
            $names['時代力量立法院黨團'] = '時代力量立法院黨團';
            $names['時代力量黨團'] = '時代力量立法院黨團';
            $names['時代力量'] = '時代力量立法院黨團';
            $names['中國國民黨立法院黨團'] = '中國國民黨立法院黨團';
            $names['國民黨黨團'] = '中國國民黨立法院黨團';
            $names['立法院國民黨黨團'] = '中國國民黨立法院黨團';
            $names['民主進步黨立法院黨團'] = '民主進步黨立法院黨團';
            $names['民進黨黨團'] = '民主進步黨立法院黨團';
            $names['親民黨立法院黨團'] = '親民黨立法院黨團';
            $names['立法院親民黨黨團'] = '親民黨立法院黨團';
            $names['親民黨立院黨團'] = '親民黨立法院黨團';
            $names['親民黨黨團立法院黨團'] = '親民黨立法院黨團';
            $names['台灣團結聯盟立法院黨團'] = '台灣團結聯盟立法院黨團';
            $names['立院新聯盟立法院政團'] = '立院新聯盟立法院政團';
            $names['無黨團結聯盟立法院黨團'] = '無黨團結聯盟立法院黨團';
        }

        while (strlen($str)) {
            foreach ($names as $qname => $name) {
                if (stripos($str, $qname) === 0) {
                    $str = substr($str, strlen($qname));
                    $hit[] = $name;
                    continue 2;
                }
            }
            if (preg_match('#^（\d+月\d+日）#', $str, $matches)) {
                // TODO: 有部份委員只出席一天，需要特別處理
                $str = substr($str, strlen($matches[0]));
                continue;
            }
            if (preg_match('#^\d+月\d+日（星期.）#u', $str, $matches)) {
                $str = substr($str, strlen($matches[0]));
                continue;
            }
            if (preg_match('#^[（(][^）\)]+[）)]#u', $str, $matches)) {
                // TODO: 一些備註
                $str = substr($str, strlen($matches[0]));
                continue;
            }
            if (strpos($str, '紀錄：') === 0 or strpos($str, '專門：') === 0) {
                break;
            }
            if (preg_match('#^(請假|出席|列席|列|視訊)\d+人#u', $str, $matches)) {
                $str = substr($str, strlen($matches[0]));
                continue;
            }
            if (strpos($str, '及') === 0) {
                $str = substr($str, strlen('及'));
                continue;
            }

            if (preg_match('#^等\d+人#u', $str, $matches)) {
                $str = substr($str, strlen($matches[0]));
                continue;
            }
            if ($str == '等') {
                break;
            }
            if ($type == '主席') { // 主席遇到錯誤就不急著處理了
                break;
            }
            echo json_encode($str);
            error_log("ostr = " . json_encode($ostr, JSON_UNESCAPED_UNICODE));
            throw new Exception("{$term} 找不到人名: " . json_encode($str, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        return $hit;
    }

    public static function matchFirstLine($line)
    {
        if (in_array(str_replace('　', '', trim($line)), array('報告事項', '討論事項'))) {
            return ['category', str_replace('　', '', trim($line))];
        }

        if (preg_match('#^([一二三四五六七八九])、#u', trim($line), $matches)) {
            return ['一', $matches[1]];
        }
        if (preg_match('#^(\([一二三四五六七八九十]+\))#u', trim($line), $matches)) {
            return ['(一)', $matches[1]];
        }
        return false;
    }

    public static function parseVote($ret)
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
                            $vote->{$matches[1]} = self::parsePeople($content);
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

    public static function parse($content)
    {
        $blocks = [];
        $block_lines = [];
        $current_block = [];
        $current_line = 1;
        $persons = [];
        $lines = explode("\n", $content);
        $skip = [
            "出席委員", "列席委員", "專門委員", "主任秘書", "決議", "決定", "請假委員", "說明", "列席官員", "註", "※註", "機關﹙單位﹚名稱", "單位", "在場人員", "歲出協商結論", "案由", "備註", "受文者", "發文日期", "發文字號", "速別", "附件", "主旨", "正本", "副本", "列席人員",
        ];
        $idx = 0;
        while (count($lines)) {
            $idx ++;
            $line = array_shift($lines);
            $line = str_replace('　', '  ', $line);
            $line = trim($line, "\n");

            if (trim($line) == '') {
                continue;
            }

            // 處理開頭是「國是論壇」
            if (!count($blocks) and $line == '國是論壇') {
                $current_block[] = $line;
                while (count($lines)) {
                    if (strpos($lines[0], '：')) {
                        break;
                    }
                    $idx ++;
                    $line = array_shift($lines);
                    $current_block[] = $line;
                }
                continue;
            }

            if (strpos($line, '|') === 0) {
                $current_block[] = $line;
                continue;
            }

            if (preg_match('#^[一二三四五六七八]、#u', $line)) {
                $blocks[] = $current_block;
                $block_lines[] = $current_line;
                $current_line = $idx;
                $current_block = ['段落：' . $line];
                continue;
            }

            if (preg_match('#^立法院.*議事錄$#', $line)) {
                $current_block[] = $line;
                while (count($lines)) {
                    $idx ++;
                    $line = array_shift($lines);
                    $line = str_replace('　', '  ', $line);
                    $line = trim($line, "\n");
                    $current_block[] = $line;
                    if (strpos($line, '散會') === 0) {
                        break;
                    }
                }
                continue;
            }
            if (!preg_match('#^([^　 ：]+)：(.+)#u', $line, $matches)) {
                if (!count($blocks) and (strpos($line, '（續接') === 0 or strpos($line, '（上接') === 0 or strpos($line, '[pic') === 0)) {
                    $blocks[] = $current_block;
                    $block_lines[] = $current_line;
                    $current_line = $idx;
                    $current_block = ['段落：' . $line];
                    continue;
                }
                $current_block[] = $line;
                continue;
            }
            $person = $matches[1];
            if (in_array($person, $skip) or strpos($person, '、')) {
                $current_block[] = $line;
                continue;
            }
            if (!array_key_Exists($person, $persons)) {
                $persons[$person] = 0;
            }
            $persons[$person] ++;
            $blocks[] = $current_block;
            $current_block = [$line];
            $block_lines[] = $current_line;
            $current_line = $idx;
        }
        $blocks[] = $current_block;
        $block_lines[] = $current_line;
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
            }
            if (strpos($line, '立法院第') === 0) {
                $ret->title = $line;
                $first_line = $line;
                $block_tmp = [];
                $origin_block = json_decode(json_encode($blocks[0]));
                while (trim($blocks[0][0]) != '') {
                    if (strpos(str_replace(' ', '', $blocks[0][0]), '時間') === 0) {
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
        }
        array_shift($ret->blocks);
        array_shift($ret->block_lines);
        return self::parseVote($ret);
    }

    public static function parseInterpellation($content)
    {
        $current_page = 1;
        $content = rtrim($content);
        if (strpos($content, '專案賥詢') !== false) {
            $content = str_replace('賥', '質', $content);
        }
        if (strpos($content, '案由〆本') !== false) {
            $content = str_replace('〆', '：', $content);
        }
        if (strpos($content, '中華术國') !== false) {
            $content = str_replace('术', '民', $content);
        }
        if (strpos($content, '職後，行政院派了與花蓮縣並無淵源的法務部次長蔡碧仲代') !== false) {
            $content = str_replace("職後，行政院派了與花蓮縣並無淵源的法務部次長蔡碧仲代", "案由：本院許委員淑華，鑒於花蓮縣前縣長傅崐萁被判刑定讞而解\n職後，行政院派了與花蓮縣並無淵源的法務部次長蔡碧仲代", $content);
        }

        if (strpos($content, '各約十萬輛次，在尖峰時，台北到花蓮花了 8 小時，因國五') !== false) {
            $content = str_replace('各約十萬輛次，在尖峰時，台北到花蓮花了 8 小時，因國五', "案由：本院傅委員崐萁，針對蘇花改今年通車，春節期間南下北上\n各約十萬輛次，在尖峰時，台北到花蓮花了 8 小時，因國五", $content);
        }

        if (strpos($content, '史、感染原因不明的死亡個案，建請行政院防疫作戰勢必更') !== false) {
            $content = str_replace('史、感染原因不明的死亡個案，建請行政院防疫作戰勢必更', '案由：本院傅委員崐萁，針對日前台灣已經出首例無接觸史、旅遊\n史、感染原因不明的死亡個案，建請行政院防疫作戰勢必更', $content);
        }

        if (strpos($content, '求，民眾因恐慌性瘋搶口罩，更使得口罩供應嚴重不足，進') !== false) {
            $content = str_replace("求，民眾因恐慌性瘋搶口罩，更使得口罩供應嚴重不足，進",
                "案由：本院傅委員崐萁，針對行政院因應「新冠肺炎」口罩供不應\n"
                . "求，民眾因恐慌性瘋搶口罩，更使得口罩供應嚴重不足，進", $content);
        }

        if (strpos($content, '國民宿業者住房率下降，花蓮民宿業者影響尤其嚴重，營業') !== false) {
            $content = str_replace("國民宿業者住房率下降，花蓮民宿業者影響尤其嚴重，營業",
                "案由：本院傅委員崐萁，針對新冠肺炎重挫我國觀光產業，以致全\n"
                . "國民宿業者住房率下降，花蓮民宿業者影響尤其嚴重，營業", $content);
        }

        if (strpos($content, '光市場，交通部目前紓困計畫有 5 個項目，已經籌編新台幣') !== false) {
            $content = str_replace("光市場，交通部目前紓困計畫有 5 個項目，已經籌編新台幣",
                "案由：本院傅委員崐萁，針對交通部因應「新冠肺炎」衝擊台灣觀\n"
                . "光市場，交通部目前紓困計畫有 5 個項目，已經籌編新台幣", $content);
        }

        if (strpos($content, '炎」疫情急速升溫，國內許多產業因嚴重特殊傳染性肺炎疫') !== false) {
            $content = str_replace("炎」疫情急速升溫，國內許多產業因嚴重特殊傳染性肺炎疫",
                "案由：本院傅委員崐萁，針對勞動部近日發函指出，因應「新冠肺\n"
                . "炎」疫情急速升溫，國內許多產業因嚴重特殊傳染性肺炎疫", $content);
        }

        if (strpos($content, '                                             中政策」宣') !== false) {
            $content = str_replace("                                             中政策」宣",
                "案由：本院傅委員崐萁，針對日前菲律賓衛生部以「一中政策」宣", $content);
        }

        if (strpos($content, '                                 型冠狀肺炎疫情日趨嚴重，') !== false) {
            $content = str_replace("                                 型冠狀肺炎疫情日趨嚴重，",
                "案由：本院陳委員秀寳，有鑑於目前新型冠狀肺炎疫情日趨嚴重，", $content);
        }

        if (strpos($content, '本院陳委素月，針對政府目')) {
            $content = str_replace('本院陳委素月，針對政府目', '本院陳委員素月，針對政府目', $content);
        }

        $lines = explode("\n", $content);
        $ret = new StdClass;
        $ret->doc_title = trim(array_shift($lines));

        $get_newline = function() use (&$lines, &$current_page, $ret, &$get_newline) {
            if (!count($lines)) {
                return null;
            }
            while (trim($lines[0]) == '') {
                if (!count($lines)) {
                    return null;
                }
                array_shift($lines);
            }

            if (preg_match('#^質 (\d+)$#u', trim($lines[0]), $matches) and trim(str_replace("\f", "", $lines[1])) == '') {
                return null;
            }
            while (preg_match('#^質 (\d+)$#u', trim($lines[0]), $matches) and strpos($lines[1], $ret->doc_title) !== false) {
                $current_page = intval($matches[1]) + 1;
                array_shift($lines);
                array_shift($lines);
                while (trim($lines[0]) == '') {
                    if (!count($lines)) {
                        return null;
                    }
                    array_shift($lines);
                }
                return $get_newline();

            }
            $line = array_shift($lines);
            if (strpos(trim($line), '案 由 ： 本 院 ') === 0) {
                $line = preg_replace('#([^ ])[ ]#u', "$1", $line);
            }
            if (strpos($line, '案由：本院陳委員學聖針對行政院回覆本席書面質詢之關係文書編') !== false) {
                $line = '案由：本院陳委員學聖，針對行政院回覆本席書面質詢之關係文書編';
            }
            if (strpos($line, '立法院議案關係文書 中華民國 104 年 10 月 14 印發') !== false) {
                $line = '立法院議案關係文書 中華民國 104 年 10 月 14 日印發';
            }
            return $line;
        };

        $pop_line = function($line) use (&$lines) {
            array_unshift($lines, $line);
        };

        $ret->interpellations = [];
        $interpellation = null;
        // 第一行會是 立法院第 8 屆第 1 會期第 1 次會議議案關係文書
        // 立法院第 11 屆第 1 會期第 10 次會議議事日程
        if (!preg_match('#立法院第 ([0-9]+) 屆第 ([0-9]+) 會期第 ([0-9]+) 次(會議議案關係文書|會議議事日程)#u', $ret->doc_title, $matches)) {
            throw new Exception("找不到屆期次: " . $ret->doc_title);
        }
        $ret->term = intval($matches[1]);
        $ret->sessionPeriod = intval($matches[2]);
        $ret->sessionTimes = intval($matches[3]);

        while (count($lines)) {
            $line = $get_newline();
            if (is_null($line)) {
                break;
            }
            // 專案質詢\n8－1－1－0001
            if (trim($line) == '專案質詢' and preg_match('#^(\d+)－(\d+)－(\d+)－(\d+)$#', trim($lines[0]), $matches)) {
                if (!is_null($interpellation)) {
                    $interpellation->page_end = $current_page;
                    $ret->interpellations[] = $interpellation;
                }
                $interpellation = new StdClass;
                $interpellation->id = implode('-', array_map('intval', array_slice($matches, 1)));
                $interpellation->page_start = $current_page;
                $interpellation->page_end = $current_page;
                array_shift($lines);

                $line = $get_newline();
                // 立法院議案關係文書 中華民國 101 年 2 月 22 日印發
                if (preg_match('#立法院議案關係文書 中華民國 ([0-9]+) 年 ([0-9]+) 月 ([0-9]+) 日印發#u', $line, $matches)) {
                    $interpellation->printed_at = sprintf("%04d-%02d-%02d", $matches[1] + 1911, $matches[2], $matches[3]);
                } else {
                    $pop_line($line);
                }
                continue;
            }

            if (preg_match('#^案由：(.*)$#u', trim($line), $matches)) {
                $interpellation->reason = $matches[1];
                if (preg_match('#^本院([^，、]+)委員([^，、]+)[，、]#u', $interpellation->reason, $matches)) {
                    $interpellation->legislators = [$matches[1] . $matches[2]];
                } elseif (preg_match('#^本院委員(.*)，#u', $interpellation->reason, $matches)) {
                    //本院委員鄭麗君、李俊俋，
                    $interpellation->legislators = explode('、', $matches[1]);
                } elseif (preg_match('#^本院([^，]*黨團)，#u', $interpellation->reason, $matches)) {
                    //本院台灣團結聯盟黨團，
                    $interpellation->legislators = [$matches[1]];
                } elseif (preg_match('#^本院([^，]*)委員，#u', $interpellation->reason, $matches)) {
                    //本院江惠貞委員，
                    $interpellation->legislators = [$matches[1]];
                } else {
                    throw new Exception("找不到委員: " . $interpellation->reason);
                }

                while ($line = $get_newline()) {
                    if (strpos(trim($line), '說明：') === 0) {
                        $pop_line($line);
                        break;
                    }
                    $interpellation->reason .= trim($line);
                }
                continue;
            }

            if (preg_match('#^說明：(.*)$#u', trim($line), $matches)) {
                $interpellation->description = $matches[1];
                if ($matches[1]) {
                    $interpellation->description .= "\n";
                }
                while ($line = $get_newline()) {
                    if (strpos(trim($line), '專案質詢') === 0) {
                        $pop_line($line);
                        break;
                    }
                    $interpellation->description .= trim($line) . "\n";
                }
                continue;
            }

            print_r($ret);
            echo json_encode($interpellation, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            echo "line: " . json_encode($line, JSON_UNESCAPED_UNICODE) . "\n";
            throw new Exception("unknown line");
        }
        if (!is_null($interpellation)) {
            $interpellation->page_end = $current_page;
            $ret->interpellations[] = $interpellation;
        }
        return $ret;
    }

    /**
     * 處理將公報經過 pdftotext -layout 產生的純文字
     * 轉成一頁一頁的文字陣列
     */
    public static function splitPages($content, $gazette_id)
    {
        $ret = new StdClass;
        $ret->pages = [];

        $page_contents = explode("\f", $content);
        // 第一頁一定有包含 The Legislative Yuan Gazette ，是封面
        $page_content = array_shift($page_contents);
        if (strpos($page_content, 'The Legislative Yuan Gazette') === false) {
            throw new Exception("第一頁不是封面");
        }

        // 第二頁一定是「目次」開頭
        $page_content = array_shift($page_contents);
        if (strpos(str_replace(" ", "", $page_content), '目次') !== 0) {
            throw new Exception("第二頁不是目次");
        }
        $lines = explode("\n", trim($page_content));
        $footer = array_pop($lines);

        if (!preg_match('#^\d\d\d\d [第全]#u', $footer)) {
            throw new Exception("目次頁尾不是第幾期: " . $footer);
        }
        $ret->issue_no = $footer;

        while (count($page_contents)) {
            $lines = explode("\n", trim($page_contents[0]));
            $footer = array_pop($lines);
            if ($footer == $ret->issue_no) {
                array_shift($page_contents);
                continue;
            }
            break;
        }

        // 略過空白頁
        while (count($page_contents) and $page_contents[0] == '') {
            array_shift($page_contents);
        }

        $page_no = 1;
        $page_type = null;
        foreach ($page_contents as $idx => $page_content) {
            $page_content = trim($page_content);
            $lines = explode("\n", $page_content);
            $line = array_shift($lines);
            $gazette_page = "{$gazette_id}-{$page_no}";

            if (!$lines) {
                $page_no ++;
                continue;
            }
            if ($idx == count($page_contents) - 2 and strpos($line, '本期冊別') === 0) {
                break;
            }
            if (in_array($gazette_page, [
                '1011702-387',
                '1012701-465',
            ])) {
                $page_type = '發言紀錄索引';
            }

            if (strpos(str_replace(' ', '', $line), '本期委員發言紀錄索引') !== false) {
                $page_type = '發言紀錄索引';
            } else if (strpos($line, '勘誤：') === 0) {
                $page_type = '勘誤';
            } else if ($page_type == '發言紀錄索引') {
                array_unshift($lines, $line);
            } else if (in_array($gazette_page, [
                '1011701-392',
            ])) {
                // 部份例外
                array_unshift($lines, $line);
            } else {
                // 第一行一定要是「立法院公報」開頭
                $line = str_replace(' ', '', $line);
                if (preg_match('#^立法院公報第([0-9]+)卷第([0-9]+)期(.*)#u', $line, $matches)) {
                    $page_type = trim($matches[3]);
                } else {
                    // 檢查是不是橫書
                    array_unshift($lines, $line);
                    $hit = [];
                    $hit_page = null;
                    foreach ($lines as $cline) {
                        if (trim($cline) == '立法院公報') {
                            $hit[] = trim($cline);
                        }
                        if (preg_match('#第 \d+ [卷期]#u', $cline)) {
                            $hit[] = $cline;
                        }
                        if (trim($cline) == $page_no) {
                            $hit_page = $cline;
                        }
                    }
                    if (count($hit) != 3 or is_null($hit_page)) {
                        throw new Exception("第 idx={$idx}, page_no={$page_no} 頁不是立法院公報: " . $line);
                    }
                    $line = implode('', $hit);
                    $lines[] = $hit_page;
                }
                if (!in_array($page_type, [
                    '院會紀錄',
                    '委員會紀錄',
                ])) {
                    throw new Exception("第 {$page_no} 頁不是立法院公報: " . $line);
                }

                if (strpos($line, '立法院公報') !== 0) {
                    throw new Exception("第 {$page_no} 頁不是立法院公報: " . $line);
                }
            }

            if (in_array($gazette_page, [
                '1011701-392',
            ])) {
                // 頁碼例外
            } else {
                // 最後一行一定要等於 $page_no 的數字
                $line = array_pop($lines);
                if (trim($line) != $page_no) {
                    throw new Exception("第 {$page_no} 頁最後一行不是頁碼: " . $line);
                }
            }
            $page = new StdClass;
            $page->page_no = $page_no;
            $page->page_type = $page_type;
            $page->content = trim(implode("\n", $lines));
            $ret->pages[] = $page;

            $page_no ++;
        }
        return $ret;
    }

    /**
     * 因為 $agenda_htmlfile 可能包含很多不同的頁數
     * 因此依照 GazetteParser::splitPages() 產生出來的 $gazette_pages
     * 只回傳需要的內容
     */
    public static function getContents($gazette_pages, $agenda_htmlfile, $agenda)
    {
        $doc = new DOMDocument;
        @$doc->loadHTMLFile($agenda_htmlfile);
        // 取得 <meta name="xmpTPg:NPages" content="4"/> 的頁碼
        $xpath = new DOMXPath($doc);
        $meta = $xpath->query('//meta[@name="xmpTPg:NPages"]')->item(0)->getAttribute('content');
        if (!$meta) {
            throw new Exception("找不到頁數");
        }
        if (count($agenda->docUrls) == 1 and ($agenda->pageEnd - $agenda->pageStart + 1) == $meta) {
            // 沒問題，全包了
        } else {
            print_r($agenda);
            throw new Exception("TODO: 頁數不一致: " . $meta);
        }

        // TODO
    }

    public static function replaceWord($content)
    {
        $content = str_replace('', '鳯', $content);
        $content = str_replace('', '寳', $content);
        $content = str_replace('', '堃', $content);
        $content = str_replace('', '崐', $content);
        $content = str_replace('', '崐', $content);
        $content = str_replace('年', '年', $content);
        $content = str_replace('專鬥委員', '專門委員', $content);
        $content = preg_replace('#\[bookmark: [^\]]+\]#u', '', $content);
        return $content;
    }

    public static function getDOMsFromHTMLs($htmls)
    {
        $doms = [];
        foreach ($htmls as $html) {
            // 讀取 $html 檔案，並且強制用 UTF-8
            $content = file_get_contents($html);
            $content = str_replace('<b>', '', $content);
            $content = str_replace('</b>', '', $content);
            $content = self::replaceWord($content);
            if (strpos($content, '議事錄') === false) {
                continue;
            }
            $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');
            $doc = new DOMDocument;
            @$doc->loadHTML($content);
            $prev_class = null;
            foreach ($doc->getElementsByTagName('body')->item(0)->childNodes as $node) {
                if ($node->nodeName == 'div' and in_array($node->getAttribute('class'), ['header', 'footer', 'embedded', 'package-entry'])) {
                    continue;
                }
                if ($node->nodeName == '#text' and trim($node->nodeValue) == '') {
                    continue;
                }
                if ($node->nodeName == 'h1' or $node->nodeName == 'h2') {
                    $doms[] = $node;
                    continue;
                }
                if ($node->nodeName == 'p') {
                    if ($prev_class == 'header') {
                        $prev_class = null;
                        if (strpos($node->nodeValue, '立法院公報') === 0) {
                            continue;
                        }
                    }
                    if ($prev_class == 'footer') {
                        $prev_class = null;
                        if (preg_match('#^\d+$#', $node->nodeValue)) {
                            continue;
                        }
                    }
                    $prev_class = null;
                    $class = $node->getAttribute('class');
                    if ($class == 'header') {
                        $prev_class = $class;
                        continue;
                    }
                    if ($class == 'footer') {
                        $prev_class = $class;
                        continue;
                    }
                    $doms[] = $node;
                    continue;
                }
                if ($node->nodeName == 'table') {
                    $doms[] = $node;
                    continue;
                }
                if ($node->nodeName == 'img') {
                    continue;
                }
                echo $doc->saveHTML($node);
                throw new Exception("不支援的 DOM: {$node->nodeName} (file: {$html})");
            }
        }
        return $doms;
    }

    public static function removeSpace($str)
    {
        $str = str_replace('　', '', $str);
        $str = str_replace(' ', '', $str);
        $str =  str_replace("\n", '', $str);
        return $str;
    }

    public static function parseSectionWithDate($doms, $current_date)
    {
        $section = new StdClass;
        $section->date = $current_date;
        $section->text = '';

        foreach ($doms as $dom) {
            if ($dom->cline == '散會') {
                break;
            }
            if (preg_match('#^(\d+)年(\d+)月(\d+)日[\(（]星期.[\)）]$#u', trim($dom->line), $matches)) {
                yield $section;
                $section->date = mktime(0, 0, 0, $matches[2], $matches[3], $matches[1] + 1911);
                $section->text = '';
                continue;
            } elseif (preg_match('#^(\d+)月(\d+)日$#u', trim($dom->line), $matches)) {
                yield $section;
                $section->date = mktime(0, 0, 0, $matches[1], $matches[2], date('Y', $section->date));
                $section->text = '';
                continue;
            }
            $section->text .= $dom->line;
        }
        yield $section;
    }

    public static function parseAgendaWholeMeetingNote($agenda, $meet_id = null, $meet_obj= null)
    {
        $fp = fopen($agenda, "r");
        $doms = [];
        while ($line = fgets($fp)) {
            if ('' == trim($line)) {
                continue;
            }
            if (preg_match('#^\s+(列席官員：.*)$#u', $line, $matches)) {
                $line = $matches[1];
            }
            $dom = new StdClass;
            $dom->line = self::replaceWord($line);
            $dom->cline = self::removeSpace($dom->line);
            $doms[] = $dom;
        }

        $ret = new StdClass;
        $ret->title = null;

        if ($meet_obj) {
            $current_meet_info = $meet_obj;
            $current_meet_id = $meet_obj->id;
            $meet_type = $meet_obj->type;
            $term = $meet_obj->term;
            $ret->term = $term;
            $ret->title = trim($meet_obj->title);
        } else {
            // 先確認「立法院第8屆第1會期第1次會議議事錄」從何時開始
            $meet_type = null;
            while (count($doms)) {
                $dom = array_shift($doms);
                $dom->cline = str_replace('[pic]', '', $dom->cline);
                $dom->cline = preg_replace('#\[image:[^\]]+\]#', '', $dom->cline);
                if (preg_match('#^立法院.*第\s*(\d+)\s*屆.*議事錄$#', trim($dom->cline), $matches)) {
                    $meet_type = null;
                    $current_meet_info = LYLib::meetNameToId($dom->cline);
                    $current_meet_id = $current_meet_info->id;
                    $meet_type = $current_meet_info->type;
                    $term = $current_meet_info->term;
                    if ($current_meet_id == $meet_id) {
                        $ret->term = $term;
                        $ret->title = trim($dom->cline);
                        break;
                    }
                    error_log("$current_meet_id {$dom->cline} != $meet_id");
                }
            }
            if (is_null($meet_type)) {
                throw new Exception("找不到會議開始");
            }
        }

        if ('院會' == $meet_type) {
            $columns = ['時間', '出席委員', '委員出席', '請假委員', '委員請假', '地點', '缺席委員', '委員缺席'];
        } elseif ('委員會' == $meet_type) {
            $columns = ['時間', '地點', '出席委員', '出席人員', '主席', '專門委員', '主任秘書', '紀錄', '速紀', '列席委員', '列席人員', '請假委員', '列席官員', '列席', '編審', '視訊委員'];
        } else if ('聯席會議' == $meet_type) {
            $columns = ['時間', '地點', '出席委員', '主席', '專門委員', '主任秘書', '紀錄', '速紀', '列席委員', '列席人員', '請假委員', '列席官員', '列席', '編審', '列席官員', '視訊委員'];
        } else {
            echo $meet_id . "\n";
            throw new Exception("不支援的會議型態: " . $meet_id . ' ' . $meet_type);
        }
        if (is_null($ret->title)) {
            throw new Exception("找不到議事錄標題: " . $meet_id);
        }
        $ret->{'人員名單'} = [];

        // 在 table 表格以前，會是 時間、出席委員、委員出席 ... 等資料
        $prev_col = null;
        $type = null;
        $section_name = null;
        while (count($doms)) {
            $dom = array_shift($doms);
            foreach ($columns as $col) {
                $value = $dom->cline;
                $value = preg_replace('#記錄#u', '紀錄', $value);
                if (strpos($value, $col) === 0 and !property_exists($ret, $col)) {
                    $prev_col = $col;
                    $ret->{$col} = substr($value, strlen($col));
                    if (strpos($ret->{$col}, '：') === 0) {
                        $ret->{$col} = substr($ret->{$col}, strlen('：'));
                    }
                    continue 2;
                } 
            }

            if ($prev_col == '時間' and preg_match('#^(\d+年|\d+月|下午|\d+時|中華民國)#u', $value)) {
                $ret->{'時間'} .= "\n" . trim($value);
                continue;
            }

            if (strpos(self::removeSpace($value), '討論事項') === 0
                or strpos(self::removeSpace($value), '報告事項') === 0
                or strpos(self::removeSpace($value), '其他事項') === 0
                or strpos(self::removeSpace($value), '朝野協商結論') === 0
                or strpos(self::removeSpace($value), '主席宣告：') === 0
            ) {
                // TODO: 留在章節名稱
                break;
            }


            if (in_array($prev_col, ['列席委員', '出席委員', '列席人員', '紀錄', '地點', '請假委員', '列席官員', '編審', '主席']) and strpos($value, '|') !== 0) {
                if (preg_match('#委員.*\d+人#u', $value)) { // LCIDC01_1056801_00008.doc
                    $ret->{$prev_col} .= $value;
                    if ($doms and $doms[0]->cline == '）') {
                        $ret->{$prev_col} .= array_shift($doms)->cline;
                    }
                    $prev_col = null;
                    continue;
                }

                if (strpos($value, '：') and in_array(explode('：', trim($value))[0], ['列席委員', '出席委員', '列席人員', '紀錄', '地點', '請假委員', '列席官員', '編審', '主席'])) {
                    $prev_col = trim(explode('：', $value)[0]);
                    $ret->{$prev_col} = explode('：', $value)[1];
                    continue;
                }
                $ret->{$prev_col} .= ' ' . trim($value);
                continue;
            }

            if (strpos($value, '|主席|') === 0 or strpos($value, '|列席') === 0) {
                $records = new StdClass;
                $records->{'人員'} = [];
                $records->{'備註'} = '';

                $type = null;
                while (true) {
                    $terms = explode('|', $value);
                    $terms = array_slice($terms, 1, -1);
                    if ($terms[0]) {
                        $type = $terms[0];
                    } else {
                        $terms[0] = $type;
                    }
                    $terms[0] = str_replace('：', '', $terms[0]);
                    if (count($terms) == 1 and strpos($terms[0], '列席人員') === 0) {
                        $type = '列席人員';
                    } elseif (count($terms) == 1 and strpos($terms[0], '列席官員') === 0) {
                        $type = '列席官員';
                    } elseif (count($terms) == 1 and preg_match('#^二、#', $terms[0])) {
                        // nothing
                    } elseif (count($terms) == 1 and preg_match('#^（.*）$#', $terms[0])) {
                        // nothing
                    } else if (count($terms) == 3) {
                        $record = new StdClass;
                        $record->{'身份'} = $terms[0];
                        $record->{'職稱'} = $terms[1];
                        $record->{'姓名'} = $terms[2];
                        if (preg_match('#(.*)（(.*)）#u', $record->{'姓名'}, $matches)) {
                            $record->{'姓名'} = $matches[1];
                            $record->{'備註'} = $matches[2];
                        }
                        $ret->{'人員名單'}[] = $record;
                    } else if (count($terms) == 2) {
                        $record = new StdClass;
                        $record->{'身份'} = $terms[0];
                        $record->{'姓名'} = $terms[2];
                        if (preg_match('#(.*)（(.*)）#u', $record->{'姓名'}, $matches)) {
                            $record->{'姓名'} = $matches[1];
                            $record->{'備註'} = $matches[2];
                        }
                        $ret->{'人員名單'}[] = $record;
                    } else if (count($terms) == 4) {
                        $record = new StdClass;
                        $record->{'身份'} = $terms[0];
                        $record->{'機關'} = $terms[1];
                        $record->{'職稱'} = $terms[2];
                        $record->{'姓名'} = $terms[3];
                        if (preg_match('#(.*)（(.*)）#u', $record->{'姓名'}, $matches)) {
                            $record->{'姓名'} = $matches[1];
                            $record->{'備註'} = $matches[2];
                        }
                        $ret->{'人員名單'}[] = $record;
                    } else {
                        echo 'error: ' . $value . "\n";
                        print_r($terms);
                        exit;
                    }
                    if (strpos($doms[0]->cline, '|') === false) {
                        break;
                    }
                    $value = array_shift($doms)->cline;
                }
                continue;
            }
        }

        $ret->{'時間'} = trim(ltrim($ret->{'時間'}, ':'));
        if (!preg_match('#^(中華民國)?(\d+)年(\d+)月(\d+)日#u', $ret->{'時間'}, $matches)) {
            throw new Exception("時間格式不正確: {$ret->{'時間'}}");
        }
        $current_date = mktime(0, 0, 0, $matches[3], $matches[4], $matches[2] + 1911);
        if ($meet_type == '聯席會議' or ($meet_type == '委員會' and $current_meet_info->committees[0] != 27)) {
            $ret->{'質詢'} = [];
            foreach (self::parseSectionWithDate($doms, $current_date) as $section) {
                $section->text = preg_replace('#【\d+份】#u', '', $section->text);
                if (preg_match('#委員([^；，。]*)等\d+人(提出)?質詢#u', $section->text, $matches)) {
                    if (strpos($matches[1], '會議詢問')) {
                        $matches[1] = preg_replace('#^.*等\d*人會議詢問、#', '', $matches[1]);
                    }
                    $ret->{'質詢'}[] = [
                        '種類' => '口頭質詢',
                        '日期' => date('Y-m-d', $section->date),
                        '委員' => self::parsePeople($matches[1], $ret->term),
                    ];
                } else if (preg_match('#委員([^；，。]*)提出質詢#u', $section->text, $matches)) {
                    try {
                        $people = self::parsePeople($matches[1], $ret->term);
                        $ret->{'質詢'}[] = [
                            '種類' => '口頭質詢',
                            '日期' => date('Y-m-d', $section->date),
                            '委員' => $people,
                        ];
                    } catch (Exception $e) {
                    }
                } else {
                    //echo mb_strimwidth($section->text, 0, 100, '...', 'utf-8');
                    //throw new Exception("找不到口頭質詢: " . json_encode($current_meet_info, JSON_UNESCAPED_UNICODE));
                }
                if (preg_match('#委員([^；，。]*)(等\d+人)?所提書面質詢#u', $section->text, $matches)) {
                    $ret->{'質詢'}[] = [
                        '種類' => '書面質詢',
                        '日期' => date('Y-m-d', $section->date),
                        '委員' => self::parsePeople($matches[1], $ret->term),
                    ];
                } elseif (preg_match('#委員([^；，。]*)提出書面質詢#u', $section->text, $matches)) {
                    $ret->{'質詢'}[] = [
                        '種類' => '書面質詢',
                        '日期' => date('Y-m-d', $section->date),
                        '委員' => self::parsePeople($matches[1], $ret->term),
                    ];
                } else {
                    //echo mb_strimwidth($other_text, 0, 100, '...', 'utf-8');
                    //throw new Exception("找不到書面質詢: " . json_encode($current_meet_info, JSON_UNESCAPED_UNICODE));
                }
            }
        }
        $ret->{'出席委員'} = self::parsePeople($ret->{'出席委員'}, $ret->term);
        foreach (['請假委員', '缺席委員', '視訊委員'] as $c) {
            if (property_exists($ret, $c)) {
                $ret->{$c} = self::parsePeople($ret->{$c}, $ret->term);
            }
        }
        if ($meet_type == '委員會' or $meet_type == '聯席會議') {
            foreach (['主席', '列席委員', '請假委員'] as $c) {
                if (property_exists($ret, $c) and is_string($ret->{$c})) {
                    $ret->{$c} = self::parsePeople($ret->{$c}, $ret->term, $c);
                }
            }
       
            foreach ($columns as $c) {
                if (property_exists($ret, $c) and is_string($ret->{$c})) {
                    $ret->{$c} = preg_replace('#^：#', '', $ret->{$c});
                }
            }
        }
        // 把還沒辦法結構化的先跳過，之後再處理
        foreach (["列席人員", "紀錄"] as $c) {
            if (property_exists($ret, $c)) {
                unset($ret->{$c});
            }
        }

        return $ret;
    }

    public static function getAgendaDocHTMLs($agenda, $retry = 0)
    {
        foreach ($agenda->docUrls as $url) {
            if (!preg_match('#https://ppg.ly.gov.tw/ppg/download/communique1/work/\d+/\d+/(LCIDC01_\d+_\d+.doc)#', $url, $matches)) {
                throw new Exception("找不到檔案: " . $url);
            }
            $filename = $matches[1];

            $doc_file = __DIR__ . '/imports/gazette/agenda-doc/' . $filename;
            if (!file_Exists($doc_file)) {
                system(sprintf("wget -4 -O %s %s", escapeshellarg(__DIR__ . '/tmp.doc'), escapeshellarg($url)), $ret);
                if ($ret) {
                    throw new Exception("下載失敗: " . $url);
                }
                copy(__DIR__ . '/tmp.doc', $doc_file);
                unlink(__DIR__ . '/tmp.doc');
            }

            $txt_file = __DIR__ . '/imports/gazette/agenda-txt/' . $filename;
            if (filesize($txt_file) < 1000 and strpos(file_get_contents($txt_file), '503 Service Unavailable') !== false) {
                unlink($txt_file);
            }
            if (!file_exists($txt_file) or filesize($txt_file) < 10) {
                error_log("轉檔: " . $txt_file);
                $cmd = sprintf("curl -T %s https://tika.openfun.dev/tika -H 'Accept: text/plain' > %s", escapeshellarg($doc_file), escapeshellarg(__DIR__ . '/tmp.txt'));
                system($cmd, $ret);
                //system(sprintf("antiword %s > %s", escapeshellarg($doc_file), escapeshellarg(__DIR__ . '/tmp.txt')), $ret);
                if ($ret) {
                    throw new Exception("轉檔失敗: " . $doc_file);
                }
                if (filesize(__DIR__ . '/tmp.txt') < 10) {
                    unlink($doc_file);
                    if ($retry > 3) {
                        throw new Exception("轉檔失敗: " . $doc_file);
                    }
                    return self::getAgendaDocHTMLs($agenda, $retry + 1);
                }
                copy(__DIR__ . '/tmp.txt', $txt_file);
                unlink(__DIR__ . '/tmp.txt');
            }
        }
    }

    public static function getSpeechFromGazette($txtfile)
    {
        $fp = fopen($txtfile, 'r');
        $lines = [];
        $lineno = 0;
        // 找 本期委員發言紀錄索引
        while (false !== ($line = fgets($fp))) {
            // remove ^L
            $line = preg_replace('#\x0c#', '', $line);
            if (trim($line) == '本期委員發言紀錄索引') {
                break;
            }
        }

        if (false === $line) {
            throw new Exception("找不到 本期委員發言紀錄索引");
        }

        while (false !== ($line = fgets($fp))) {
            if (strpos($line, '補刊：') !== false or strpos($line, '本期冊別') !== false) {
                break;
            }
            if (strpos($line, '勘誤：') !== false) {
                break;
            }
            if (strpos($line, '其他：') !== false) {
                break;
            }
            $line = preg_replace('#\x0c#', '', $line);
            if (preg_match('#^[0-9]+$#', trim($line))) {
                continue;
            }
            if (preg_match('#^第\d+屆第\d+會期第\d+期$#', trim($line))) {
                continue;
            }
            if (preg_match('#^出版日期:\d+年\d+月\d+日$#', trim($line))) {
                continue;
            }
            $line = preg_replace('#（[上下]接第.冊）#u', '', $line);
            if (trim($line) == '') {
                continue;
            }
            $line = preg_replace('#格式化: (.*)$#u', '', $line);
            $lines[] = $line;
        }
        fclose($fp);

        $ret = new StdClass;
        $ret->meet_name = $ret->content = $ret->speakers = '';
        // 先抓 立法院xxx 開頭的
        while (count($lines)) {
            $line = array_shift($lines);
            if (preg_match('#^立法院(.*)會議#', $line, $matches)) {
                if ($ret->content) {
                    $ret->line = $line;
                    yield clone $ret;
                }
                $ret->meet_name = trim($line);
                $ret->content = '';
                $ret->speakers = null;
                continue;
            } elseif (preg_match('#^立法院(.*)#', $line) and preg_match('#紀錄#', $lines[0])) {
                if ($ret->content) {
                    $ret->line = $line;
                    yield clone $ret;
                }
                $ret->meet_name = trim($line) . trim(array_shift($lines));
                $ret->content = '';
                $ret->speakers = null;
                continue;

            }

            if (preg_match('#（頁次[^）]*）$#u', trim($line))) {
                if (!is_null($ret->speakers)) {
                    $ret->line = $line;
                    yield clone $ret;
                    $ret->content = '';
                    $ret->speakers = '';
                }
                $ret->content .= $line;
                $ret->speakers = '';
                continue;
            }

            if (strpos(str_replace(' ', '', $line), '發言者') === 0) {
                if (is_null($ret->speakers)) {
                    $ret->speakers = '';
                }
                $ret->speakers .= str_replace('發言者', '', str_replace(' ', '', $line));
                continue;
            }

            if (is_null($ret->speakers)) {
                $ret->content .= $line;
                continue;
            }

            if (ltrim($line) == $line) {
                $ret->line = $line;
                yield clone $ret;
                $ret->content = $line;
                $ret->speakers = null;
                continue;
            }
            $ret->speakers .= $line;
        }

        if ($ret->content) {
            $ret->line = $line;
            yield clone $ret;
        }
    }
}
