<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>Takayanagi Portfolio</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
html, body {margin:0; padding:0; font-family:system-ui, sans-serif; background:#fff; color:#000;}
.header {position:fixed; top:20px; left:20px; font-size:20px; font-weight:bold; z-index:10;}
.gallery {display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:15px; padding:80px 15px 15px 15px;}
.gallery a {position:relative; display:block; overflow:hidden; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,0.15); transition:transform 0.3s, box-shadow 0.3s;}
.gallery a:hover {transform:scale(1.05); box-shadow:0 4px 12px rgba(0,0,0,0.25);}
.gallery img {width:100%; height:100%; object-fit:cover; display:block;}
.overlay {position:absolute; bottom:0; left:0; width:100%; background:rgba(0,0,0,0.5); color:#fff; padding:0.3em; font-size:0.8em; text-align:center; opacity:0; transition:opacity 0.3s;}
.gallery a:hover .overlay {opacity:1;}
</style>
</head>
<body>

<div class="header">Takayanagi Web Design Portfolio</div>

<div class="gallery">
<?php
$sites = [
    'https://takayanagi-gyosei.com/',
    'https://takayanagi-gyosei.com/shako-shomei/',
    'https://takayanagi-gyosei.com/immigration/br/',
    'https://takayanagi-ent.com/'
];

foreach ($sites as $site) {
    $parsed = parse_url($site);
    $domain = $parsed['host'] ?? 'default';
    $path = trim($parsed['path'], '/');
    $path_safe = $path ? str_replace(['/', '?', '&'], '_', $path) : '';

    // タイトル取得（簡易）
    $title = 'No Title';
    $html = @file_get_contents($site);
    if ($html && preg_match('/<title>(.*?)<\/title>/i', $html, $m)) {
        $title = trim($m[1]);
    }

    // fetch_og_live.php に URL を渡す
    $img_src = 'fetch_og_live.php?url=' . urlencode($site);

    echo '<a href="'.htmlspecialchars($site).'" target="_blank">';
    echo '<img src="'.htmlspecialchars($img_src).'" alt="'.htmlspecialchars($title).'">';
    echo '<div class="overlay">'.htmlspecialchars($title).'</div>';
    echo '</a>';
}
?>
</div>

</body>
</html>