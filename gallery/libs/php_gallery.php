<?php
# ========================================================================#
#
#  Author:    Vu Khanh Truong
#  Email: vukhanhtruong@gmail.com
#  Version:	 1.0
#  Date:      12-April-2013
#  Purpose:   Generate thumbnail gallery from specific folders
#  Requires : Requires PHP5, GD library, mbstring library, exif library.
#  Note: Windows users must enable both the php_mbstring.dll and php_exif.dll DLL's in php.ini. The php_mbstring.dll DLL must be loaded before the php_exif.dll DLL so adjust your php.ini accordingly.
#  Usage Example:
#		require_once('libs/php_gallery.php');
#		$php_gallery = new PHP_Gallery('./images/', true);
#		$images = $php_gallery->getImages();
#
#  Output:
/*
		Array
		(
		    [1] => Array
		        (
					[src]         => path/to/image
					[thumbnail]   => path/to/thumbnail
					[title]       => Your Title
					[description] => Your Description
					[url]         => http://example.com
		        )

		    [2] => Array
		        (
					[src]         => path/to/image
					[thumbnail]   => path/to/thumbnail
					[title]       => Your Title
					[description] => Your Description
					[url]         => http://example.com
		        )
			...
		)
*/
#
# ========================================================================#
require_once("resize-class.php");
require_once("Encoding.php");

