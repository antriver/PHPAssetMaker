<?php

namespace TMD;

class SpriteMaker
{

	//Contains css classes to be made into a stylesheet
	private $css = array();

	//Contains the paths of all the images to be combined into one
	private $images = array();

	private $settings;
	private $outcsspath;
	private $outimagepath;
	private $outimageurl;

	//Construct with an object of settings (from the sources.json file)
	public function __construct($name, $settings, $outdir, $outurl, $versionnum)
	{
		$this->settings = $settings;
		$this->makePaths($name, $outdir, $outurl, $versionnum);
	}

	//Create the filenames we're going to use
	public function makePaths($name, $outdir, $outurl, $versionnum)
	{
		$this->outcsspath = $outdir . $name . '-' . $versionnum . '.css';
		$this->outimagepath = $outdir . $name . '-' . $versionnum . '.png';
		$this->outimageurl = $outurl . $name . '-' . $versionnum . '.png';
	}

	//Process the input directories, find the images in them and stick them into CSS rules
	public function makeSprites()
	{

		//Add a css rule for the container class (the class that must be applied to all the sprites)
		$this->css[$this->settings->className] = array(
			'display' => 'inline-block',
			'background-repeat' => 'no-repeat',
			'background-image' => 'url(' . $this->outimageurl . ')'
		);

		if ($this->settings->width) {
			$this->css[$this->settings->className]['width'] = $this->settings->width . 'px';
		}
		if ($this->settings->height) {
			$this->css[$this->settings->className]['height'] = $this->settings->height . 'px';
		}

		$x = 0; //x offset in the image
		$y = 0; //y offset in the page

		//For each input directory...
		foreach ($this->settings->input as $i => $directory) {

			//If this is the first and only directory...
			if (!empty($directory->className)) {
				$this->css[$directory->className] = array();

				//If a class was given for this directory and a width or height was given
				if (!empty($directory->width)) {
					$this->css[$directory->className]['width'] = $directory->width . 'px';
				}
				if (!empty($directory->height)) {
					$this->css[$directory->className]['height'] = $directory->height . 'px';
				}
			}

			//Get the files in the directory
			$files = $this->readDirectory($directory->path);

			//For each file in the directory
			foreach ($files as $filename) {
				//Add it to the array of images to be combined
				$this->images[] = $directory->path . $filename;

				//Get the size of the image
				$imageSize = getimagesize($directory->path . $filename);

				//Create a class for this image
				$className = $this->makeClassNameFromFilename($filename);

				//if there's a class prefix for this directory, prepend it to the class name
				if ($directory->classPrefix) {
					$className = $directory->classPrefix . $className;
				}

				//Add the rule for this image
				$this->css[$className]['background-position'] = '0px -' . $y . 'px';
				if (!empty($directory->includeSize)) {
					$this->css[$className]['width'] = $imageSize[0] . 'px';
					$this->css[$className]['height'] = $imageSize[1] . 'px';
				}

				//Since we're simply building a vertical list, we just add each image's height to the y offset
				$y += $imageSize[1];
			}
		}

		//Build the image
		$this->makeImage();
	}

	//Generate the sprite sheet from the images we've loaded
	private function makeImage()
	{
		//Combine all the images using imagemagicks's montage
		$images = "'" . implode("' '", $this->images) . "'";
		$cmd = 'montage ' . $images . ' -background transparent -mode Concatenate -tile 1x -geometry +0+0 ' . $this->outimagepath;
		echo "\n$cmd\n";
		exec($cmd);

		//Optimise the resulting image with optipng
		$cmd = 'optipng -o6 ' . $this->outimagepath;
		echo "\n$cmd\n";
		exec($cmd);
	}

	//Returns a list of jpg, png and gif files in a directory
	private function readDirectory($path)
	{
		$files = array();

		$dirContents = scandir($path); //Returns an array of the directory contents

		foreach ($dirContents as $filename) {
			//Ingore references to the current and parent folder
			if ($filename == '.' || $filename == '..') {
				continue;
			}

			//Split by "." to find the file extension
			$parts = explode('.', $filename);

			//Extension will be the last item
			$ext = array_pop($parts);

			//Ignore non-images
			if ($ext != 'jpg' && $ext != 'png' && $ext != 'gif') {
				continue;
			}

			$files [] = $filename;
		}
		return $files;
	}

	//Convert a filename into a css friendly class name
	private function makeClassNameFromFilename($filename)
	{
		//Split up by "."
		$parts = explode('.', $filename);

		//Remove the extension
		$ext = array_pop($parts);

		//Rebuild the parts of the filename as a string (with periods removed)
		$className = implode('', $parts);

		//Leave only letters, numbers, underscores and dashes
		$className = preg_replace('/[^a-zA-Z0-9\-_]+/i', '', $className);

		//Special cases for _hover and _active sprites
		if (substr($className, -6) == '_hover') {
			$className = str_replace('_hover', ':hover', $className);
		} elseif (substr($className, -6) == '_active') {
			$className = str_replace('_active', ':active', $className);
		}

		//Prevent duplicate class names
		$i = 1;
		$originalClassName = $className;
		//If this name is already in use
		while (isset($this->css[$className])) {
			++$i;
			$className = $originalClassName . $i; //Try classname1, classname2 etc. until we have an available name
		}

		//Return the new name
		return $className;
	}

	//Build some actual CSS from the array of CSS rules we created
	public function getCSS()
	{
		$css = '';
		foreach ($this->css as $selector => $rules) {
			$css .= ".$selector{";
			foreach ($rules as $property => $value) {
				$css .= $property . ':' . $value . ';';
			}
			$css .= '}';
		}
		return $css;
	}
}
