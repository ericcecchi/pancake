<?php

require('parser.php');

// Fake a POST
// $_POST = [
// 		'content' => 'Some content',
// 		'title' => 'The Title',
// 		'type' => 'the-type',
// 		'category' => 'category',
// 		'key' => 'value'
// ];

if (save_post($_POST)) {
	header("Location: /posts");
}
else {
	header("Location: /create");
}
