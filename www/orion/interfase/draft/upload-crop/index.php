<?php

error_reporting (E_ALL);

require_once('../../upload.php');
$upload = new upload();

$upload_dir = "upload_pic"; 				// The directory for the images to be saved in
$upload_path = $upload_dir."/";				// The path to where the image will be saved
$large_image_prefix = "resize_"; 			// The prefix name to large image
$thumb_image_prefix = "thumbnail_";			// The prefix name to the thumb image
$large_image_name = $large_image_prefix . '1';     // New name of the large image (append the timestamp to the filename)
$thumb_image_name = $thumb_image_prefix . '1';     // New name of the thumbnail image (append the timestamp to the filename)

$thumb_photo_exists = '';
$large_photo_exists = '';

$max_width = "500";							// Max width allowed for the large image
$thumb_width = "300";						// Width of thumbnail image
$thumb_height = "150";						// Height of thumbnail image

$image_ext = '';
$error = '';

if (isset($_POST["upload"])) { 
	//Get the file information
	$userfile_name = $_FILES['image']['name'];
	$userfile_tmp = $_FILES['image']['tmp_name'];
	$userfile_size = $_FILES['image']['size'];
	$userfile_type = $_FILES['image']['type'];
	$filename = basename($_FILES['image']['name']);
	$file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
	$file_ext = '.' . $file_ext;

	//Image Locations
	$large_image_location = $upload_path . $large_image_name . $file_ext;
	$thumb_image_location = $upload_path . $thumb_image_name . $file_ext;
	
	//Everything is ok, so we can upload the image.
	if (isset($_FILES['image']['name'])){
		move_uploaded_file($userfile_tmp, $large_image_location);
		chmod($large_image_location, 0777);
		
		$width = $upload->getWidth($large_image_location);
		$height = $upload->getHeight($large_image_location);
		//Scale the image if it is greater than the width set above
		if ($width > $max_width) {
			$scale = $max_width / $width;
			$uploaded = $upload->resizeImage($large_image_location,$width,$height,$scale);
		} else {
			$scale = 1;
			$uploaded = $upload->resizeImage($large_image_location,$width,$height,$scale);
		}

		//Delete the thumbnail file so the user can create a new one
		/*if (file_exists($thumb_image_location)) {
			unlink($thumb_image_location);
		}*/
	}

	if (file_exists($large_image_location)) {
		$thumb_photo_exists = $upload_path . $large_image_name . $file_ext;
		$large_photo_exists = $upload_path . $large_image_name . $file_ext;
	}
}

if (isset($_POST["upload_thumbnail"])) {
	//Get the new coordinates to crop the image.
	$x1 = $_POST["x1"];
	$y1 = $_POST["y1"];
	$x2 = $_POST["x2"];
	$y2 = $_POST["y2"];
	$w = $_POST["w"];
	$h = $_POST["h"];
	$thumb_image_location = $_POST['thumb_image'];
	$large_image_location = $_POST['large_image'];

	//Scale the image to the thumb_width set above
	$scale = $thumb_width/$w;
	$cropped = $upload->resizeThumbnailImage($thumb_image_location, $large_image_location, $w, $h, $x1, $y1, $scale);

	//Reload the page again to view the thumbnail
	header("location:".$_SERVER["PHP_SELF"]);
	exit;
}

//Only display the javacript if an image has been uploaded
if (strlen($large_photo_exists) > 0) {
	$current_large_image_width = $upload->getWidth($large_image_location);
	$current_large_image_height = $upload->getHeight($large_image_location);

}

?>
<!DOCTYPE html>
<html>
<head>
	<title>u-crop</title>
	<link rel="stylesheet" href="//assets.orion.com/default.css?g=" />
<link rel="stylesheet" href="//assets.orion.com/mobile.css?g=" />
<link rel="shortcut icon" href="//orion.com/favicon.ico" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script src="//assets.orion.com/g.js?g=" type="text/javascript"></script>

	<script type="text/javascript">
	function preview(img, selection) { 
		var scaleX = <?php echo $thumb_width; ?> / selection.width; 
		var scaleY = <?php echo $thumb_height; ?> / selection.height; 
		
		$('#thumbnail + div > img').css({ 
			width: Math.round(scaleX * <?php echo $current_large_image_width; ?>) + 'px', 
			height: Math.round(scaleY * <?php echo $current_large_image_height; ?>) + 'px',
			marginLeft: '-' + Math.round(scaleX * selection.x1) + 'px', 
			marginTop: '-' + Math.round(scaleY * selection.y1) + 'px' 
		});

		$('#x1').val(selection.x1);
		$('#y1').val(selection.y1);
		$('#x2').val(selection.x2);
		$('#y2').val(selection.y2);
		$('#w').val(selection.width);
		$('#h').val(selection.height);
	}

	$(function () { 
		$('#save_thumb').click(save_thumb);
	}); 

	$(window).load(function () { 
		$('#thumbnail').imgAreaSelect({ aspectRatio: '1:<?php echo $thumb_height/$thumb_width;?>', onSelectChange: preview, show: true, handles: true, hide: false }); 
	});
	</script>
</head>
<body>
<h1>Photo Upload and Crop</h1>
<?php

if (strlen($large_photo_exists) > 0) {
?>
	<h2>Create Thumbnail</h2>
	<div align="center">
		<img src="<?php echo $large_photo_exists; ?>" style="float: left; margin-right: 10px;" id="thumbnail" alt="Create Thumbnail" />
		<div style="border:1px #e5e5e5 solid; float:left; position:relative; overflow:hidden; width:<?php echo $thumb_width;?>px; height:<?php echo $thumb_height;?>px;">
			<img src="<?php echo $thumb_photo_exists; ?>" style="position: relative;" alt="Thumbnail Preview" />
		</div>
		<br style="clear:both;"/>
		<form name="thumbnail" action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
			<input type="hidden" name="x1" value="" id="x1" />
			<input type="hidden" name="y1" value="" id="y1" />
			<input type="hidden" name="x2" value="" id="x2" />
			<input type="hidden" name="y2" value="" id="y2" />
			<input type="hidden" name="w" value="" id="w" />
			<input type="hidden" name="h" value="" id="h" />
			<input type="hidden" name="thumb_image" value="<?php echo $thumb_image_location; ?>" />
			<input type="hidden" name="large_image" value="<?php echo $large_image_location; ?>" />

			<input type="submit" name="upload_thumbnail" value="Save Thumbnail" id="save_thumb" />
		</form>
	</div>
	<hr />

<?php
} else {
?>
	<h2>Upload Photo</h2>
	<form name="photo" enctype="multipart/form-data" action="<?php echo $_SERVER["PHP_SELF"];?>" method="post">
	Photo <input type="file" name="image" size="30" /> <input type="submit" name="upload" value="Upload" />
	</form>
<?php } ?>

</body>
</html>
