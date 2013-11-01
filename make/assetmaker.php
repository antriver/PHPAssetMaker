<?php

	class assetMaker
	{
		private $sources;
		
		function __construct()
		{
			$this->sources = $this->loadSources();
		}
		
		function make( $what , $version=1 )
		{
			if ( !isset($this->sources->{$what}) )
			{
				throw new Exception('Unknown source '.$what);
			}
			
			$source = $this->sources->{$what};
			
			if ( count($source->files) < 1 )
			{
				throw new Exception('Source has no files');
			}
			
			$content = $this->readFiles($source->files);
			
			switch( $source->type )
			{
				case 'css':
					$content = $this->css($content);
					$cacheFilename = $version.'.css';
				break;
				
				case 'js':
					$content = $this->js($content);
					$cacheFilename = $version.'.js';
				break;
			}
			
			$content = '/* Generated at '.date('r').' */ '.$content;
			
			//Save it
			$this->save( $what , $cacheFilename , $content );
			
			//Return it
			return $content;
		}
		
		//Load the JSON file with the sources
		private function loadSources()
		{
			$sources = @file_get_contents(__DIR__.'/sources.json');
			if ( !$sources )
			{
				throw new Exception('sources.json file does not exist or cold not be read.');
			}

			$sources = @json_decode($sources);
			if ( !is_object($sources) )
			{
				throw new Exception('sources.json file is not valid JSON.');
			}
			
			return $sources;
		}
		
		private function readFiles( array $files )
		{
			$content = '';
			foreach ( $files as $path )
			{
				$content .= file_get_contents($path).' ';
			}
			return $content;
		}
	
		private function css( $content )
		{
			header('Content-Type: text/css');
			
			require_once __DIR__.'/thirdparty/cssmin.php';
			
			return CssMin::minify( $content );
		}
		
		private function js( $content )
		{
			header('Content-Type: application/javascript');
			
			require_once __DIR__.'/thirdparty/jshrink.php';
			
			return \JShrink\Minifier::minify( $content );
		}
		
		private function save( $dir , $filename , $contents )
		{
			//It would be nice to save in a 'cache' subdirectory but mod_rewrite is awkward
			$dir = dirname(__DIR__).'/'.$dir;
			if ( !is_dir($dir) )
			{
				mkdir($dir);
			}
			
			$path = $dir.'/'.$filename;
			$file = fopen($path,'w+');
			fwrite($file,$contents);
			fclose($file);
		}
	
	}

?>