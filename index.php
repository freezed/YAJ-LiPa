<?php
/*
 * index.php - analyse d'un fichier 'AndroidApplicationCrash-*.txt'
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

// CONSTANT
define('CONTENT_DIR', 'contenu/');
define('AAC_BASE_NAME', 'AndroidApplicationCrash');
define('AAC_PATTERN', '#AndroidApplicationCrash#');

// VARIABLES
$organisedContent = array();
$filesList = scandir(CONTENT_DIR);
$filesInfo = array();
$htmlList = '<li><a href="'.$_SERVER['SCRIPT_NAME'].'" title="Retour index">Home</a></li>'.PHP_EOL;
$fileContent = '<p>Choississez un fichier...</p>';

// PATTERN/REPLACEMENT FOR PRE-PROCESSING
$pattern[0] = '#, #';
$pattern[1] = '#{}#';
$pattern[2] = '#={#';
$pattern[3] = '#}|^{|}$#';

$replacement[0] = ',';
$replacement[1] = 'NULL';
$replacement[2] = '{';
$replacement[3] = '';

// GET FILE LIST
$i=0;
foreach($filesList as $file) {

	if(!is_dir(CONTENT_DIR.$file) AND preg_match(AAC_PATTERN, $file)) {
		$explodedFileName = explode('.', $file);
		$tstp = explode('-', $explodedFileName[0]);

		$filesInfo[$i]['full_name'] = $file;
		$filesInfo[$i]['short_name'] = $explodedFileName[0];
		$filesInfo[$i]['timestamp'] = $tstp[1];

		$htmlList .= '<li><a href="'.$_SERVER['SCRIPT_NAME'].'?id='.$tstp[1].'" title="Afficher le contenu de '.$tstp[1].'">'.$tstp[1].'</a></li>'.PHP_EOL;

		if(isset($_GET['id']) AND ctype_digit($_GET['id'])){
			//~ $tstp[1] === $_GET['id'] ? : $htmlMsg = '<p>OK</p>';
			$tstp[1] === $_GET['id'] ? : $fileContent = file_get_contents(CONTENT_DIR.$file);
		}
		$i++;
	}
}

// PRE-PROCESSING REGEX
$fileContent = preg_replace($pattern , $replacement , $fileContent);

// PROCESSING
// FIXME: mauvaise recuperation des valeurs de dimensions (pixels)
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
		// FIXME: Notice: Undefined offset: 1
		$organisedContent[$nextPieces[0]] = $nextPieces[1];
	}
}

// PRINTING HTML
echo $htmlList;
echo '<hr />';
echo '<pre>';
print_r($organisedContent);
echo '</pre>';
