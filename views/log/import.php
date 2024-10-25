<?php
$ret = Elastic::dbQuery("/{prefix}logs-import*/_search", "POST", json_encode([
    'size' => 100,
    'sort' => [
        'log_at' => 'desc'
    ],
]));
?>
<?php foreach ($ret->hits->hits as $hit) { ?>
<?= json_encode($hit->_source, JSON_UNESCAPED_UNICODE) ?><br>
<hr>
<?php } ?>
