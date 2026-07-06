<?php

declare(strict_types=1);

/*
 * design/tokens.json -> assets/css/tokens.css
 * The Style Guide page reads the same JSON, so CSS and guide cannot drift.
 * Run: php scripts/build-tokens.php
 */
$root = dirname(__DIR__);
$tokens = json_decode((string) file_get_contents($root . '/design/tokens.json'), true);
if (!is_array($tokens)) {
    fwrite(STDERR, "tokens.json is invalid\n");
    exit(1);
}

$lines = ["/* GENERATED from design/tokens.json — edit that file, then re-run scripts/build-tokens.php */", ':root {'];

foreach ($tokens['colors'] as $scale => $steps) {
    foreach ($steps as $step => $hex) {
        $lines[] = "    --{$scale}-{$step}: {$hex};";
    }
}
foreach ($tokens['spacing'] as $step => $value) {
    $lines[] = "    --space-{$step}: {$value};";
}
$lines[] = '    --space-section: clamp(4rem, 3rem + 6vw, 7.5rem);';
foreach ($tokens['radius'] as $name => $value) {
    $lines[] = "    --radius-{$name}: {$value};";
}
foreach ($tokens['shadows'] as $name => $value) {
    $lines[] = "    --shadow-{$name}: {$value};";
}
foreach ($tokens['type'] as $name => $value) {
    $lines[] = "    --text-{$name}: {$value};";
}
foreach ($tokens['motion'] as $name => $value) {
    $lines[] = "    --{$name}: {$value};";
}
$lines[] = '}';

file_put_contents($root . '/assets/css/tokens.css', implode("\n", $lines) . "\n");
echo "Wrote assets/css/tokens.css (" . count($lines) . " lines)\n";
