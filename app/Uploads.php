<?php

declare(strict_types=1);

/*
 * Upload hardening: extension whitelist AND finfo MIME AND getimagesize must
 * all agree, then the image is re-encoded through GD (which destroys any
 * polyglot payload and strips EXIF), saved under a random name. The uploads
 * directory additionally refuses to execute PHP via its own .htaccess.
 */
final class Uploads
{
    private const MAX_BYTES = 10485760; // 10MB
    private const MAX_EDGE = 2400;
    private const THUMB_WIDTH = 480;

    private const EXT_WHITELIST = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private const MIME_WHITELIST = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    public static function handle(array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) {
            return ['ok' => false, 'error' => 'Upload failed.'];
        }
        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            return ['ok' => false, 'error' => 'File exceeds 10MB.'];
        }

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, self::EXT_WHITELIST, true)) {
            return ['ok' => false, 'error' => 'Only jpg, png, gif, webp allowed.'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, self::MIME_WHITELIST, true)) {
            return ['ok' => false, 'error' => 'File content is not an allowed image type.'];
        }

        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
            return ['ok' => false, 'error' => 'File is not a readable image.'];
        }

        // Load by DETECTED type, not extension; output format follows detection.
        [$image, $outExt] = match ($info[2]) {
            IMAGETYPE_JPEG => [@imagecreatefromjpeg($file['tmp_name']), 'jpg'],
            IMAGETYPE_PNG => [@imagecreatefrompng($file['tmp_name']), 'png'],
            IMAGETYPE_GIF => [@imagecreatefromgif($file['tmp_name']), 'png'],
            IMAGETYPE_WEBP => [@imagecreatefromwebp($file['tmp_name']), 'webp'],
            default => [false, ''],
        };
        if ($image === false) {
            return ['ok' => false, 'error' => 'Image could not be decoded.'];
        }

        $name = 'img_' . bin2hex(random_bytes(8));
        $mainPath = UPLOADS_DIR . '/' . $name . '.' . $outExt;
        $thumbPath = UPLOADS_DIR . '/' . $name . '_thumb.' . $outExt;

        $resized = self::scale($image, self::MAX_EDGE);
        $thumb = self::scaleToWidth($resized, self::THUMB_WIDTH);

        $okMain = self::save($resized, $mainPath, $outExt);
        $okThumb = self::save($thumb, $thumbPath, $outExt);
        imagedestroy($image);
        if ($resized !== $image) {
            imagedestroy($resized);
        }
        imagedestroy($thumb);

        if (!$okMain || !$okThumb) {
            @unlink($mainPath);
            @unlink($thumbPath);
            return ['ok' => false, 'error' => 'Could not write image.'];
        }
        return [
            'ok' => true,
            'path' => '/media/uploads/' . $name . '.' . $outExt,
            'thumb' => '/media/uploads/' . $name . '_thumb.' . $outExt,
        ];
    }

    /** @return array[] uploaded images (main files only), newest first */
    public static function list(): array
    {
        $out = [];
        foreach (glob(UPLOADS_DIR . '/img_*.{jpg,png,webp,gif}', GLOB_BRACE) ?: [] as $file) {
            $base = basename($file);
            if (str_contains($base, '_thumb.')) {
                continue;
            }
            $thumb = preg_replace('/\.(\w+)$/', '_thumb.$1', $base);
            $out[] = [
                'path' => '/media/uploads/' . $base,
                'thumb' => is_file(UPLOADS_DIR . '/' . $thumb) ? '/media/uploads/' . $thumb : '/media/uploads/' . $base,
                'mtime' => filemtime($file) ?: 0,
            ];
        }
        usort($out, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
        return $out;
    }

    public static function delete(string $webPath): bool
    {
        $base = basename($webPath);
        if (!preg_match('/^img_[a-f0-9]{16}(_thumb)?\.(jpg|png|webp|gif)$/', $base)) {
            return false;
        }
        $main = UPLOADS_DIR . '/' . $base;
        $thumb = UPLOADS_DIR . '/' . preg_replace('/\.(\w+)$/', '_thumb.$1', $base);
        $ok = is_file($main) && unlink($main);
        if (is_file($thumb)) {
            unlink($thumb);
        }
        return $ok;
    }

    private static function scale(GdImage $img, int $maxEdge): GdImage
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $edge = max($w, $h);
        if ($edge <= $maxEdge) {
            return $img;
        }
        $ratio = $maxEdge / $edge;
        return self::resample($img, (int) round($w * $ratio), (int) round($h * $ratio));
    }

    private static function scaleToWidth(GdImage $img, int $width): GdImage
    {
        $w = imagesx($img);
        $h = imagesy($img);
        if ($w <= $width) {
            return self::resample($img, $w, $h); // always a copy so caller can destroy safely
        }
        return self::resample($img, $width, (int) round($h * $width / $w));
    }

    private static function resample(GdImage $img, int $w, int $h): GdImage
    {
        $canvas = imagecreatetruecolor($w, $h);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);
        imagecopyresampled($canvas, $img, 0, 0, 0, 0, $w, $h, imagesx($img), imagesy($img));
        return $canvas;
    }

    private static function save(GdImage $img, string $path, string $ext): bool
    {
        return match ($ext) {
            'jpg' => imagejpeg($img, $path, 82),
            'png' => imagepng($img, $path, 6),
            'webp' => imagewebp($img, $path, 82),
            default => false,
        };
    }
}
