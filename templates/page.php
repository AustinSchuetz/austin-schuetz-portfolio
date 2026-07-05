<?php

$content = View::renderBlocks($doc['blocks'] ?? [], ['site' => $site, 'doc' => $doc]);
include TEMPLATE_DIR . '/layout.php';
