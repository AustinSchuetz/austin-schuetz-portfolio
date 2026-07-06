<?php

$breadcrumb = '<nav class="breadcrumb container" aria-label="Breadcrumb">'
    . '<a class="breadcrumb__back mono" href="/work">&larr; All work</a>'
    . '</nav>';

$content = '<article class="case-study">'
    . $breadcrumb
    . View::renderBlocks($doc['blocks'] ?? [], ['site' => $site, 'doc' => $doc])
    . View::partial('project-pager', ['doc' => $doc])
    . '</article>';
include TEMPLATE_DIR . '/layout.php';
