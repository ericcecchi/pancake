<?php

class Pancake {

	public function __construct() {
		$this->get_url();
		$this->get_params();
		$this->load_file();
		$this->send_json();
	}

	private function base_url() {
		$this->get_protocol();
		return rtrim(str_replace($this->url, '', $this->protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']), '/');
	}

	private function get_params() {
		$defaults = array(
			'with' => array(),
			'without' => array(),
			'content' => 'true',
			'items' => 'true',
			'sort' => 'date',
			'order' => 'asc',
		);

		foreach($defaults as $key=>$val){
			if(isset($_GET[$key]) && $_GET[$key]) {
				if ($key == 'with' | $key == 'without') $_GET[$key] = explode(',', $_GET[$key]);
				$defaults[$key] = $_GET[$key];
			}
		}
		$this->params = $defaults;
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

		// Get rid of query string
		$request_url = strtok($request_url,'?');
		$script_url = strtok($script_url,'?');
		
		// Get our url path and trim the / of the left and the right
		if($request_url != $script_url) $this->url = trim(preg_replace('/'. str_replace('/', '\/', str_replace('index.php', '', $script_url)) .'/', '', $request_url, 1), '/');
	}

	private function load_file() {
		// Get the file path
		if ($this->url) $file = CONTENT_DIR . $this->url;
		else $file = CONTENT_DIR .'index';

		// Load the file
		if(is_dir($file)) {
			// If it's an index, load the items
			$items = ($this->params['items'] == 'true') ? $this->load_items($file) : array();
			$file = CONTENT_DIR . $this->url .'/index.md';
		}
		else {
			$file .= '.md';
			$items = array();
		}

		if(file_exists($file)) $result = parse_flat($file,$this->params);
		else $result = parse_flat(CONTENT_DIR .'404.md',$this->params);

		$this->items = $items;
		$this->meta = $result['meta'];
		$this->content =  $result['content'];
	}

	private function load_item($entry,$dirname) {
		$entryfile = $dirname.'/'.$entry;
		if(file_exists($entryfile)) {
			$result = parse_flat($entryfile,$this->params);
			if ($result) {
				$slug = preg_replace('/(\.md)$/', '', $entry);
				$a = array(
						'meta' => $result['meta'],
					);
				$a['content'] = $result['content'] ? $result['content'] : '';
				return $a;
			}
		}
	}

	private function load_items($dirname) {
		if ($dir = opendir($dirname)) {
			$items = array();
			while (false !== ($entry = readdir($dir))) {
				if ($entry != "index.md") {
					$entryfile = $dirname.'/'. $entry;
					if (preg_match('/(\.md)$/', $entry)) {
						$items[] = $this->load_item($entry,$dirname);
					}
					elseif (is_dir($entryfile) && $entry != '.' && $entry != '..') {
						$i = $this->load_items($entryfile);
						foreach ($i as $value) {
							$items[] = $value;
						}
					}
				}
			}
		}
		closedir($dir);

		usort($items, function($a, $b) {
			$sort = $this->params['sort'];
			$order = $this->params['order'];
			if (array_key_exists($sort, $b['meta']) && array_key_exists($sort, $a['meta'])) {
				if ($order == 'asc') return ($b['meta'][$sort] > $a['meta'][$sort]) ? -1 : 1;
				else return ($b['meta'][$sort] < $a['meta'][$sort]) ? -1 : 1;
			}
		});

		return $items;
	}

		private function send_json() {
		$json = array(
			'meta' => $this->meta,
		);
		if ($this->content) $json['content'] = $this->content;
		if ($this->items) $json['items'] = $this->items;
		
		header('Content-Type: application/json');
		header('Access-Control-Allow-Origin: *');
		echo json_encode($json);
	}
}
