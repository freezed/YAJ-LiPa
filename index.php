<?php
/*
 * index.php - liste et extrait le contenu de fichier(s) d'un format exotique
 *
 * auteur:	Freezed <git@freezed.me>
 * licence: GNU GPL v3 [http://www.gnu.org/licenses/gpl.html]
 *
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 * FIXME
 *
 * TODO
 *
 */

/**********
 * CONFIG *
 **********/
$debutScript = microtime(TRUE);
$htmlList			= '';
$html_content		= '<p>no data</p>';
$organisedContent	= '<p>No valid ID</p>';

define('CONTENT_DIR',	'contenu/');
define('BASE_NAME',		'AndroidApplicationCrash');
define('PATTERN',		'#AndroidApplicationCrash#');
define('FILE_CONTENT',	'<p>Choississez un fichier...</p>');
define('MENU_URL',		'<p><a href="'.$_SERVER['SCRIPT_NAME'].'" title="Retour index">Home</a> | <a href="'.$_SERVER['SCRIPT_NAME'].'?mode=report" title="Liste courte">Report</a></p>');

// PATTERN/REPLACEMENT FOR PRE-PROCESSING
$pattern[0] = '#, #';
$pattern[1] = '#{}#';
$pattern[2] = '#={#';
$pattern[3] = '#}|^{|}$#';

$replacement[0] = ',';
$replacement[1] = 'NULL';
$replacement[2] = '{';
$replacement[3] = '';

// USEFUL KEYS
$usefulKey = array(
	//~ 'ANDROID_ID'		=> 'android_id',
	//~ 'INSTALLATION_ID'	=> 'installation_id',
	//~ 'ANDROID_VERSION'	=> 'and_version',
	//~ 'APP_VERSION_CODE'	=> 'app_v_code',
	//~ 'APP_VERSION_NAME'	=> 'app_v_name',
	//~ 'TIME'				=> 'time',
	//~ 'HOST'				=> 'host',
	//~ 'DEVICE'			=> 'device',
	//~ 'TAGS'				=> 'tags',
	//~ 'ID'				=> 'id',
	//~ 'TYPE'				=> 'type',
	//~ 'MANUFACTURER'		=> 'manufacturer',
	//~ 'SERIAL'			=> 'serial',
	//~ 'MODEL'				=> 'model',
	//~ 'BRAND'				=> 'brand',
	//~ 'USER_APP_START_DATE'=> 'app_start_date',
	'pda'				=> 'pda',
	'USER_CRASH_DATE'	=> 'app_crash_date',
	'STACK_TRACE'		=> 'trace',
	'LOGCAT'			=> 'log',
	'REPORT_ID'			=> 'report_id'
);

/*************
 * FUNCTIONS *
 *************/

// GET FILE LIST
function get_file_list($contentDir=CONTENT_DIR, $pattern=PATTERN)
{
	$i=0;
	$filesList	= scandir($contentDir);
	$fileList	= array();

	foreach($filesList as $file) {

		if(!is_dir($contentDir.$file) AND preg_match($pattern, $file)) {
			$explodedFileName		= explode('.', $file);
			$tstp					= explode('-', $explodedFileName[0]);
			$fileList[$i]['tstp']	= $tstp[1];
			$fileList[$i]['md5']		= md5_file($contentDir.$file);

			$i++;
		}
	}

	return array_reverse($fileList);
}

// GET HTML FILE LIST
function get_html_file_list($htmlList, $contentDir=CONTENT_DIR, $pattern=PATTERN)
{
	$i=0;
	$filesList = scandir($contentDir);
	$filesInfo = array();

	foreach($filesList as $file) {

		if(!is_dir($contentDir.$file) AND preg_match($pattern, $file)) {
			$explodedFileName = explode('.', $file);
			$tstp = explode('-', $explodedFileName[0]);

			$filesInfo[$i]['full_name'] = $file;
			$filesInfo[$i]['short_name'] = $explodedFileName[0];
			$filesInfo[$i]['timestamp'] = $tstp[1];
			$filesInfo[$i]['md5'] = md5_file($contentDir.$file);

			$htmlList .= '<li><a href="'.$_SERVER['SCRIPT_NAME'].'?id='.$tstp[1].'" title="Afficher le contenu de '.$tstp[1].'">'.$tstp[1].'</a>-'.$filesInfo[$i]['md5'].'</li>'.PHP_EOL;
			$i++;
		}
	}
	return $htmlList;
}

// GET FILE DATA
function get_file_data($fileContent, $pattern=array() , $replacement=array())
{
	$organisedContent = array();

	// PRE-PROCESSING REGEX
	$fileContent = preg_replace($pattern, $replacement, $fileContent);

	// PROCESSING
	$splittedData = preg_split('#,#', $fileContent);

	foreach($splittedData as $row){
		if(preg_match('#=#', $row) AND !preg_match('#{#', $row)){
			$pieces = explode('=', $row);
			$organisedContent[$pieces[0]] = $pieces[1];
		}
		// TODO: don't flatten data
		if(preg_match('#{#', $row)){
			$pieces = explode('{', $row);
			$nextPieces = explode('=', $pieces[1]);

			if (isset($nextPieces[1])) {		// subterfuge pour palier la mauvaise recuperation des valeurs de dimensions (pixels)
				$organisedContent[$nextPieces[0]] = $nextPieces[1];
			}
		}
	}
	return $organisedContent;
}

