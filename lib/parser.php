<?php

require(__DIR__.'/../config.php');

// Development
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

function slugify($slug) {
	// everything to lower and no spaces begin or end
	$slug = strtolower(trim($slug));
 
	// adding - for spaces and union characters
	$find = array(' ', '&', '\r\n', '\n', '+',',');
	$slug = str_replace($find, '-', $slug);
 
	//delete and replace rest of special chars
	$find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
	$repl = array('', '-', '');
	$slug = preg_replace($find, $repl, $slug);
 
	//return the URL-friendly slug
	return $slug; 
}

function build_path($title,$type=null,$category=null) {

	$filename = slugify($title) . '.md';
	$dir = CONTENT_DIR;
	if ($type) $dir .= slugify($type) . '/';
	if ($category) $dir .= slugify($category) . '/';

	return $dir . $filename;
}

function parse_flat($path) {
	// Returns an array with keys 'meta' (array) and 'content' (string) or false
	if (file_exists($path)) {
		$file = fopen($path, 'r');

		$meta = array();
		$content = '';
		$line = fgets($file);

		if (preg_match('/^[ \t\/*#@-]*$/', $line)) {
			$line = fgets($file);

			while ((!preg_match('/^[ \t\/*#@-]*$/', $line) | $line == "\n") && !feof($file)) {
				$arr = explode(': ', $line);
				if (array_key_exists(0, $arr) && array_key_exists(1, $arr)) $meta[trim($arr[0])] = trim($arr[1]);
				$line = fgets($file);
			}
		}
		else {
			$content .= $line;
		}

		while (!feof($file)) {
			$content .= fgets($file);
		}

		fclose($file);

		return array('meta'=>$meta, 'content'=>$content);
	}
	else {
		return false;
	}
}

function save_post($post) {
	$meta = '';
	$content = $post['content'];

	foreach ($post as $key => $value) {
		if ($key == 'content') continue;
		$meta .= $key . ': ' . $value . "\n"; // key: value
	}

	$filename = slugify($post['title']) . '.md';
	$dir = CONTENT_DIR;
	if (array_key_exists('type', $post)) $dir .= slugify($post['type']) . '/';
	if (array_key_exists('category', $post)) $dir .= slugify($post['category']) . '/';
	if (!(is_dir($dir))) mkdir($dir, 0777, true);

	return file_put_contents($dir . $filename, "---\n" . $meta . "---\n\n" . $content); // /content/type/category/title.md
}
