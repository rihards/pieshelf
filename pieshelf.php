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
$default_thumb_height = $thumb_height = 200;
$full_height = false;
$force = false;

# Let's define our CLI options, and fetch them
$shortopts = 'i:o:n::t::h::H::f::';
$longopts = array(
	'input:',
	'output:',
	'name::',
	'theme::',
	'thumbnail::',
	'full::',
	'force::',
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
	if(!is_numeric($thumb_height)) {
		l('Invalid thumbnail height specified, setting it to default value of ' . $default_thumb_height . '.');
		$thumb_height = $default_thumb_height;
	}
	else {
		$thumb_height = intval($thumb_height);
	}
}

# Full size height
if(!empty($options['H']) || !empty($options['full'])) {
	$full_height = isset($options['H']) ? $options['H'] : $options['full'];
	if(!is_numeric($full_height)) {
		l('Invalid full height specified, setting it to default value of false.');
		$full_height = false;
	}
	else {
		$full_height = intval($full_height);
	}
}

# Force option
if(!empty($options['f']) || !empty($options['force'])) {
	$force = isset($options['f']) ? $options['f'] : $options['force'];
	if($force == '1' || strtolower($force[0]) == 'y') {
		$force = true;
	}
	else {
		# Some funky value, let's set it back to false
		$force = false;
	}
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

# Stuff for our index template
$_name =  $name;
$_directories = array();
$_images_processed = 0;

# Here we go then, let's do the work then
foreach($directories as $key => $dir) {
	l('Checking ' . $dir . ' for images.');
	$image_files = glob($dir . '/{*.jpg,*.JPG,*.jpeg,*.JPEG,*.png,*.PNG,*.gif,*.GIF}', GLOB_BRACE);

	# Glob can return false as well, but luckily empty() checks that too
	if(!empty($image_files)) {
		# Just some groundwork here
		$_directories[$key] = array('images' => array());

		foreach($image_files as $image_key => $image_file) {

			# File name stuff
			$file_parts = pathinfo($image_file);
			$filename = $file_parts['basename'];

			# By md5'ing the new file name, we attempt to prevent two or more identically named files from subdirectories colliding in the new directories
			$new_filename = md5($image_file) . '.' . $file_parts['extension'];

			# Check if we have generated a thumbnail for this image already
			if(file_exists($output . '/thumbs/' . $new_filename) && $force === false) {
				l('Thumbnail already exists, skipping: ' . $file_parts['basename']);
				continue;
			}

			# Load the image
			$image = new Imagick($image_file);
			if(empty($image)) {
				# This should be an error, but we don't want to fail the whole script because we couldn't load this one image
				l('Failed to load image: ' . $image_file);
				continue;
			}

			# Some basic image information
			$orientation = $image->getImageOrientation();

			# Fix the orientation
			switch($orientation) {
				case imagick::ORIENTATION_BOTTOMRIGHT:
					$image->rotateimage("#000", 180); // rotate 180 degrees
				break;

				case imagick::ORIENTATION_RIGHTTOP:
					$image->rotateimage("#000", 90); // rotate 90 degrees CW
				break;

				case imagick::ORIENTATION_LEFTBOTTOM:
					$image->rotateimage("#000", -90); // rotate 90 degrees CCW
				break;
			}

			# If no full size was specified then we just copy the files
			l('Generating full size for: ' . $image_file);
			if($full_height === false) {
				copy($image_file, $output . '/full/' . $new_filename);
			}
			else {
				$image->thumbnailImage(0, $full_height);
				$image->writeImage($output . '/full/' . $new_filename);
			}

			# Thumbnail it
			l('Generating thumbnail for: ' . $image_file);
			$image->thumbnailImage(0, $thumb_height);
			$image->writeImage($output . '/thumbs/' . $new_filename);

			# Will be used later in a check whether we need to regenerate the index.html file
			# Can also be used for debugging and what not purposes of course as well
			$_images_processed++;

			# Fetch these to add them to the image information
			$thumbnail_geometry = $image->getImageGeometry();
			$thumbnail_width = $image->getImageWidth();
			$thumbnail_height = $image->getImageHeight();

			# Assign all the image info to the _directories array
			$image_info = array(
				'full_url' => 'full/' . $new_filename,
				'thumbnail_url' => 'thumbs/' . $new_filename,
				'with' => $thumbnail_width,
				'height' => $thumbnail_height,
				'alt' => $filename,
			);
			$_directories[$key]['images'][$image_key] = $image_info;
		}
	}
	else {
		l('No images found.');
	}
}

l('Processed ' . $_images_processed . ' images.');

# Now that we've processed the timages it's time to create the index.html
if(!file_exists($output . '/index.html') || $force === true || $_images_processed > 0) {
	l('Creating the index.html file.');
	ob_start();
	require dirname(__FILE__) . '/themes/' . $theme . '/index.tpl.php';
	$html = ob_get_contents();
	ob_end_clean();
	file_put_contents($output . '/index.html', $html);
}
else {
	l('Skipping generating index.html, no changes detected, and not forced.');
}

# All done, enjoy some green colour!
l("\033[32m" . 'All done, enjoy your gallery!' . "\033[0m");

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

