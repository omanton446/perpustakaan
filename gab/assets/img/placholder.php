<?php
header("Content-Type: image/png");
$width = $_GET['w'] ?? 75;
$height = $_GET['h'] ?? 110;
$text = $_GET['text'] ?? 'No Cover';

$img = imagecreatetruecolor($width, $height);
$bg = imagecolorallocate($img, 233, 236, 239); // #e9ecef
$textColor = imagecolorallocate($img, 108, 117, 125); // #6c757d
$borderColor = imagecolorallocate($img, 222, 226, 230); // #dee2e6

imagefilledrectangle($img, 0, 0, $width, $height, $bg);
imagerectangle($img, 0, 0, $width-1, $height-1, $borderColor);

$fontSize = 3;
$textWidth = strlen($text) * imagefontwidth($fontSize);
$textX = ($width - $textWidth) / 2;
$textY = ($height - imagefontheight($fontSize)) / 2;
imagestring($img, $fontSize, $textX, $textY, $text, $textColor);

imagepng($img);
imagedestroy($img);
?>