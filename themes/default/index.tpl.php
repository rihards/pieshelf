<?php

/**
 * Creating your own themes
 *
 * Basically anything goes really, you have the entirity of PHP to your disposal.
 * 
 * These variables could come in handy though:
 *   $_directories - contains all the directors and subdirectories, which in turn contain all the images and their info
 *   $_name - name of the gallery that can be specified by the user
 */

if(PHP_SAPI !== 'cli') {
	die('pieshelf should only be ran as a CLI script! See README.md for usage instructions.');
}
?><!DOCTYPE html>
	<head>
		<title><?php echo $_name; ?></title>
		<meta name="robots" content="ALL">
		<meta charset="UTF-8">
		<meta content="width=device-width; initial-scale=1.0; maximum-scale=1.0;" name="viewport">
		<link href="style.css" rel="stylesheet" type="text/css">
		<meta name="generator" content="pieshelf">
	</head>
	<body>
	<?php
	if(!empty($_directories)) {
		foreach($_directories as $_directory) {
			?>
				<article class="directory">
					<ul class="images">
					<?php
					foreach ($_directory['images'] as $_image) {
					?>
						<li>
							<a href="<?php echo $_image['full_url']; ?>">
								<img src="<?php echo $_image['thumbnail_url']; ?>" alt="<?php echo $_image['alt']; ?>">
							</a>
						</li>
					<?php
					}
					?>
					</ul>
				</article>
			<?php
		}
	}
	?>
	</body>
</html>
