**This project has been abandoned. You should probably use something like Grunt/Gulp instead.**

PHPAssetMaker
=============

A PHP tool to generate combined and minfied CSS, CSS Sprites, and Javascript

## How it works
1. Specify your source files in settings.json
2. Run the make.php script from the command line
3. The sources get combined and minified into a single output file. The URL to this file is saved in a 'versions' file.
4. Include the versions file in your pages and use this URL to make sure your visitors always get the latest version.

## Usage
 
### Settings (settings.json.template)

First, copy the `settings.json.template` file to the directory that contains the `assetmaker` directory and rename it to `assetmaker-settings.json`.

![Where to put the settings file](http://img.ctrlv.in/img/52d9f7bac7b72.png)

Then edit the assetmaker-settings.json file with your settings. This should be the only file you need to edit. See the included sample file to help understand. Here's a rundown of the settings:

* `outdir` is the directory where the final combined, minified etc. files will be saved. Specify a full path to the directory. e.g. **/home/ctrlv/assets/generated/**. Make sure this directory is writable by the user the script runs as.
* `outurl` should be the public facing URL to access the files in `outdir`. Specify a full URL. e.g. **http://assets.ctrlv.in/generated/**
* `versionfile` should be the path to a file you want the latest URLs saved in. (More on this below). Make sure this file is writable by the user the script runs as.
* `sources` is an array of the different files you want to combine.
  * `nameofyoursource` something to uniquely identify each source. Used in the resulting filename.
    * `type` can currently be one of **css**, **js**, or **sprites**

    ------
    For CSS or Javascript

    * `input` an array of the source files you want combined into one
      
      ------
      
      * `path` path to the file
      * `minify` **true** or **false** if you want the source CSS or Javascript file minified
      
      ------
      
      * `sprites` if you're creating a CSS file, you can also include in it some sprites generated with this maker. Give the name of the sprite source (from this file) here and those sprites will be included in your output CSS too. But note that you must define the source for the sprites **before** this source that includes it.
         
    ------
    For sprites
       
    * `className` the class that will be used on every sprite (to set the background image etc.)
    * `width` (optional) a width that will be applied to every element with `className`. Useful if all your images are the same size, instead of the size for every image being specified.
    * `height` (optional) same as `width`
    * `input` an array of directories containing the images you want to be combined into one sprite image
      * `path` path to a directory containing the images
      * `className` (optional) a class to be applied to all the images in this directory only
      * `width` (optional) a width that will be applied to every element with this directory's `className`. Useful if all the images in this directory are the same size, instead of the size for every image being specified.
      * `height` (optional) same as `width`
      * `classPrefix` (optional) a string to begin the class name of each of the images in this directory. e.g. **icon-**
      * `includeSize` **true** or **false** if the size of each image should be specified in the resulting CSS
      
Make sure your input files and directories are readable by the user the script runs as.
          
### Version File

This is useful to ensure your visitors always use the latest version of your scripts (a.k.a for cache-busting).

When you run the asset maker, as well as producing your asset file(s) it also produces a php file containg a single array of the latest URLs to your assets. An example of this file looks like this:

```php
<?php return array (
  'iconsprites' => 'http://assets.ctrlv.in/generated/iconsprites-20131103152640.css',
  'wwwstyle' => 'http://assets.ctrlv.in/generated/wwwstyle-20131103185351.css',
  'wwwjs' => 'http://assets.ctrlv.in/generated/wwwjs-20131103155424.js'
); ?>
```

You can use these URLs in your project like this:

```php
<?php
 $assets = include '/path/to/version/file.php';
?>
<html>
<head>
  <link rel="stylesheet" href="<?php echo $assets['wwwstyle']?>" />
  <script type="text/javascript" src="<?php echo $assets['wwwjs']?>"></script>
```

This means you don't have to worry about upading your URLs every time you change the file. Your page will always point to the latest version.

### Running The Script

```
$ cd /path/to/assetmaker
$ php make.php nameofsource
```
or to make everything at once
```
$ php make.php all
```
