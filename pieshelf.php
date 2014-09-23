<?php

# This should only be ran as a CLI script
if(PHP_SAPI !== 'cli') {
	die('pieshelf should only be ran as a CLI script! See README.md for usage instructions.');
}

# Imagick / ImageMagick is required at this point
if(!class_exists('Imagick')) {
	l('You need to install PHP ImageMacick support.', STDERR);
	die();
}

# Define variables and their defaults ahead of time for later use
$input = '';
$output = '';
$name = 'Pie Shelf Gallery';
$theme = 'default';
$thumb_height = 200;
$full_height = 1400;

# Let's define our CLI options, and fetch them
$shortopts = 'i:o:n::t::h::H::';
$longopts = array(
	'input:',
	'output:',
	'name::',
	'theme::',
	'thumbnail::',
	'full::'
);
$options = getopt($shortopts, $longopts);

# If input and/or output options are missing, let's output a message
if(empty($options['i']) && empty($options['input'])) {
	l('Please specify input option (directory where your images are located) with --input option.', STDERR);
}
else {
	$input = isset($options['i']) ? $options['i'] : $options['input'];
}

# Do the same for output, our other required option
if(empty($options['o']) && empty($options['output'])) {
	l('Please specify output option (directory where your gallery will be generated) with --output option.', STDERR);
}
else {
	$output = isset($options['o']) ? $options['o'] : $options['output'];
}

# Check for default overwrites
if(!empty($options['n']) || !empty($options['name'])) {
	$name = isset($options['n']) ? $options['n'] : $options['name'];
}

# Theme
if(!empty($options['t']) || !empty($options['theme'])) {
	$theme = isset($options['t']) ? $options['t'] : $options['theme'];
}

# Thumbnail height
if(!empty($options['h']) || !empty($options['thumbnail'])) {
	$thumb_height = isset($options['h']) ? $options['h'] : $options['thumbnail'];
}

# Full size height
if(!empty($options['H']) || !empty($options['full'])) {
	$full_height = isset($options['H']) ? $options['H'] : $options['full'];
}

# If the output doesn't exist let's try creating it
if(!file_exists($output)) {
	if(!mkdir($output, 0777, true)) {
		l('Failed to create output directory, check your permissions.', STDERR);
	}
}

# Final check for both directories
if(!is_readable($input) || !is_dir($input)) {
	l('Could not read the input directory, or it\'s not a directory.', STDERR);
}

if(!is_writable($output) || !is_dir($output)) {
	l('Could not write to the output directory, or it\'s not a directory.', STDERR);
}

# Check if the theme exists
if(!is_dir(dirname(__FILE__) . '/themes/' . $theme) || !file_exists(dirname(__FILE__) . '/themes/' . $theme . '/index.tpl.php')) {
	# Not a valid theme since it doesn't exist, or the index.tpl.php doesn't exist (which is required)
	l('Invalid theme specified.', STDERR);
}

# Make a list of directories to go through (if we have subdirectories)
$directories = array($input);
$subdirectories = glob($input . '/*', GLOB_ONLYDIR);
if(!empty($subdirectories)) {
	$directories = array_merge($directories, $subdirectories);
}

# Setup some of the things for the gallery
if(!file_exists($output . '/thumbs') && !mkdir($output . '/thumbs', 0777, true)) {
	l('Failed to create thumbnail directory, check your permissions.', STDERR);
}

if(!file_exists($output . '/full') && !mkdir($output . '/full', 0777, true)) {
	l('Failed to create full sized image directory, check your permissions.', STDERR);
}

if(!file_exists($output . '/style.css') && !copy(dirname(__FILE__) . '/themes/' . $theme . '/style.css', $output . '/style.css')) {
	l('Failed to copy stylesheet to the output directory, make sure style.css exists in your theme and check your permissions.', STDERR);
}

# Debug output
l('Input: ' . $input);
l('Output: ' . $output);
l('Name: ' . $name);
l('Theme: ' . $theme);
l('Starting the generator!');

# Here we go then


/**
 * Simple output function.
 * @param String $message Message that is being logged / output
 * @param Resource $method Resource that we're using to output the message, defaults to STDOUT
 */
function l($message, $method = STDOUT) {
	# Add some colour, because why not
	if($method === STDERR) {
		$message = "\033[31m" . $message . "\033[0m";
	}
	fwrite($method, date('c') . ': ' . $message . PHP_EOL);

	# If the message is an error, kill the script after outputing it
	if($method === STDERR) {
		exit(1);
	}
}

