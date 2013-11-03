<?php

	class assetMaker
	{
		private $settings;
		
		function __construct( $settingsFile )
		{
			$this->loadSettings( $settingsFile );
		}
		
		
		//Load the JSON file with the settings
		private function loadSettings( $settingsFile )
		{
			$settings = @file_get_contents( $settingsFile );
			if ( !$settings )
			{
				throw new Exception('Settings file does not exist or cold not be read: '.$settingsFile);
			}

			$settings = @json_decode($settings);
			if ( !is_object($settings) )
			{
				throw new Exception('Settings file is not valid JSON: '.$settingsFile);
			}
			
			$this->settings = $settings;
		}
		
		
		function makeAll()
		{
			exec('rm -rf '.$this->settings->outdir.'*');
			
			foreach ( $this->settings->sources as $sourceName => $source )
			{
				$this->make($sourceName);
			}
		}
		
		
		function make( $sourceName )
		{
			if ( !isset($this->settings->sources->{$sourceName}) )
			{
				throw new Exception('Unknown source: '.$sourceName);
			}
			
			$source = $this->settings->sources->{$sourceName};
			
			if ( count($source->files) < 1 )
			{
				throw new Exception('Source has no files');
			}
			
			$version = date('YmdHis');
			
			switch( $source->type )
			{
				case 'css':
					$content = '';
					foreach ( $source->files as $file )
					{
						$fileContent = $this->readFile($file->path);
						if ( $file->minify )
						{
							$content .= $this->css($fileContent).' ';
						}
						else
						{
							$content .= $fileContent.' ';
						}
					}
					$content = rtrim($content);
					$outputFilename = $sourceName.'-'.$version.'.css';
					$ext = 'css';
				break;
				
				case 'js':
					$content = '';
					foreach ( $source->files as $file )
					{
						$fileContent = $this->readFile($file->path);
						if ( $file->minify )
						{
							$content .= $this->js($fileContent).' ';
						}
						else
						{
							$content .= $fileContent.' ';
						}
					}
					$content = rtrim($content);
					$outputFilename = $sourceName.'-'.$version.'.js';
					$ext = 'js';
				break;
			}
			
			//Save it
			$this->saveFile( $outputFilename , $content );
			
			//Set latest version
			$this->updateVersionURL( $sourceName , $version , $ext );
			
			//Return it
			return $content;
		}
		
		//Returns the contents of a file
		private function readFile( $path )
		{
			if ( !file_exists($path) )
			{
				throw new Exception('Unable to read source file: '.$path);
			}
			return file_get_contents($path);
		}
	
		//Minify a string containing CSS
		private function css( $content )
		{
			require_once __DIR__.'/thirdparty/cssmin.php';	
			return CssMin::minify( $content );
		}
		
		//Minify a string contining javascript
		private function js( $content )
		{
			require_once __DIR__.'/thirdparty/jshrink.php';	
			return \JShrink\Minifier::minify( $content );
		}
		
		//Save the contents of a file to the output directory
		private function saveFile( $filename , $contents )
		{
			$path = $this->settings->outdir.$filename;
			
			$file = fopen($path,'w+');
			if ( !$file )
			{
				throw new Exception('Unable to open output file for writing: '.$path);
			}
			fwrite($file,$contents);
			fclose($file);
		}
		
		private function updateVersionURL( $sourceName , $version , $ext )
		{
			if ( file_exists($this->settings->versionfile) )
			{
				$versions = include $this->settings->versionfile;
			}
			
			if ( !is_array($versions) )
			{
				$versions = array();
			}
			
			$versions[$sourceName] = $this->settings->outurl.$sourceName.'-'.$version.'.'.$ext;
			
			$output = '<?php return '.var_export($versions,true).'; ?>';
			
			file_put_contents($this->settings->versionfile, $output);
		}
	
	}

?>