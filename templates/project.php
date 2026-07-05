<?php

$content = '<article class="case-study">'
    . View::renderBlocks($doc['blocks'] ?? [], ['site' => $site, 'doc' => $doc])
    . View::partial('project-pager', ['doc' => $doc])
    . '</article>';
include TEMPLATE_DIR . '/layout.php';