Class PHP_Gallery{
	/**
	 * default file types variable
	 */
	private $_filetypes = array("png", "PNG", "jpg", "JPG", "jpeg", "JPEG", "gif", "GIF");
	/**
	 * default base directory
	 */
	private $_basedir   = './galleries/';
	/**
	 * default thumb directory
	 */
	private $_thumbdir   = '';
	/**
	 * thumbnail option array
	 */
	private $_thumbOptions   = array(250, 250, 'crop');

	/**
	 * mark as create thumbnail
	 */
	private $_createThumb   = true;
	/**
	 * mark as cacheable
	 */
	private $_cache   = false;
	/**
	 * cache file
	 */
	private $cacheFile = "gallery.json";

	/**
	 * constructor method
	 *
	 * @param string base directory
	 * @return void
	 */
	public function __construct($basedir=null, $_createThumb=true)
	{
		if($basedir){
			$this->setBaseDir($basedir);
		}


		if($_createThumb){
			$this->_createThumb = $_createThumb;
			$this->setThumbDir($this->getBaseDir().'thumbnails/');
		}
	}

	/**
	 * Property Get/Set base dir
	 *
	 * @return Array
	 */
	public function setBaseDir($basedir){
		$this->_basedir = $basedir;
	}

	public function getBaseDir(){
		return $this->_basedir;
	}

	/**
	 * Property Get/Set thumb dir
	 *
	 * @return Array
	 */
	public function setThumbDir($thumbdir){
		$this->_thumbdir = $thumbdir;
	}

	public function getThumbDir(){
		return $this->_thumbdir;
	}

	/**
	 * Property Set cache file
	 *
	 * @return Array
	 */
	public function setCache($cache){
		$this->_cache = $cache;
		if(!$cache){
			@unlink($this->_basedir.$this->cacheFile);
		}
	}

	/**
	 * Set option thumbnail
	 *
	 * @param Array($width, $height, $option='auto') - $option have 5 types: exact, portrait, landscape, crop, auto(default)
	 * @return void
	 */
	private function setThumbnailOptions($option)
	{
		if(!empty($option) && count($option) > 3){
			// initial resize
			$newWidth  = (intval($option[0]) > 0) ? intval($option[0]) : $this->thumbs[0];
			$newHeight = (intval($option[1]) > 0) ? intval($option[1]) : $this->thumbs[1];
			$newOption = (intval($option[2]) > 0) ? intval($option[2]) : $this->thumbs[2];

			$this->_thumbOptions = array($newWidth, $newHeight, $newOption);
		}
	}

	/**
	 * Scan directory and return array list of files
	 *
	 * @return Array
	 */
	private function scanDirectory()
	{
		$path		 = $this->_basedir;
		$sortedData  = array();
		$data1       = array();
		$data2       = array();
		foreach(scandir($path) as $file)
		{
			if(!strstr($path, '..'))
			{
				if(is_file($path.$file))
				{
					$file_parts = pathinfo($path.$file);

					if(in_array($file_parts['extension'], $this->_filetypes))
					{
						array_push($data2, $file);
					}
				}
				// else
				// {
				// 	array_push($data1, $file);
				// }
			}
		}
		$sortedData = array_merge($data1, $data2);

		//remove un-neccessary system files
		$sortedData = array_diff($sortedData, array('..', '.', 'Thumbs.db', 'thumbs.db', '.DS_Store', 'thumbs'));

		return $sortedData;
	}


	/*
	* Checks that a value is a valid URL according to http://www.w3.org/Addressing/URL/url-spec.txt
	*
	* The regex checks for the following component parts:
	*
	* - a valid, optional, scheme
	* - a valid ip address OR
	*   a valid domain name as defined by section 2.3.1 of http://www.ietf.org/rfc/rfc1035.txt
	*   with an optional port number
	* - an optional valid path
	* - an optional query string (get parameters)
	* - an optional fragment (anchor tag)
	*
	* @param string $check Value to check
	* @param boolean $strict Require URL to be prefixed by a valid scheme (one of http(s)/ftp(s)/file/news/gopher)
	* @return boolean Success
	*/
	private function validURL($check, $strict = false) {
		$hostname = '(?:[-_a-z0-9][-_a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,})';

		$IPv6 = '((([0-9A-Fa-f]{1,4}:){7}(([0-9A-Fa-f]{1,4})|:))|(([0-9A-Fa-f]{1,4}:){6}';
		$IPv6 .= '(:|((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})';
		$IPv6 .= '|(:[0-9A-Fa-f]{1,4})))|(([0-9A-Fa-f]{1,4}:){5}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})';
		$IPv6 .= '(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:)';
		$IPv6 .= '{4}(:[0-9A-Fa-f]{1,4}){0,1}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2}))';
		$IPv6 .= '{3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){3}(:[0-9A-Fa-f]{1,4}){0,2}';
		$IPv6 .= '((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|';
		$IPv6 .= '((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){2}(:[0-9A-Fa-f]{1,4}){0,3}';
		$IPv6 .= '((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2}))';
		$IPv6 .= '{3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:)(:[0-9A-Fa-f]{1,4})';
		$IPv6 .= '{0,4}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)';
		$IPv6 .= '|((:[0-9A-Fa-f]{1,4}){1,2})))|(:(:[0-9A-Fa-f]{1,4}){0,5}((:((25[0-5]|2[0-4]';
		$IPv6 .= '\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4})';
		$IPv6 .= '{1,2})))|(((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})))(%.+)?';


		$IPv4 = '(?:(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])';

		$validChars = '([' . preg_quote('!"$&\'()*+,-.@_:;=~[]') . '\/0-9a-z\p{L}\p{N}]|(%[0-9a-f]{2}))';
		$regex = '/^(?:(?:https?|ftps?|sftp|file|news|gopher):\/\/)' . (!empty($strict) ? '' : '?') .
			'(?:' . $IPv4 . '|\[' . $IPv6 . '\]|' . $hostname . ')(?::[1-9][0-9]{0,4})?' .
			'(?:\/?|\/' . $validChars . '*)?' .
			'(?:\?' . $validChars . '*)?' .
			'(?:#' . $validChars . '*)?$/iu';

		if (is_string($regex) && preg_match($regex, $check)) {
			return true;
		}
		return false;
	}

   /**
	 * Scan directory and return array list of files
	 *
	 * @return Array
	 */
	public function getImages()
	{
		//read file gallery.json and compare with $img_files. If number of file changed, remove gallery.json. Else json decode this file content
		if($this->_cache && is_file($this->_basedir.$this->cacheFile)){
			$readCacheFile = file_get_contents ($this->_basedir.$this->cacheFile);
			if($readCacheFile){
				//read list of cache file
				$listCacheFiles = unserialize($readCacheFile);
				return $listCacheFiles;
			}
		}

		//read directory and get list of images
		$img_files      = $this->scanDirectory();

		//create thumbnail
		if($this->_createThumb){
			//Create a folder if it doesn't already exist
			if (!is_dir($this->_thumbdir)) {
			    mkdir($this->_thumbdir, 0777);
			}

			foreach ($img_files as $img) {
				if(!is_file($this->_thumbdir.$img)){
					// Initialise / load image
					$resizeObj = new resize($this->_basedir.$img);
					//Set image options
					$resizeObj -> resizeImage($this->_thumbOptions[0], $this->_thumbOptions[1], $this->_thumbOptions[2]);
					// Save image
					$resizeObj -> saveImage($this->_thumbdir.$img, 100);
				}
			}
		}

		//get image information
		$img_infomations = array();
		foreach ($img_files as $idx => $img) {
			$exif = @exif_read_data($this->_basedir.$img, 'IFD0', 0);
			$img_infomations[$idx]['src']       =  $this->_basedir.$img;
			$img_infomations[$idx]['thumbnail']   =  ($this->_thumbdir) ? $this->_thumbdir.$img : $this->_basedir.$img;
			$img_infomations[$idx]['title']       =  (isset($exif['Title'])) ? $exif['Title'] : 'Untitled';
			//$img_infomations[$idx]['subject']     =  (isset($exif['Title'])) ? $exif['Subject'] : '';
			$img_infomations[$idx]['url'] 		  =  '';

			$description						  =	 (isset($exif['Title'])) ? $exif['Comments'] : '';
			if(mb_detect_encoding($description) == 'UTF-8'){
				$img_infomations[$idx]['description'] =  Encoding::toUTF8($description);
			}else{
				$img_infomations[$idx]['description'] =  $description;
			}

			$regex = '~<! (.*?) !>~i';
			preg_match_all($regex, $description, $result, PREG_PATTERN_ORDER);
			foreach ($result as $url) {
				if(empty($url[0]) || !$this->validURL($url[0])){
					continue;
				}
				$img_infomations[$idx]['url'] =  $url[0];
			}
		}

		//caching data by write the img_information to file
		if($this->_cache){
			$fh = fopen($this->_basedir.$this->cacheFile, 'w');
			fwrite($fh, serialize($img_infomations));
			fclose($fh);
		}

		return $img_infomations;
	}

}
?>