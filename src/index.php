<?php


/**
 * Combine all district data from SVG files in `/svg/parts` directory 
 * and converts into JSON format.
 * 
 * Use this file is recommended to automatically combine all districts
 * data in `/svg/parts` directory.
 * 
 * In case any data is updated in `/svg/parts` directory, it is best
 * to run this file to output a updated combined data and then save 
 * that into `/json/districts-data.json`.
 * 
 * This file will automatically exclude the directories and empty files.
 * 
 * @author: Jabran Rafique <hello@jabran.me>
 * @license: MIT License
 *
 *	The MIT License (MIT)
 *
 * Copyright (c) 2014 Jabran Rafique <hello@jabran.me>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


// Define base path
define('ABSDIR', dirname(__DIR__) . '/svg/parts');

// Scane and search SVG parts directory
$files = scandir(ABSDIR);

// Empty array to hold file names
$svgs = array();

// Collect all available and valid files
foreach ($files as $file) {
	if ( strpos($file, '.svg') )
		$svgs[] = $file;
}

// Empty array to hold exporting data
$data = array();


// Loop through list of available files
foreach ($svgs as $svg) {

	// Get data from SVG file
	$content = file_get_contents(ABSDIR . '/' . $svg);

	// Convert to XML
	$xml = simplexml_load_string($content);

	// Loop through XML to extract data
	foreach ($xml->g->children() as $child) {

		// Loop through attributes for basic meta data
		foreach ($child->attributes() as $key => $value) {

			// Clean ID
			if ( $key === 'id' ) {
				$id = (string) $child->attributes()[$key];
				$id = preg_replace('/[0-9]/', '', $id);
				$id = str_replace('__', '', $id);
				$id = trim(strtolower(str_replace('.', '', str_replace(' ', '_', $id))));
			}

			// Clean path data and add to `data` array
			if ( $key === 'd' ) {
				$d = (string) $child->attributes()[$key];
				$d = preg_replace('/([\s\s]+)/', ' ', $d);
				$data[$id]['path'][] = $d;
			}

			// In case of multiple paths, loop through all paths
			// to clean and add to `data` array
			else {
				foreach ($child->children() as $paths) {
					foreach ($paths->attributes() as $k => $v) {
						if ( $k === 'd' ) {
							$v = (string) $v;
							$v = preg_replace('/([\s\s]+)/', ' ', $v);
							$data[$id]['path'][] = $v;
						}
					}
				}
			}
		}

		// Loop through top level attributes to extract meta data
		foreach ($xml->attributes() as $key => $value) {
			if ( $key === 'version' ) continue;
			
			if ( $key === 'id' )
				$data[$id]['meta'][$key] = $id;
			else
				$data[$id]['meta'][$key] = (string) $value;
		}
	}
}

// Setup HTTP response header
header('content-type: application/json; charset=utf-8');

// Export as JSON depending upon available PHP version
if ( version_compare(PHP_VERSION, '5.4.0', '<') )
	echo json_encode($data);
else
	echo json_encode($data, JSON_PRETTY_PRINT);