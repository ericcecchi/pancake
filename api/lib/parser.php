<?php

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

function parse_flat($path,$params=array()) {
	// Returns an array with keys 'meta' (array) and 'content' (string) or false
	if (file_exists($path)) {
		$file = fopen($path, 'r');

		$yaml = '';
		$content = '';
		$line = fgets($file);

		if (preg_match('/^[ \t\/*#@-]*$/', $line)) {
			$line = fgets($file);

			while ((!preg_match('/^[ \t\/*#@-]*$/', $line) | $line == "\n") && !feof($file)) {
				$yaml .= $line;
				$line = fgets($file);
			}
		}
		else {
			if ($params['content'] != 'false') {
				$content .= $line;
			}
		}

		if ($params['content'] != 'false') {
			while (!feof($file)) {
				$content .= fgets($file);
			}
			$content = Markdown(Smartypants($content));
		}

		fclose($file);

		$meta = Spyc::YAMLLoad($yaml);

		$meta['url'] = '/'.preg_replace('/(\.md)$/', '', str_replace(CONTENT_DIR, '', realpath($path)));
		$slug = explode('/', $meta['url']);
		$meta['slug'] = preg_replace('/(\.md)$/', '', end($slug));

		foreach ($meta as $key => $value) {
			if ((!in_array($key, $params['with']) && $params['with'] != array()) | in_array($key, $params['without'])) unset($meta[$key]);
		}

		return array('meta'=> $meta, 'content'=> $content);
	}
	else {
		return false;
	}
}

function save_post($post) {
	if ($post) {
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
}
