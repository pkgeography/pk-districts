<?php

define('ABSDIR', __DIR__);

$files = scandir(ABSDIR);

$svgs = array();

foreach ($files as $file) {
	if ( strpos($file, '.svg') )
		$svgs[] = $file;
}

$data = array();

foreach ($svgs as $svg) {
	$content = file_get_contents(ABSDIR . '/' . $svg);
	$xml = simplexml_load_string($content);

	foreach ($xml->attributes() as $parentNode) {
		$nodeName = $parentNode->getName();

		if ($nodeName === 'version' || $nodeName === 'viewBox' || $nodeName === 'style')
			continue;

		$parentNode = (array) $parentNode;
		$d[$nodeName] = trim($parentNode[0]);
	}

	foreach ($xml->children() as $node) {
		$paths = $node->{$node->getName()};

		foreach ($paths as $pathObj) {
			$path = $pathObj->path;
			foreach ($path->attributes() as $key => $value) {
				$value = (array) $value;

				if ($key === 'id') {
					$v = str_replace('DISTRICT_x3D_', '', trim($value[0]));
					$v = str_replace('_x2C_', '', $v);
					$v = explode('PROVINCE_x3D_', $v)[0];
				}
				else {
					$v = trim($value[0]);
				}

				$d[$key] = strtolower($v);
			}
		}
	}	
	$data[] = $d;

}

$output = array();

foreach ($data as $outputData) {
	$output[$outputData['id']][] = $outputData;
}

header('content-type: application/json; charset=utf-8');
echo json_encode($output, JSON_PRETTY_PRINT);