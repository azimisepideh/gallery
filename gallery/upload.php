<?php
  session_start();
  // Simple PHP Upload Script:  http://coursesweb.net/php-mysql/

  $uploadpath = 'gallery/';      // directory to store the uploaded files
  $max_size = 2000;          // maximum file size, in KiloBytes
  $alwidth = 1600;            // maximum allowed width, in pixels
  $alheight = 1200;           // maximum allowed height, in pixels
  $allowtype = array('gif', 'jpg', 'jpeg', 'png');        // allowed extensions

  if(isset($_FILES['fileup']) && strlen($_FILES['fileup']['name']) > 1) {
    $uploadpath = $uploadpath . basename( $_FILES['fileup']['name']);       // gets the file name
    $sepext = explode('.', strtolower($_FILES['fileup']['name']));
    $type = end($sepext);       // gets extension
    list($width, $height) = getimagesize($_FILES['fileup']['tmp_name']);     // gets image width and height
    $err = '';         // to store the errors

    // Checks if the file has allowed type, size, width and height (for images)
    if(!in_array($type, $allowtype)){
      $err .= 'The file: <b>'. $_FILES['fileup']['name']. '</b> not has the allowed extension type.';
      $_SESSION['alert'] = $err;
    }elseif($_FILES['fileup']['size'] > $max_size*1000){
      $err .= '<br/>Maximum file size must be: '. $max_size. ' KB.';
      $_SESSION['alert'] = $err;
    }
    elseif(isset($width) && isset($height) && ($width >= $alwidth || $height >= $alheight))
    {
      $err .= '<br/>The maximum Width x Height must be: '. $alwidth. ' x '. $alheight;
      $_SESSION['alert'] = $err;
    }
    else
    {
      // If no errors, upload the image, else, output the errors
      if(move_uploaded_file($_FILES['fileup']['tmp_name'], $uploadpath))
      {
        $alert =  'File: <b>'. basename( $_FILES['fileup']['name']). '</b> successfully uploaded<br><br>';
        // $alert .=  '<br/>File type: <b>'. $_FILES['fileup']['type'] .'</b>';
        // $alert .=   '<br />Size: <b>'. number_format($_FILES['fileup']['size']/1024, 3, '.', '') .'</b> KB';
        // if(isset($width) && isset($height)) $alert .=   '<br/>Image Width x Height: '. $width. ' x '. $height.'<br>';
        $alert .=   '<a href="index.php" style="color: #092e98; font-weight: bold;"><< BACK TO GALLERY</a><br><br>';
        //$alert .=   '<br/><br/>Image address: <b>http://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['REQUEST_URI']), '\\/').'/'.$uploadpath.'</b>';
        $_SESSION['alert'] = $alert;
        @unlink('gallery/gallery.json');
      }
      else
      {
        $_SESSION['alert'] = '<b>Unable to upload the file.</b>';
      }
    }
  }
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thumbnail Grid with Expanding Preview</title>
  <meta name="description" content="Thumbnail Grid with Expanding Preview" />
  <meta name="keywords" content="thumbnails, grid, preview, google image search, jquery, image grid, expanding, preview, portfolio" />
  <link rel="stylesheet" type="text/css" href="css/default.css" />
  <link rel="stylesheet" type="text/css" href="css/component.css" />
  <script src="js/modernizr.custom.js"></script>
</head>
<body>
  <div class="demo-top clearfix">
    <span class="right">
      <a href="index.php"><strong>< Back To Gallery</strong></a>
    </span>
  </div>
  <div class="container">
    <header class="clearfix">
      <h1>Upload Your Image</h1>
    </header>
    <div class="main">
       <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data">

        <div style="text-align:center">
          <?php if(isset($_SESSION['alert'])) echo $_SESSION['alert'];?>
          Upload File: <input type="file" name="fileup" /><br/>
        </div>
        <div style="text-align:center; margin-top: 20px;">
          <input type="submit" name='submit' value="Upload" class="btn"/>
          <p>Max file size: 2000 Kb</p>
          <p>Allowed extensions: jpg, gif, png</p>
        </div>
      </form>
    </div>
  </div><!-- /container -->

</body>
</html>
<?php session_destroy();?>
