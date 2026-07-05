<?php
$b = $block;
$kind = $b['kind'] ?? 'colors';
$tokensFile = BASE_DIR . '/design/tokens.json';
$tokens = is_file($tokensFile) ? (json_decode((string) file_get_contents($tokensFile), true) ?: []) : [];
?>
<section class="style-tokens style-tokens--<?= e($kind) ?>">
    <div class="container">
        <?php if ($kind === 'colors' && !empty($tokens['colors'])): ?>
            <?php foreach ($tokens['colors'] as $scale => $steps): ?>
                <h3 class="mono style-tokens__scale"><?= e($scale) ?></h3>
                <div class="swatch-row">
                    <?php foreach ((array) $steps as $step => $hex): ?>
                        <figure class="swatch">
                            <span class="swatch__chip" style="background: <?= e((string) $hex) ?>"></span>
                            <figcaption class="mono">--<?= e($scale) ?>-<?= e((string) $step) ?><br><?= e((string) $hex) ?></figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php elseif ($kind === 'type'): ?>
            <div class="type-specimen">
                <p class="type-specimen__display">Front-end developer turned product builder.</p>
                <h2>Heading two — Fraunces</h2>
                <h3>Heading three — Public Sans 600</h3>
                <p>Body copy set in Public Sans. The quick brown fox jumps over the lazy dog, then heads west on the Colorado Trail toward the divide.</p>
                <p class="mono">MONO EYEBROW — JETBRAINS MONO · 39.7392&deg; N</p>
            </div>
        <?php elseif ($kind === 'spacing' && !empty($tokens['spacing'])): ?>
            <div class="spacing-demo">
                <?php foreach ($tokens['spacing'] as $name => $value): ?>
                    <div class="spacing-demo__row"><span class="mono">--space-<?= e((string) $name) ?></span><span class="spacing-demo__bar" style="width: <?= e((string) $value) ?>"></span></div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($kind === 'shadows'): ?>
            <div class="swatch-row">
                <?php foreach (['xs', 'sm', 'md', 'lift'] as $s): ?>
                    <div class="card shadow-demo shadow-demo--<?= $s ?>"><span class="mono">--shadow-<?= $s ?></span></div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($kind === 'motifs'): ?>
            <div class="motif-demos">
                <div class="motif-demo motif-demo--topo"><span class="mono">topo — territory</span></div>
                <?php foreach (['a', 'b', 'c'] as $r): ?>
                    <div class="motif-demo"><span class="mono">ridge-<?= $r ?></span><?= View::partial('ridge-' . $r, ['tint' => 'green', 'flip' => false]) ?></div>
                <?php endforeach; ?>
                <div class="motif-demo motif-demo--grain"><span class="mono">paper grain — atmosphere</span></div>
            </div>
        <?php elseif ($kind === 'blocks-gallery'): ?>
            <ul class="blocks-gallery-list">
                <?php foreach (BlockSchema::blocks() as $type => $def): ?>
                    <li><span class="mono">block:<?= e($type) ?></span> — <?= e($def['label']) ?> (<?= count($def['fields']) ?> fields)</li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</section>
