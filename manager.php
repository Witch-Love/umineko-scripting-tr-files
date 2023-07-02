<?php

define('CRLF', "\r\n");
define('DS', '/');

$exclude = [
	'.DS_Store',
	'thumbs.db',
	'Thumbs.db'
];

function err($m) {
	echo $m.PHP_EOL;
	die(0);
}

function main($argc, $argv) {
	if ($argc < 2) return;

	switch ($argv[1]) {
		case 'genhash':
			if ($argc < 3) return;
			if (!file_exists($argv[2])) err('No such file '.$argv[2]);

			$hashes = [];
			hashDir($argv[3], $argv[3], $hashes, 'size');
			
			$output = filteredIniCreate($hashes, 'size');

			file_put_contents($argv[2], $output);
			break;
		default:
			err('ERROR');
	}

}

function hashDir($dir, $base, &$map, $type) {
	if (!is_dir($dir)) err('No such directory '.$dir);

	$files = scandir($dir);
	for ($i = 2, $s = sizeof($files); $i < $s; $i++) {
		if (is_dir($dir.DS.$files[$i])) {
			hashDir($dir.DS.$files[$i], $base, $map, $type);
		} else {
			if ($type == 'adler') {
				$hash = hash('adler32', file_get_contents($dir.DS.$files[$i]));
			} else if ($type == 'size') {
				$hash = filesize($dir.DS.$files[$i]);
			} else {
				$hash = md5_file($dir.DS.$files[$i]);
			}
			$map[str_replace($base, '', $dir).DS.$files[$i]] = $hash;
		}
	}
}

function hasIn($haystack, $needle) {
	if (!is_array($needle)) $needle = [$needle];
	foreach ($needle as $query) {
		if (strstr($haystack, $query) !== false) return true;
	}
	return false;
}

function filteredIniCreate($hashes, $mode) {
	global $exclude;

	$output = '[info]'.CRLF;
	$output .= '"game"="UminekoPS3fication*"'.CRLF;
	$output .= '"hash"="'.$mode.'"'.CRLF;
	$output .= '"ver"="20190109-ru"'.CRLF;
	$output .= '"apiver"="2.2.0"'.CRLF;
	$output .= '"date"="ignore"'.CRLF;
	$output .= '[data]'.CRLF;

	foreach ($hashes as $file => $hash) {
		if (!hasIn($file, $exclude) && !strstr($file, 'game.hash')) {
			if ($file[0] == '/') $file = substr($file, 1);
			$output .= '"'.$file.'"="'.$hash.'"'.CRLF; 
		}
	}

	return $output;
}


main($argc, $argv);