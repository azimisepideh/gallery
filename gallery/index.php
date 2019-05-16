<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>sepideh azimi</title>
		<meta name="description" content="Thumbnail Grid with Expanding Preview" />
		<meta name="keywords" content="thumbnails, grid, preview, google image search, jquery, image grid, expanding, preview, portfolio" />
		<link rel="stylesheet" type="text/css" href="css/default.css" />
		<link rel="stylesheet" type="text/css" href="css/component.css" />
		<script src="js/modernizr.custom.js"></script>
	</head>
	<body>
		<div class="demo-top clearfix">
			<span class="right">
				<a href="upload.php"><strong>+ Try Upload Your Image</strong></a>
			</span>
		</div>
		<div class="container">
			<header class="clearfix">
				<h1>Thumbnail Grid <span>with Expanding Preview</span></h1>
			</header>
			<div class="main">
				<ul id="og-grid" class="og-grid">
				<?php
					require_once('libs/php_gallery.php');
					$php_gallery = new PHP_Gallery('./gallery/');
					//$php_gallery->setCache(true);
					$images = $php_gallery->getImages();
					foreach ($images as $image) :
				?>
					<li>
						<a href="<?php echo $image['url'];?>" data-largesrc="<?php echo $image['src'];?>" data-title="<?php echo $image['title'];?>"
							data-description="<?php echo $image['description'];?>">
							<img src="<?php echo $image['thumbnail'];?>" alt="<?php echo $image['title'];?>"/>
						</a>
					</li>
				<?php
					endforeach;
				?>
				</ul>
				<p><a href="#" alt="">Thumbnail Grid by Sepideh Azimi</a></p>
			</div>
		</div><!-- /container -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="js/grid.js"></script>
		<script>
			$(function() {
				Grid.init();
			});
		</script>
	</body>
</html>