// ARRAY TO HTML TABLE
function array_to_table_html($sourceArray, $numCol = FALSE, $noRoof = FALSE)
{
	// missing data
	if(!is_array($sourceArray)){
		return FALSE;

	// SANS numerotation de ligne
	} elseif (!$numCol){
		$keys = array_keys($sourceArray[0]);

		$header = '<tr>'.PHP_EOL .'	<th>' . implode('</th><th>', $keys) . '</th>'.PHP_EOL .'</tr>'.PHP_EOL;

		$content = '';
		foreach ($sourceArray as $row){
			$content .= '<tr>'.PHP_EOL .'<td>' . implode('</td><td>', $row) . '</td>'.PHP_EOL .'</tr>'.PHP_EOL;
		}

	// AVEC numerotation de ligne
	} else {
		$keys = array_keys($sourceArray[0]);
		$header = '<tr>'.PHP_EOL .'	<th>#</th><th>'. implode('</th><th>', $keys) . '</th>'.PHP_EOL .'</tr>'.PHP_EOL;

		$content = '';
		$i=1;

		foreach ($sourceArray as $row){
			$content .= '<tr>'.PHP_EOL .'<td>'.$i.'</td><td>' . implode('</td><td>', $row) . '</td>'.PHP_EOL .'</tr>'.PHP_EOL;
			$i++;
		}
	}
	if($noRoof){
		$retour = $content . PHP_EOL .'</table>';
	} else {
		$retour = '<table>' . PHP_EOL . $header . PHP_EOL . $content . PHP_EOL .'</table>';
	}
	return $retour;
}

/********
 * WORK *
 ********/

// SHOW FILE CONTENT
if(isset($_GET['id']) AND ctype_digit($_GET['id'])){

	$tstp				= $_GET['id'];
	$fileContent		= file_get_contents(CONTENT_DIR.BASE_NAME.'-'.$tstp.'.txt');
	$organisedContent	= get_file_data($fileContent, $pattern, $replacement);
	$html_content = '<pre>'.print_r($organisedContent, TRUE).'</pre>';

// LIST FILES SUMMARY
} elseif (isset($_GET['mode']) AND $_GET['mode'] === 'report') {
	$fileList	= get_file_list();
	$i=0;
	$report = array();
	$previousHash	= '';
	$previousTstp	= '';

	foreach($fileList as $file => $spec) {
		$fileContent		= file_get_contents(CONTENT_DIR.BASE_NAME.'-'.$spec["tstp"].'.txt');
		$organisedContent	= get_file_data($fileContent, $pattern, $replacement);

		foreach($usefulKey as $source => $dest) {

			// FILE DATA
			$report[$i]['file_resume']	= '<a href="'.$_SERVER['SCRIPT_NAME'].'?id='.$spec['tstp'].'" title="Afficher le contenu du fichier tri&eacute;">'.$spec['tstp'].'</a>';
			$report[$i]['raw_data']		= '<a href="'.CONTENT_DIR.BASE_NAME.'-'.$spec['tstp'].'.txt" title="Afficher le contenu du fichier brut ">data</a>';

			if (isset($organisedContent[$source])) {

				// DATE CONVERSION
				if ($source == 'USER_CRASH_DATE'){
					$date = date_create_from_format("Y-m-d\TH:i:s.000P", $organisedContent[$source]);
					$report[$i]['crash_date']	= date_format($date, 'Y-m-d');
					$report[$i]['crash_time']	= date_format($date, 'H:i:s');

				} else {
					$report[$i][$dest]	= $organisedContent[$source];
				}

			// NO DATA
			} else {
				$report[$i][$dest]	= '-';
			}
		}

		// CHECKING DOUBLES
		if ($spec['md5'] == $previousHash) {
			$report[$i]['doublon']	= '<a href="'.$_SERVER['SCRIPT_NAME'].'?id='.$previousTstp.'" title="Afficher le contenu du doublon">DOUBLON</a>';
		} else {
			$report[$i]['doublon']	= '-';
		}

		$previousHash	= $spec['md5'];
		$previousTstp	= $spec['tstp'];
		$i++;
	}
	$html_content = array_to_table_html($report, TRUE);

// HOME PAGE
} else {
	$htmlList		= get_html_file_list($htmlList);
	$html_content	= $htmlList;

}


// HTML RENDERING
?>

<?= MENU_URL; ?>
<hr />
<?= $html_content; ?>
<hr />
<?= MENU_URL;

	$finScript	= microtime(TRUE);
	$duree = $finScript-$debutScript;
?>
<p><small>[dur&eacute;e: <?= $duree;?>s]<small></p>
