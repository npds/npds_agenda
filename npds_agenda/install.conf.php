<?php
/************************************************************************/
/* DUNE by NPDS                                                         */
/*                                                                      */
/* NPDS Copyright (c) 2002-2022 by Philippe Brunier                     */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/*                                                                      */
/* NMIG : NPDS Module Installer Generator                               */
/* ---------------------------------------------------------------------*/
/* Version 2.0 - 2015                                                   */
/* Générateur de fichier de configuration pour Module-Install 1.1       */
/* Développé par Boris - http://www.lordi-depanneur.com                 */
/* Module-Install est un installeur inspiré du programme d'installation */
/* d'origine du module Hot-Projet développé par Hotfirenet              */
/* ---------------------------------------------------------------------*/
/* Version 2.1 for NPDS 16 jpb 2017                                     */
/************************************************************************/

global $ModInstall;
#autodoc $name_module: Nom du module #required (no space and exotism)
$name_module = 'npds_agenda';

#autodoc $path_adm_module: chemin depuis $ModInstall #required SI admin avec interface
$path_adm_module = 'admin/adm';

#autodoc $affich: pour l'affichage du nom du module dans l'admin
$affich='npds_agenda';

#autodoc $icon: icon pour l'admin : c'est un nom de fichier(sans extension) !! #required SI admin avec interface
$icon='npds_agenda';

#autodoc $list_fich : Modifications de fichiers: Dans le premier tableau, tapez le nom du fichier
#autodoc et dans le deuxième, A LA MEME POSITION D'INDEX QUE LE PREMIER, tapez le code à insérer dans le fichier.
#autodoc Si le fichier doit être créé, n'oubliez pas les < ? php et ? > !!! (sans espace!).
#autodoc Synopsis: $list_fich = array(array("nom_fichier1","nom_fichier2"), array("contenu_fichier1","contenu_fichier2"));
$list_fich = array(array('',''), array('',''));

#autodoc $sql = array(""): Si votre module doit exécuter une ou plusieurs requêtes SQL, tapez vos requêtes ici.
#autodoc Attention! UNE requête par élément de tableau! SVP respecter la syntaxe suivante PAS d'espace avant l'instruction
#autodoc Synopsis: $sql = array("requête_sql_1","requête_sql_2");
$sql = array("CREATE TABLE ".$NPDS_Prefix."agend (
  id int(11) NOT NULL AUTO_INCREMENT,
  date date DEFAULT NULL,
  liaison int(11) NOT NULL,
  KEY id (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
"CREATE TABLE ".$NPDS_Prefix."agendsujet (
  topicid int(3) NOT NULL AUTO_INCREMENT,
  topicimage varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  topictext mediumtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY  (topicid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
"CREATE TABLE ".$NPDS_Prefix."agend_dem (
  id int(11) NOT NULL AUTO_INCREMENT,
  titre mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  intro longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  descript longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  lieu varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  topicid int(11) NOT NULL,
  posteur varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  groupvoir int(3) NOT NULL,
  valid int(1) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");


#autodoc $blocs = array(array(""), array(""), array(""), array(""), array(""), array(""), array(""), array(""), array(""))
#autodoc                titre      contenu    membre     groupe     index      rétention  actif      aide       description
#autodoc Configuration des blocs
$blocs = array(array("Agenda"), array("include#modules/npds_agenda/bloc/agbloc.php"), array("0"), array(""), array("1"), array("0"), array("1"), array("Affiche l\'agenda"), array("Affiche l'agenda"));

#autodoc $txtdeb : Vous pouvez mettre ici un texte de votre choix avec du html qui s'affichera au début de l'install
#autodoc Si rien n'est mis, le texte par défaut sera automatiquement affiché
$txtdeb = '<h4>Installation du module Agenda v 2.0</h4><p>Ce module vous permet de disposer d\'un agenda sur votre site.</p>';

#autodoc $txtfin : Vous pouvez mettre ici un texte de votre choix avec du html qui s'affichera à la fin de l'install
$txtfin = "<p>Votre installation est maintenant presque terminée</p><p>Penser également aux chmods :<br /><ul><li>Chmod 755 ou 777 aux dossiers.</li><li>Chmod 766 aux 2 fichiers :</li><ol><li>cache.timings.php</li><li>admin/config.php</li></ol><li>Chmod 666 aux fichiers se terminant par .html.</li><li>Chmod 744 aux autres fichiers.<br /></li></ul></p>";

#autodoc $end_link: Lien sur lequel sera redirigé l'utilisateur à la fin de l'install (si laissé vide, redirigé sur index.php)
#autodoc N'oubliez pas les '\' si vous utilisez des guillemets !!!
$end_link = '';
?>