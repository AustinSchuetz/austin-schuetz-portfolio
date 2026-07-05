<?php
$b = $block;
$style = $b['style'] ?? 'rule';
?>
<?php if (str_starts_with($style, 'ridge-')): ?>
    <?= View::partial($style, ['tint' => $b['tint'] ?? 'green', 'flip' => !empty($b['flip'])]) ?>
<?php elseif ($style === 'rule'): ?>
    <div class="container"><hr class="divider-rule"></div>
<?php else: ?>
    <div class="divider-space" aria-hidden="true"></div>
<?php endif; ?>
