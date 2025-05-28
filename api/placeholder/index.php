<?php
// Simple placeholder image generator
header('Content-Type: image/png');

// Get the requested dimensions from the URL
$width = isset($_GET[0]) ? intval($_GET[0]) : 800;
$height = isset($_GET[1]) ? intval($_GET[1]) : 600;

// Create the image
$image = imagecreatetruecolor($width, $height);

// Define colors
$background = imagecolorallocate($image, 245, 245, 250);
$text_color = imagecolorallocate($image, 100, 100, 100);
$border_color = imagecolorallocate($image, 200, 200, 200);

// Fill the background
imagefill($image, 0, 0, $background);

// Draw border
imagerectangle($image, 0, 0, $width-1, $height-1, $border_color);

// Add dimensions as text
$text = "{$width} x {$height}";
$font_size = 5;
$text_width = imagefontwidth($font_size) * strlen($text);
$text_height = imagefontheight($font_size);

$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;

imagestring($image, $font_size, $x, $y, $text, $text_color);

// Output the image
imagepng($image);
imagedestroy($image);
