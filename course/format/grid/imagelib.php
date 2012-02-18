<?php
/*
 * * File: imagelib.php
 * * Author: Paul Krix
 * * Image resizing based heavily on SimpleImage.php. Attribution below.
 * * 
 * * File: SimpleImage.php
 * * Author: Simon Jarvis
 * * Copyright: 2006 Simon Jarvis
 * * Date: 08/11/06
 * * Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
 * * 
 * * This program is free software; you can redistribute it and/or 
 * * modify it under the terms of the GNU General Public License 
 * * as published by the Free Software Foundation; either version 2 
 * * of the License, or (at your option) any later version.
 * * 
 * * This program is distributed in the hope that it will be useful, 
 * * but WITHOUT ANY WARRANTY; without even the implied warranty of 
 * * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
 * * GNU General Public License for more details: 
 * * http://www.gnu.org/licenses/gpl.html
 * *
 * */
 
 function validate_upload($upload_name) {
 
 	$whitelist = array('jpg', 'png', 'gif', 'jpeg'); 	// Allowed file extensions
	$backlist = array('php', 'php3', 'php4', 'phtml','exe'); // Restrict file extensions
	$valid_chars_regex = 'A-Za-z0-9_-\s. ';				// Characters allowed in the file name (in a Regular Expression format)
	$file_name = '';
	$file_extension = '';
	$mime_type = ''; 
 
	// Validate the upload
	if (!isset($_FILES[$upload_name])) {
		HandleError('No upload found in \$_FILES for ' . $upload_name);
		return false;
	} else if (isset($_FILES[$upload_name]['error']) && $_FILES[$upload_name]['error'] != 0) {
		HandleError($uploadErrors[$_FILES[$upload_name]['error']]);
		return false;
	} else if (!isset($_FILES[$upload_name]['tmp_name']) || !@is_uploaded_file($_FILES[$upload_name]['tmp_name'])) {
		HandleError('Upload failed is_uploaded_file test.');
		return false;
	} else if (!isset($_FILES[$upload_name]['name'])) {
		HandleError('File has no name.'); 
		return false;		
	}
// Validate its a MIME Images (Take note that not all MIME is the same across different browser, especially when its zip file)
	$mime_type = $_FILES[$upload_name]['type'];	
	if(!eregi('image/', $mime_type)) {
		HandleError('Please upload a valid file!');
		return false;
	}	

// Validate that it is an image
	$imageinfo = getimagesize($_FILES[$upload_name]['tmp_name']);
	if($imageinfo['mime'] != 'image/gif' && $imageinfo['mime'] != 'image/jpeg' && $imageinfo['mime'] != 'image/png' && isset($imageinfo)) {
		HandleError('Sorry, we only accept GIF and JPEG images');
		return false;		
	}

// Validate file name (for our purposes we'll just remove invalid characters)
	$file_name = preg_replace('/[^'.$valid_chars_regex.']|\.+$/i', '', strtolower(basename($_FILES[$upload_name]['name'])));
	if (strlen($file_name) == 0) {
		HandleError('Invalid file name');
		return false;		
	}

// Validate file extension
	if(!in_array(end(explode('.', $file_name)), $whitelist)) {
		HandleError('Invalid file extension');
		return false;
	}
	if(in_array(end(explode('.', $file_name)), $backlist)) {
		HandleError('Invalid file extension');
		return false;
	}
 	return true;
 }
 

function HandleError($message) {
	//just outputs the message at the moment. Here for future convenience
	echo "Error: " . $message;
}
 
class ImageFunctions {
	   
	   var $image;
	   var $image_type;
	    
	   function set_image($image, $image_type) {
	   	   $this->image = $image;
	   	   $this->image_type = $image_type;
	   }
	   
	   function get_image() {
	   	   return $this->image;
	   }
	    
