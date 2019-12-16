<?php
function get_folder_in_args($argv, $argc) {
	return $argv[1];
}

function get_image($dir)
{
	$imagesArray = [];
	if (is_dir($dir)) {
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if(preg_match('/\.(png)$/', $file)) {
					array_push($imagesArray,$dir."/".$file);
				}
			}
			closedir($dh);
		}
	}
	return $imagesArray;
}

function generate_css($arrayFiles, $argv)
{
	$arrayWidth = array();
	$totalSize = 0;
	$path = $argv[1];
	foreach ($arrayFiles as $filepng)
	{
		$filepng = imagecreatefrompng($filepng);
		$imWidth = imagesx($filepng);
		$totalSize = $totalSize += $imWidth;
		$imHeight = imagesy($filepng);
		array_push($arrayWidth, $imHeight);
	}
	$biggestHeight = max($arrayWidth);
	generator($biggestHeight, $totalSize, $arrayFiles);
}

function generator($biggestHeight, $totalSize, $arrayFiles)
{
	$sprite = imagecreatetruecolor($totalSize, $biggestHeight);
	$png = imagecreatefrompng($arrayFiles[0]);
	$widthFirst = imagesx($png);
	$heightFirst = imagesy($png);
	$im = imagecopy($sprite, $png, 0, 0, 0, 0, $widthFirst, $heightFirst);
	$totalHeight = 0;
	$totalWidth = 0;
	$css = "";
	$xpos = 0;
	foreach ($arrayFiles as $filename)
	{
		$png = imagecreatefrompng($filename);
		$width = imagesx($png);
		$height = imagesy($png);
		$im = imagecopy($sprite, $png, $totalWidth, $totalHeight, 0, 0, $width, $height);
		$totalWidth = $totalWidth + $width;
		$im = imagepng($sprite, "sprite.png");
		$clearPng = pathinfo($filename);
		$imgName = str_replace(".", "", $clearPng['filename']);
		$css .=
".". $imgName . "
{
width: ".$width."px; height: ".$height."px;
background: url('sprite.png') -". $xpos."px 0px;
}\n\n";
	$xpos += $width;
	}
	file_put_contents("style.css", $css);
	echo "done\n";
	return 0;
}

generate_css(get_image(get_folder_in_args($argv, $argc)), $argv);
