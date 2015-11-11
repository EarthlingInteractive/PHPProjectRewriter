<?php

class EarthIT_PHPProjectRewriter_Worker
{
	protected $inProj;
	protected $outProj;
	protected $textReplacements; // For strtr
	protected $filenameReplacements; // For preg_replace
	
	// For several variables, it is assumed that the output project has
	// a similar structure to the input one.  e.g.  output.sourceDirs
	// is not consulted; only input.sourceDirs.
	
	protected static function replacements( EarthIT_PHPProjectRewriter_Project $inProj, EarthIT_PHPProjectRewriter_Project $outProj ) {
		$inConfig = $inProj->getConfig();
		$outConfig = $outProj->getConfig();
		$replacements = array();
		foreach( $inConfig as $k=>$v ) {
			if( is_string($v) ) {
				// May need to use a list of config properties that are actually names
				$replacements[$v] = $outConfig[$k];
			}
		}
		//$replacements[$sector.'/'.$inProj->getKrog13($sector)] = $outProj->getSourceDir($sector);
		return $replacements;
	}
	
	public function __construct( EarthIT_PHPProjectRewriter_Project $inProj, EarthIT_PHPProjectRewriter_Project $outProj ) {
		$this->inProj = $inProj;
		$this->outProj = $outProj;
		$this->textReplacements = self::replacements($inProj, $outProj);
	}
	
	protected function transformSubPath($subPath) {
		return strtr($subPath, $this->textReplacements);
	}
	
	protected function fullPath( EarthIT_PHPProjectRewriter_Project $proj, $path ) {
		return $proj->getDir() . ($path ? '/'.$path : '');
	}
	
	public function run($inSubPath='', $doReplacements=true) {
		$outSubPath = $doReplacements ? $this->transformSubPath($inSubPath) : $inSubPath;
		$inFile = self::fullPath($this->inProj, $inSubPath);
		$outFile = self::fullPath($this->outProj, $outSubPath);
		if( is_dir($inFile) ) {
			if(!is_dir($outFile)) mkdir($outFile,0755,true);
			$dh = opendir($inFile);
			if( $dh === false ) throw new Exception("Failed to open directory '$inFile'");
			while( ($fn = readdir($dh)) !== false ) {
				if( preg_match('#^(\.|\.\.|.*~)$#', $fn) ) continue;
				$this->run($inSubPath ? $inSubPath.'/'.$fn : $fn, $doReplacements && ($fn !== '.git'));
			}
			closedir($dh);
		} else {
			// Don't overwrite existing files
			if( file_exists($outFile) ) return;
			
			$content = file_get_contents($inFile);
			if( $content === false ) throw new Exception("Failed to read '$inFile'");
			$content = $doReplacements ? strtr($content, $this->textReplacements) : $content;

			if( ($outDir = dirname($outFile)) and !is_dir($outDir) ) mkdir($outDir, 0755, true);
			file_put_contents($outFile, $content);
			chmod($outFile, fileperms($inFile) & 0777);
		}
	}
}
