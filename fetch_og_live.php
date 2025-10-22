<?php
$url = $_GET['url'] ?? '';
if (!$url) { http_response_code(400); echo "URL required"; exit; }

// --------------------------
// OG画像取得関数
// --------------------------
function fetch_og_image($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/141.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $html = curl_exec($ch);
    curl_close($ch);

    if (!$html) return false;

    if (preg_match('/<meta\s+(?:property|name)="og:image"\s+content=[\'"]([^\'"]+)[\'"]/i', $html, $m)) {
        $og_image = trim($m[1]);

        if (strpos($og_image, '//') === 0) $og_image = 'https:' . $og_image;
        if (strpos($og_image, '/') === 0) {
            $parsed = parse_url($url);
            $scheme = $parsed['scheme'] ?? 'https';
            $host = $parsed['host'] ?? '';
            $og_image = $scheme . '://' . $host . $og_image;
        }
        return $og_image;
    }

    return false;
}

// --------------------------
// ページ単位とドメイン単位画像決定
// --------------------------
$parsed = parse_url($url);
$domain = $parsed['host'] ?? 'default';
$path = trim($parsed['path'], '/');
$path_safe = $path ? str_replace(['/', '?', '&'], '_', $path) : '';

// ページ単位画像
$manualPageImage   = __DIR__ . "/images/{$domain}" . ($path_safe ? "_{$path_safe}" : "") . ".jpg";
// ドメイン単位画像
$manualDomainImage = __DIR__ . "/images/{$domain}.jpg";
// デフォルト
$defaultImage      = __DIR__ . "/images/default.png";

$imagePath = '';

// --------------------------
// 1. OG画像を取得
$og_image = fetch_og_image($url);
if ($og_image && filter_var($og_image, FILTER_VALIDATE_URL)) {
    $img_data = @file_get_contents($og_image);
    if ($img_data) {
        $tmpFile = tempnam(sys_get_temp_dir(), 'og');
        file_put_contents($tmpFile, $img_data);
        $imagePath = $tmpFile;
    }
}

// --------------------------
// 2. ページ単位画像優先
if ((!$imagePath || !file_exists($imagePath)) && file_exists($manualPageImage)) {
    $imagePath = $manualPageImage;
}

// --------------------------
// 3. ドメイン単位画像
if ((!$imagePath || !file_exists($imagePath)) && file_exists($manualDomainImage)) {
    $imagePath = $manualDomainImage;
}

// --------------------------
// 4. デフォルト画像
if (!$imagePath || !file_exists($imagePath)) {
    $imagePath = $defaultImage;
}

// --------------------------
// GDで正方形サムネイル生成
// --------------------------
$thumbSize = 300;
list($width, $height) = getimagesize($imagePath);
$srcImg = imagecreatefromstring(file_get_contents($imagePath));
$minSide = min($width, $height);
$dstImg = imagecreatetruecolor($thumbSize, $thumbSize);
imagecopyresampled(
    $dstImg,
    $srcImg,
    0, 0,
    intval(($width - $minSide)/2), intval(($height - $minSide)/2),
    $thumbSize, $thumbSize,
    $minSide, $minSide
);

// --------------------------
// 出力
// --------------------------
header('Content-Type: image/jpeg');
imagejpeg($dstImg, null, 90);
imagedestroy($srcImg);
imagedestroy($dstImg);

// 一時ファイル削除
if (isset($tmpFile) && file_exists($tmpFile)) unlink($tmpFile);