	   function load($filename) {
		   $image_info = getimagesize($filename);
		   $this->image_type = $image_info[2];
		   if( $this->image_type == IMAGETYPE_JPEG ) {
			   $this->image = imagecreatefromjpeg($filename);
		   } elseif( $this->image_type == IMAGETYPE_GIF ) {
		           $this->image = imagecreatefromgif($filename);
		   } elseif( $this->image_type == IMAGETYPE_PNG ) {
		           $this->image = imagecreatefrompng($filename);
		   }
	   }
	   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
	   	if( $image_type == IMAGETYPE_JPEG ) {
			imagejpeg($this->image,$filename,$compression);
	        } elseif( $image_type == IMAGETYPE_GIF ) {
	                imagegif($this->image,$filename);         
	        } elseif( $image_type == IMAGETYPE_PNG ) {
	                imagepng($this->image,$filename);
	        }   
	        if( $permissions != null) {
	                chmod($filename,$permissions);
	        }
	   }
	   function output($image_type=IMAGETYPE_JPEG) {
	        if( $image_type == IMAGETYPE_JPEG ) {
		        imagejpeg($this->image);
		} elseif( $image_type == IMAGETYPE_GIF ) {
		        imagegif($this->image);         
		} elseif( $image_type == IMAGETYPE_PNG ) {
			imagepng($this->image);
		}   
	   }
	   function getWidth() {
		return imagesx($this->image);
	   }
	   function getHeight() {
	        return imagesy($this->image);
	   }
	   function resizeToHeight($height) {
	        $ratio = $height / $this->getHeight();
	        $width = $this->getWidth() * $ratio;
	        $this->resize($width,$height);
	   }
	   function resizeToWidth($width) {
	        $ratio = $width / $this->getWidth();
	        $height = $this->getheight() * $ratio;
	        $this->resize($width,$height);
	   }
	   function scale($scale) {
	        $width = $this->getWidth() * $scale/100;
	        $height = $this->getheight() * $scale/100; 
	        $this->resize($width,$height);
	   }
	   function resize($width,$height) {
	        $new_image = imagecreatetruecolor($width, $height);
	        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
	        $this->image = $new_image;   
	   }      
	   function cropCenterHeight($height) {
		$width = $this->getWidth();
		$srcOffset = $this->getHeight()/2 - $height/2;
		$new_image = imagecreatetruecolor($width, $height);
	        imagecopyresampled($new_image, $this->image, 0, 0, 0, $srcOffset, $width, $height, $width, $height);
	        $this->image = $new_image;   
	   }
	   function cropCenterWidth($width) {
		$height = $this->getHeight();
		$srcOffset = $this->getWidth()/2 - $width/2;
		$new_image = imagecreatetruecolor($width, $height);
	        imagecopyresampled($new_image, $this->image, 0, 0, $srcOffset, 0, $width, $height, $width, $height);
	        $this->image = $new_image;   
	   }
	   function getSmallestDim() {
		$width = $this->getWidth();
		$height = $this->getHeight();
		if ($width < $height) {
			return $width;
		}
		return $height;
	   }
	   function cropSmallestDim() {
		$width = $this->getWidth();
		$height = $this->getHeight();
		$yOffset = 0;
		$xOffset = 0;
		$smallestDim = 0;
		if ($width < $height) {
			$smallestDim = $width;
			$yOffset = $height/2 - $smallestDim/2;
		} else {
			$smallestDim = $height;
			$xOffset = $width/2 - $smallestDim/2;
		}
		$new_image = imagecreatetruecolor($smallestDim, $smallestDim);
	        imagecopyresampled($new_image, $this->image, 0, 0, $xOffset, $yOffset, $smallestDim, $smallestDim, $smallestDim, $smallestDim);
	        $this->image = $new_image;   
	   }

	   function resizeAndCrop($width, $height) {
	   	$ratio = $width / $height;	   
		$curWidth = $this->getWidth();
	   	$curHeight = $this->getHeight();
	   	$curRatio = $curWidth / $curHeight;
	   	if($curRatio < $ratio) {
		   	$this->resizeToWidth($width);
		   	$this->cropCenterHeight($height);
	   	} else {
	   		$this->resizeToHeight($height);
		   	$this->cropCenterWidth($width);
		}		   
	   }
	   function resizeNormalImage($width, $height) {
	   		$curWidth = $this->getWidth();
	   		$curHeight = $this->getHeight();
	   		$diffWidth = $curWidth - $width;
	   		$diffHeight = $curHeight - $height;

	   		if($diffWidth > 0 || $diffHeight > 0) {
		   		if($diffWidth >= $diffHeight) {
		   			$this->resizeToWidth($width);
		   		} else {	
		   			$this->resizeToHeight($height);
		   		}
		   	}
	   }
}
?>
