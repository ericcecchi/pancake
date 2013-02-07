<?php

class Pancake {

	public function __construct() {
		$this->get_url();
		$this->get_config();
		$this->load_file();
		$this->load_html();
	}

	private function base_url() {
		global $config;
		if(isset($config['base_url']) && $config['base_url']) return $config['base_url'];

		$this->get_protocol();
		return rtrim(str_replace($this->url, '', $this->protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']), '/');
	}

	private function get_config() {
		global $config;

		$defaults = array(
			'site_title' => 'Pancake',
			'base_url' => $this->base_url(),
			'enable_cache' => false
		);

		foreach($defaults as $key=>$val){
			if(isset($config[$key]) && $config[$key]) $defaults[$key] = $config[$key];
		}

		$this->settings = $defaults;
	}

	private function get_protocol() {
		preg_match("|^HTTP[S]?|is",$_SERVER['SERVER_PROTOCOL'],$m);
		$this->protocol = strtolower($m[0]);
	}

	private function get_url() {
				// Get request url and script url
		$this->url = '';
		$request_url = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '';
		$script_url  = (isset($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : '';
			
		// Get our url path and trim the / of the left and the right
		if($request_url != $script_url) $this->url = trim(preg_replace('/'. str_replace('/', '\/', str_replace('index.php', '', $script_url)) .'/', '', $request_url, 1), '/');
	}

	private function load_file() {
		// Get the file path
		if ($this->url) $file = strtolower(CONTENT_DIR . $this->url);
		else $file = CONTENT_DIR .'index';

		// Load the file
		if(is_dir($file)) {
			// If it's an index, load the items
			if ($file = opendir($file)) {
				$items = array();
				while (false !== ($entry = readdir($file))) {
					if (preg_match('/(\.md)$/', $entry)  && $entry != "index.md") {
						$entryfile = CONTENT_DIR . '/'.$this->url.'/'. $entry;
						if(file_exists($entryfile)) {
							$result = parse_flat($entryfile);
							if ($result) {
								$title = preg_replace('/(\.md)$/', '', $entry);
								$items[] = array(
										'url' => '/'.$this->url.'/'.$title,
										'meta' => $result['meta'],
										'content' => Markdown($result['content'])
									);
							}
						}
					}
				}
				closedir($file);
			}
			$file = CONTENT_DIR . $this->url .'/index.md';
		}
		else {
			$file .= '.md';
			$items = array();
		}

		if(file_exists($file)) $result = parse_flat($file);
		else $result = parse_flat(CONTENT_DIR .'404.md');

		$this->items = $items;
		$this->meta = $result['meta'];
		$this->content =  Markdown($result['content']);
	}

	private function load_html() {
		$env = array('autoescape' => false);
		if($this->settings['enable_cache']) $env['cache'] = CACHE_DIR;
		
		$loader = new Twig_Loader_Filesystem(LAYOUT_DIR);
		$twig = new Twig_Environment($loader, $env);

		// Look for /layout/type.html
		if (array_key_exists('type', $this->meta) && file_exists(LAYOUT_DIR.'/'.slugify($this->meta['type']).'.html')) {
			$template = slugify($this->meta['type']).'.html';
		}
		else {
			$template = 'default.html';
		}

		echo $twig->render($template, array(
			'settings' => $this->settings,
			'base_dir' => rtrim(ROOT_DIR, '/'),
			'base_url' => $this->settings['base_url'],
			'layout_dir' => LAYOUT_DIR,
			'layout_url' => '/'. basename(LAYOUT_DIR),
			'site_title' => $this->settings['site_title'],
			'meta' => $this->meta,
			'content' => $this->content,
			'items' => $this->items
		));
	}
}
