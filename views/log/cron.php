<?php
$ret = Elastic::dbQuery("/{prefix}logs-cron*/_search", "POST", json_encode([
    'size' => 100,
    'sort' => [
        'log_at' => 'desc'
    ],
]));
?>
<?php foreach ($ret->hits->hits as $hit) { ?>
ID: <?= $this->escape($hit->_source->id) ?><br>
Log at: <?= $this->escape(date('c', $hit->_source->log_at)) ?><br>
Start: <?= $this->escape($hit->_source->start) ?><br>
End: <?= $this->escape($hit->_source->end) ?><br>
delta: <?= $this->escape($hit->_source->delta) ?><br>
Status: <?= $this->escape($hit->_source->code) ?><br>
Stdout: <br>
<div style="border: 1px solid #ccc; padding: 10px; max-height: 200px; overflow: auto;">
    <?= $this->escape($hit->_source->output->stdout) ?>
</div>
Stderr: <br>
<div style="border: 1px solid #ccc; padding: 10px; max-height: 200px; overflow: auto;">
    <pre><?= $this->escape($hit->_source->output->stderr) ?></pre>
</div>
<hr>
<?php } ?>
