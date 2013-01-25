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
			'theme' => 'default',
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
		if(is_dir($file)) $file = CONTENT_DIR . $this->url .'/index.md';
		else $file .= '.md';

		if(file_exists($file)) $result = parse_flat($file);
		else $result = parse_flat(CONTENT_DIR .'404.md');

		$this->meta = $result['meta'];
		$this->content =  Markdown($result['content']);
	}

	private function load_html() {
		$env = array('autoescape' => false);
		if($this->settings['enable_cache']) $env['cache'] = CACHE_DIR;
		
		Twig_Autoloader::register();
		$loader = new Twig_Loader_Filesystem(THEMES_DIR . $this->settings['theme']);
		$twig = new Twig_Environment($loader, $env);

		if (array_key_exists('type', $this->meta) && file_exists(THEMES_DIR.'/'.$this->settings['theme'].'/'.slugify($this->meta['type']).'.html')) {
			$template = slugify($this->meta['type']).'.html';
		}
		else {
			$template = 'default.html';
		}

		echo $twig->render($template, array(
			'config' => $this->settings,
			'base_dir' => rtrim(ROOT_DIR, '/'),
			'base_url' => $this->settings['base_url'],
			'theme_dir' => THEMES_DIR . $this->settings['theme'],
			'theme_url' => $this->settings['base_url'] .'/'. basename(THEMES_DIR) .'/'. $this->settings['theme'],
			'site_title' => $this->settings['site_title'],
			'meta' => $this->meta,
			'content' => $this->content
		));
	}
}
