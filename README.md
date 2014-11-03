YAJ-LiPa
========

__YAJ-LiPa__ (Yet Another JSON-Like Parser) liste et extrait le contenu de fichier(s) d'un format exotique du genre:

`{VAR_001={VAR_010=10, VAR_011=011, VAR_012={var_110=[0,1,2,3], var_111=false, var_112=[0,1]}, var_013=true, var_014=foo bar 2014}, VAR_002={}, VAR_003=/my/path/to/location, VAR_004=NULL}`

Le nom de fichier est constitué du motif suivant:

`BaseName-nnnnnnnnnnnnn.txt` (nnnnnnnnnnnnn est un étrange format de timesamp unix avec 3 chiffres en plus à la fin).

Une fonction retourne une liste des fichiers disponible, une autre le contenu du fichier _applati_, dans un Array().

### Avertissement ###
Ce script a été réalisé: __par__ curiosité, __pour__ répondre à une problématique probablement *unique*, __avec__ la meilleure des volontés, __mais__ de piètres compétences. Il en cours de dévelloppement, et donc il n'offre **aucune garantie**.

### Usage ###

1. copier le fichier _index.php_ à la racine de votre serveur web
2. la fonction `get_file_list()`, retourne une liste formaté HTML des fichiers `BaseName-nnnnnnnnnnnnn.txt` du répertoire `./contenu/`
3. la fonction `get_file_data()`, retourne les données du fichier passé en paramètre

### Licence ###
__YAJ-LiPa__ est publié sous licence [GNU-GPLv3](http://www.gnu.org/licenses/gpl.html)

_Merci pour votre intérêt_
