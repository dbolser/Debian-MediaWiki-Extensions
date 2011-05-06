<?php

# CoffMan (http://www.wickle.com) code adapted from timeline extension.
# Timeline extension
# To use, include this file from your LocalSettings.php
# To configure, set members of $wgGraphVizSettings after the inclusion

class GraphVizSettings {
	var $dotCommand;
};
$wgGraphVizSettings = new GraphVizSettings;
$wgGraphVizSettings->dotCommand = "/usr/bin/dot";

$wgExtensionFunctions[] = "wfGraphVizExtension";

function wfGraphVizExtension() {
	global $wgParser;
	$wgParser->setHook( "graphviz", "renderGraphviz" );
}

function renderGraphviz( $timelinesrc )
{
	global $wgUploadDirectory, $wgUploadPath, $IP, $wgGraphVizSettings, $wgArticlePath, $wgTmpDirectory;
	$hash = md5( $timelinesrc );
	$dest = $wgUploadDirectory."/graphviz/";
	if ( ! is_dir( $dest ) ) { mkdir( $dest, 0777 ); }
	if ( ! is_dir( $wgTmpDirectory ) ) { mkdir( $wgTmpDirectory, 0777 ); }

	$fname = $dest . $hash;
//	echo $fname;
	if ( ! ( file_exists( $fname.".png" ) || file_exists( $fname.".err" ) ) )
	{
		$handle = fopen($fname, "w");
		fwrite($handle, $timelinesrc);
		fclose($handle);

		$cmdline = wfEscapeShellArg( $wgGraphVizSettings->dotCommand) . 
		  " -Tpng -o " . wfEscapeShellArg( $fname. ".png") . " " .
		  wfEscapeShellArg( $fname ) ;
		$cmdlinemap = wfEscapeShellArg( $wgGraphVizSettings->dotCommand) . 
		  " -Tcmapx -o " . wfEscapeShellArg( $fname. ".map") . " " .
		  wfEscapeShellArg( $fname ) ;
//		echo $cmdline;
//		exit;
//		break;
//		echo "ADIOS";
		$ret = `{$cmdline}`;
		$ret = `{$cmdlinemap}`;

		unlink($fname);

/*
if ( $ret == "" ) {
			// Message not localized, only relevant during install
			return "<div id=\"toc\"><tt>Timeline error: Executable not found. Command line was: {$cmdline}</tt></div>";
		}
*/
	}
	
	@$err=file_get_contents( $fname.".err" ); 

	if ( $err != "" ) {
		$txt = "<div id=\"toc\"><tt>$err</tt></div>";
	} else {
		//echo $fname.".map";
		@$map = file_get_contents( $fname.".map" );
		//echo "mapa-antes:".$map;
		$map=preg_replace("#<ma(.*)>#"," ",$map);
		$map=str_replace("</map>","",$map);

		//echo "mapa:".$map;	
		if (substr(php_uname(), 0, 7) == "Windows") {
			$ext = "gif";
		} else {
			$ext = "png";
		}
		
		$txt  = "<map name=\"$hash\">{$map}</map>".
		        "<img usemap=\"#{$hash}\" src=\"{$wgUploadPath}/graphviz/{$hash}.{$ext}\">";
	}
	return $txt;
}

?>
