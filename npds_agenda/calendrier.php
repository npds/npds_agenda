<?php
/************************************************************************/
/* DUNE by NPDS                                                         */
/*                                                                      */
/* NPDS Copyright (c) 2002-2018 by Philippe Brunier                     */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/*                                                                      */
/* Module npds_agenda 2.0                                               */
/*                                                                      */
/* Auteur Oim                                                           */
/* Changement de nom du module version Rev16 par jpb/phr janv 2017      */
/************************************************************************/

// For More security
if (!stristr($_SERVER['PHP_SELF'],"modules.php")) die();
if (strstr($ModPath,'..') || strstr($ModStart,'..') || stristr($ModPath, 'script') || stristr($ModPath, 'cookie') || stristr($ModPath, 'iframe') || stristr($ModPath, 'applet') || stristr($ModPath, 'object') || stristr($ModPath, 'meta') || stristr($ModStart, 'script') || stristr($ModStart, 'cookie') || stristr($ModStart, 'iframe') || stristr($ModStart, 'applet') || stristr($ModStart, 'object') || stristr($ModStart, 'meta'))
   die();
settype($niv,"integer");
settype($sup,"integer");//à voir cohérence
settype($inf,"integer");//à voir cohérence

// DEBUT LISTE EVENEMENT
function listsuj($sujet, $niv) {
   global $NPDS_Prefix, $ModPath, $theme, $cookie,  $nb_news, $tipath, $page;
   $ThisFile = 'modules.php?ModPath='.$ModPath.'&amp;ModStart=calendrier';

   /*Debut securite*/
   settype($sujet,"integer");
   settype($niv,"integer");
   settype($page,"integer");
   settype($cs1,"string");
   settype($cs,"string");

   settype($sup,"integer");
   settype($inf,"integer");
   settype($datepourmonmodal,"string");

   /*Fin securite*/
   require_once('modules/'.$ModPath.'/pag_fonc.php');
   //debut theme html partie 1/2
   $inclusion = "modules/".$ModPath."/html/listsuj.html";
   //fin theme html partie 1/2

/*Gestion naviguation en cours ou passe*/
   $now = date('Y-m-d');

/*Total pour pagination*/
   $req1 = sql_query("SELECT
         ut.groupvoir
      FROM
         ".$NPDS_Prefix."agend_dem ut,
         ".$NPDS_Prefix."agend us 
      WHERE
         ut.topicid = '$sujet'
         AND us.liaison = ut.id
         AND ut.valid = '1'
         AND us.date >= '$now'
      GROUP BY us.liaison");
   while(list($groupvoir) = sql_fetch_row($req1)) {
      if(autorisation($groupvoir)) $sup++;
   }

/*Total pour pagination*/
   $req1 = sql_query("SELECT
         ut.groupvoir
      FROM
         ".$NPDS_Prefix."agend_dem ut,
         ".$NPDS_Prefix."agend us
      WHERE
         ut.topicid = '$sujet'
         AND us.liaison = ut.id
         AND ut.valid = '1'
         AND us.date < '$now'
      GROUP BY us.liaison");
   while(list($groupvoir) = sql_fetch_row($req1)) {
      if(autorisation($groupvoir)) $inf++;
   }
   if ($sup == '') $sup = '0';
   if ($inf == '') $inf = '0';
   if($niv == '0') {
      $cs = 'class="text-danger"';
      $nb_entrees = $sup;
      $cond = "date >= '$now'";
   }
   else if($niv == '1') {
      $cs1 = 'class="text-danger"';
      $nb_entrees = $inf;
      $cond = "date < '$now'";
   }

   //Pour la navigation
   $total_pages = ceil($nb_entrees/$nb_news);
   if($page == 1)
      $page_courante = 1;
   else {
      if ($page < 1)
         $page_courante = 1;
      elseif ($page > $total_pages)
         $page_courante = $total_pages;
      else
         $page_courante = $page;
   }
   $start = ($page_courante * $nb_news - $nb_news);

   //Requete affiche sujet suivant $sujet
   $res = sql_query("SELECT topicimage, topictext FROM ".$NPDS_Prefix."agendsujet WHERE topicid = '$sujet'");
   list($topicimage, $topictext) = sql_fetch_row($res);
   $topictext = stripslashes(aff_langue($topictext));
   
   $affres ='
   <div class="card">
      <div class="card-body">
         <h4 class="mb-3">'.ag_translate('Liste des événements').' '.ag_translate('pour').' '.$topictext.'</h4>';
   if ($sup == '0' && $inf == '0')
      $affres .= '<div class="alert alert-danger"><i class="fa fa-info-circle mr-2" aria-hidden="true"></i>'.ag_translate('Vide').'</div>';
   else {
      $affres .= '
      <div class="list-group">
         <a class="list-group-item list-group-item-action" href="'.$ThisFile.'&amp;subop=listsuj&amp;sujet='.$sujet.'&amp;niv=0">'.ag_translate('Evénement(s) à venir').' <span class="badge badge-success float-right" data-toggle="tooltip" data-placement="bottom" title="Visualiser">'.$sup.'</span></a>
         <a class="list-group-item list-group-item-action" href="'.$ThisFile.'&amp;subop=listsuj&amp;sujet='.$sujet.'&amp;niv=1">'.ag_translate('Evénement(s) en cours ou passé(s)').' <span class="badge badge-secondary float-right" data-toggle="tooltip" data-placement="bottom" title="Visualiser">'.$inf.'</span></a>
      </div>';

      //Requete liste evenement suivant $sujet
      $result = sql_query("SELECT
            us.id, us.date, us.liaison,
            ut.titre, ut.intro, ut.descript, ut.lieu, ut.posteur, ut.groupvoir
         FROM
            ".$NPDS_Prefix."agend us,
            ".$NPDS_Prefix."agend_dem ut
         WHERE
            ut.topicid = '$sujet'
            AND us.liaison = ut.id
            AND ut.valid = '1'
            AND us.$cond
         GROUP BY us.liaison
         ORDER BY us.date DESC LIMIT $start,$nb_news");
      while(list($id, $date, $liaison, $titre, $intro, $descript, $lieu, $posteur, $groupvoir) = sql_fetch_row($result)) {
         $titre = stripslashes(aff_langue($titre));
         $intro = stripslashes(aff_langue($intro));
         $lieu = stripslashes(aff_langue($lieu));
         //Si membre appartient au bon groupe
         if(autorisation($groupvoir)) {
         //Si evenement plusieurs jours
            $result1 = sql_query("SELECT date FROM ".$NPDS_Prefix."agend WHERE liaison = '$liaison' ORDER BY date DESC");
            $tot = sql_num_rows($result1);

            $affres .= '
            <div class="card my-3">
                <div class="card-body">
                  <h4 class="card-title">'.$titre.'</h4>
                  <p class="card-text">';
            if ($tot > 1) {
               $affres .= '<i class="fa fa-info-circle mr-2" aria-hidden="true"></i>'.ag_translate('Cet événement dure plusieurs jours').'</p>';
               while (list($ddate) = sql_fetch_row($result1)) {
                  if($ddate > $now) $etat = 'badge badge-success';
                  else if($ddate == $now) $etat = 'badge badge-warning';
                  else if($ddate < $now) $etat = 'badge badge-warning';
                  $newdate = formatfrancais($ddate);
                  $affres .= '<div class="'.$etat.' mr-2 mb-2">'.$newdate.'</div>';
                  $datepourmonmodal .= '<span class="'.$etat.'">'.$newdate.'</span>';
               }
            }
            else {
               list($ddate) = sql_fetch_row($result1);
               $newdate = formatfrancais($ddate);
               if($ddate > $now) $etat = 'badge badge-success';
               else if($ddate == $now){$etat = 'badge badge-warning';}
               else if($ddate < $now){$etat = 'badge badge-warning';}
               $affres .= '<i class="fa fa-info-circle mr-2" aria-hidden="true"></i>'.ag_translate('Cet événement dure 1 jour').'</p>';
               $affres .= '<div class="'.$etat.' mr-2 mb-2">'.$newdate.'</div>';
            }
            $affres .= '
            <div class="row">
               <div class="col-md-2">'.ag_translate('Résumé').'</div>
               <div class="col-md-10">'.$intro.'</div>
               <div class="col-md-2">'.ag_translate('Lieu').'</div>
               <div class="col-md-10">'.$lieu.'</div>
            </div>
            <div class="row">
               <div class="col-md-12">
                  <button type="button" class="btn btn-secondary btn-sm my-2" data-toggle="modal" data-target="#ev'.$id.'">'.ag_translate('Voir la fiche').'</button>
                  <div class="modal fade" id="ev'.$id.'" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
                     <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                           <div class="modal-header">
                              <h4 class="modal-title" id="'.$id.'Label">'.$titre.'</h4>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                           </div>
                           <div class="modal-body">
                              <h5 class="'.$etat.'"><strong>';
            if ($tot > 1)
               $affres .= $datepourmonmodal;
            else
               $affres .= $newdate;
            $affres .= '</strong></h5>
                              <div class="row">
                                 <div class="col-md-2">'.ag_translate('Résumé').'</div>
                                 <div class="col-md-10">'.$intro.'</div>
                              </div>
                              <div class="row">
                                 <div class="col-md-2">'.ag_translate('Description').'</div>
                                 <div class="col-md-10">'.$descript.'</div>
                              </div>
                              <div class="row">
                                 <div class="col-md-2">'.ag_translate('Lieu').'</div>
                                 <div class="col-md-10">'.$lieu.'</div>
                              </div>
                           </div>
                           <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <hr class="my-4" />
            <p class="card-text">';
            if ($posteur == $cookie[1])
               $affres .= '
               <a class="btn btn-outline-primary btn-sm" href="modules.php?ModPath='.$ModPath.'&amp;ModStart=administration&amp;subop=editevt&amp;id='.$liaison.'"><i class="far fa-edit" aria-hidden="true"></i></a>
               <a class="btn btn-outline-danger btn-sm" href="modules.php?ModPath='.$ModPath.'&amp;ModStart=administration&amp;subop=suppevt&amp;id='.$liaison.'"><i class="fa fa-trash" aria-hidden="true"></i></a>';
            else
               $affres .= ag_translate('Posté par').' '.$posteur;
            $affres .= '
            </p>
         </div>
      </div>';
         }
      }
      /*Affiche pagination*/
      echo ag_pag($total_pages,$page_courante,'2',''.$ThisFile.'&amp;subop=listsuj&amp;sujet='.$sujet.'&amp;niv='.$niv.'','_mod');
         $affres.='
         </div>
      </div>';
   }

   /*debut theme html partie 2/2*/
   ob_start();
   include ($inclusion);
   $Xcontent = ob_get_contents();
   ob_end_clean();
   $npds_METALANG_words = array("'!topictext!'i"=>"$topictext","'!affres!'i"=>"$affres");
   echo meta_lang(aff_langue(preg_replace(array_keys($npds_METALANG_words),array_values($npds_METALANG_words), $Xcontent)));
   /*fin theme html partie 2/2*/

}
/// FIN LISTE EVENEMENT ///

/// DEBUT FONCTION JOUR ///
function jour($date) {
   global $ModPath, $NPDS_Prefix, $theme, $cookie, $ThisFile, $nb_news, $tipath, $page;
   $affeven=''; $datepourmonmodal='';
   //Debut securite
   settype($page,"integer");
   $date = removeHack($date);
   //Fin securite
   require_once('modules/'.$ModPath.'/pag_fonc.php');
   //debut theme html partie 1/2
   $inclusion = false;
   $inclusion = "modules/".$ModPath."/html/jour.html";
   //Gestion naviguation en cours ou passe
   $now = date('Y-m-d');

//Total pour naviguation
   settype($nb_entrees,'integer');
   $req1 = sql_query("SELECT
         ut.groupvoir
      FROM
         ".$NPDS_Prefix."agend us,
         ".$NPDS_Prefix."agend_dem ut
      WHERE
         us.date = '$date'
         AND us.liaison = ut.id
         AND valid = '1'");
   while(list($groupvoir) = sql_fetch_row($req1)) {
      if(autorisation($groupvoir)) $nb_entrees++;
   }

//Pour la naviguation

   $total_pages = ceil($nb_entrees/$nb_news);
   if($page == 1)
      $page_courante = 1;
   else {
      if ($page < 1)
         $page_courante = 1;
      elseif ($page > $total_pages)
         $page_courante = $total_pages;
      else
         $page_courante = $page;
   }
   $start = ($page_courante * $nb_news - $nb_news);
   $retour = convertion($date);
   $datetime = formatfrancais($date);
   $bandeau = '<a class="btn btn-outline-primary btn-sm" href="modules.php?ModPath=npds_agenda&ModStart=calendrier&amp;month='.$retour[0].'&amp;an='.$retour[1].'">'.ag_translate('Retour au calendrier').'</a>';
   $lejour = $datetime;
   if ($nb_entrees == 0)
      $affeven = '<p><i class="fa fa-info-circle mr-2" aria-hidden="true"></i>'.ag_translate('Rien de prévu ce jour').'</p>';
   else {
   //Requete liste evenement suivant $date
      $result = sql_query("SELECT
            us.id, us.date, us.liaison,
            ut.titre, ut.intro, ut.descript, ut.lieu, ut.topicid, ut.posteur, ut.groupvoir,
            uv.topicimage, uv.topictext
         FROM
            ".$NPDS_Prefix."agend us,
            ".$NPDS_Prefix."agend_dem ut,
            ".$NPDS_Prefix."agendsujet uv
         WHERE
            us.date = '$date'
            AND us.liaison = ut.id
            AND ut.valid = '1'
            AND ut.topicid = uv.topicid
         LIMIT $start,$nb_news");
      while(list($id, $date, $liaison, $titre, $intro, $descript, $lieu, $topicid, $posteur, $groupvoir, $topicimage, $topictext) = sql_fetch_row($result)) {
         $titre = stripslashes(aff_langue($titre));
         $intro = stripslashes(aff_langue($intro));
         $lieu = stripslashes(aff_langue($lieu));
         $topictext = stripslashes(aff_langue($topictext));
         $affeven .= '
         <div class="card my-3">
            <div class="card-body">
               <p class="card-text">';
         /*Si membre appartient au bon groupe*/
         if(autorisation($groupvoir)) {
            /*Si evenement plusieurs jours*/
            $result1 = sql_query("SELECT date FROM ".$NPDS_Prefix."agend WHERE liaison = '$liaison' ORDER BY date DESC");
            $tot = sql_num_rows($result1);
            $affeven .= '<img class="img-thumbnail col-2" src="'.$tipath.''.$topicimage.'" />';
            $affeven .= '<h4 class="card-title">'.$titre.'</h4>';

            if ($posteur == $cookie[1])
               $affeven .= '<a class="btn btn-outline-primary btn-sm" href="modules.php?ModPath='.$ModPath.'&amp;ModStart=administration&amp;subop=editevt&amp;id='.$liaison.'"><i class="far fa-edit" aria-hidden="true"></i></a>
               <a class="btn btn-outline-danger btn-sm" href="modules.php?ModPath='.$ModPath.'&amp;ModStart=administration&amp;subop=suppevt&amp;id='.$liaison.'"><i class="fa fa-trash" aria-hidden="true"></i></a>';
            else
               $affeven .= '<p>'.ag_translate('Posté par').'&nbsp;'.$posteur.'</p>';
            $affeven .= '<p class="card-text">';
            if ($tot > 1) {
               $affeven .= '<i class="fa fa-info-circle mr-2" aria-hidden="true"></i>'.ag_translate('Cet événement dure plusieurs jours').'</p>';
               while (list($ddate) = sql_fetch_row($result1)) {
                  if($ddate > $now){$etat = 'badge badge-success';}
                  else if($ddate == $now){$etat = 'badge badge-warning';}
                  else if($ddate < $now){$etat = 'badge badge-warning';}
                  $newdate = formatfrancais($ddate);
                  $affeven .= '<div class="'.$etat.' mr-2 mb-2">'.$newdate.'</div>';
                  $datepourmonmodal .= '<span class="'.$etat.'">'.$newdate.'</span>';
               }
            }
            else {
               list($ddate) = sql_fetch_row($result1);
               $newdate = formatfrancais($ddate);
               if($ddate > $now){$etat = 'badge badge-success';}
               else if($ddate == $now){$etat = 'badge badge-warning';}
               else if($ddate < $now){$etat = 'badge badge-warning';}
               $affeven .= '<i class="fa fa-info-circle mr-2" aria-hidden="true"></i>'.ag_translate('Cet événement dure 1 jour').'</p>';
               $affeven .= '<div class="'.$etat.' mr-2 mb-2">'.$newdate.'</div>';
            }
            $affeven .= '
            <div class="row">
               <div class="col-md-2">'.ag_translate('Résumé').'</div>
               <div class="col-md-10">'.$intro.'</div>
               <div class="col-md-2">'.ag_translate('Lieu').'</div>
               <div class="col-md-10">'.$lieu.'</div>
            </div>';
//événement du calendrier 
            $affeven .= '
            <div class="row">
            <div class="col-md-12">
            <button type="button" class="btn btn-secondary btn-sm my-2" data-toggle="modal" data-target="#ev'.$id.'">
            '.ag_translate('Voir la fiche').'
            </button>
            <div class="modal fade" id="ev'.$id.'" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
            <div class="modal-dialog modal-lg"" role="document">
            <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="'.$id.'Label">'.$titre.'</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
               <h5 class="'.$etat.'"><strong>';
            if ($tot > 1)
               $affeven .= $datepourmonmodal;
            else
               $affeven .= $newdate;
           $affeven .= '</strong></h5>
               <div class="row">
                  <div class="col-md-2">'.ag_translate('Résumé').'</div>
                  <div class="col-md-10">'.$intro.'</div>
                  <div class="col-md-2">'.ag_translate('Description').'</div>
                  <div class="col-md-10">'.$descript.'</div>
                  <div class="col-md-2">'.ag_translate('Lieu').'</div>
                  <div class="col-md-10">'.$lieu.'</div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
         </div>
      </div>
   </div>
</div>
</div>';
      $affeven .= '</div></div>';
   }
   }
   }
   
/*debut theme html partie 2/2*/
   ob_start();
   include ($inclusion);
   $Xcontent = ob_get_contents();
   ob_end_clean();
   $npds_METALANG_words = array(
      "'!bandeau!'i"=>"$bandeau",
      "'!lejour!'i"=>"$lejour",
      "'!affeven!'i"=>"$affeven"
   );
   echo meta_lang(aff_langue(preg_replace(array_keys($npds_METALANG_words),array_values($npds_METALANG_words), $Xcontent)));
/*fin theme html partie 2/2*/

/*Affiche pagination*/
   echo ag_pag($total_pages,$page_courante,'2',''.$ThisFile.'&amp;subop=jour&amp;date='.$date.'','_mod');
   
}
/// FIN JOUR ///

/// DEBUT FICHE ///
function fiche($date, $id) {
   global $ModPath, $NPDS_Prefix, $cookie, $theme, $ThisFile, $nb_news, $tipath;
   //Debut securite
   settype($id,"integer");
   $date = removeHack($date);
   //Fin securite

   //debut theme html partie 1/2
//   $inclusion = false;
   $inclusion = "modules/".$ModPath."/html/fiche.html";

   //fin theme html partie 1/2

   /*Gestion naviguation en cours ou passe*/
   $now = date('Y-m-d');
   $retour = convertion($date);
   $datetime = formatfrancais($date);
   $bandeau = '<a class="btn btn-secondary" href="'.$ThisFile.'&amp;month='.$retour[0].'&amp;an='.$retour[1].'">'.ag_translate('Retour au calendrier').'</a>';
   $bandeau1 = '<a class="btn btn-secondary" href="'.$ThisFile.'&amp;subop=jour&amp;date='.$date.'">'.ag_translate('Retour au jour').'</a>';
   $lejour = $datetime;

   /*Requete affiche evenement suivant $id*/
   $result = sql_query("SELECT titre, intro, descript, lieu, topicid, posteur, groupvoir FROM ".$NPDS_Prefix."agend_dem WHERE id = '$id' AND valid = '1'");
   $total = sql_num_rows($result);
   if ($total == 0)
      $vide = '<p class="lead"><i class="fa fa-info-circle mr-2" aria-hidden="true"></i>'.ag_translate('Aucun événement trouvé').'</p>';
   else {
      list($titre, $intro, $descript, $lieu, $topicid, $posteur, $groupvoir) = sql_fetch_row($result);

      //Si membre appartient au bon groupe
      if(autorisation($groupvoir)) {
      //Si evenement plusieur jours
         $result1 = sql_query("SELECT date FROM ".$NPDS_Prefix."agend WHERE liaison = '$id' ORDER BY date DESC");
         $tot = sql_num_rows($result1);
         if ($posteur == $cookie[1]) {
            $postepar = '<a class="btn btn-outline-primary btn-sm" href="modules.php?ModPath='.$ModPath.'&amp;ModStart=administration&amp;subop=editevt&amp;id='.$id.'"><i class="far fa-edit" aria-hidden="true"></i></a>&nbsp;&nbsp;
            <a class="btn btn-danger-outline btn-sm" href="modules.php?ModPath='.$ModPath.'&amp;ModStart=administration&amp;subop=suppevt&amp;date='.$date.'&amp;id='.$id.'"><i class="fa fa-trash" aria-hidden="true"></i></a>';
         }
         else
            $postepar = ''.ag_translate('Posté par').'&nbsp;'.$posteur.'</td>';
         $affres .= '</tr><tr><td>';
         if ($tot > 1) {
            $imgfle .= '<i class="fa fa-info-circle mr-2" aria-hidden="true"></i>'.ag_translate('Cet événement dure sur plusieurs jours').'&nbsp;:&nbsp;
            <select>
               <option>'.ag_translate('Voir').'</option>';
            while (list($ddate) = sql_fetch_row($result1)) {
               if($ddate > $now) $etat = ' style="color:#009900;"';
               else if($ddate == $now) $etat = ' style="color:#0000FF;"';
               else if($ddate < $now) $etat = ' style="color:#FF0000;"';
               $newdate = formatfrancais($ddate);
               $imgfle .= '
               <option'.$etat.'>'.$newdate.'</option>';
            }
            $imgfle .= '
            </select>';
         }
         else
            $imgfle .= '<img src="modules/'.$ModPath.'/images/fle.gif" /> '.ag_translate('Cet événement dure 1 jour').'';
         $imgfle .= '</div>';

         /*Requete liste categorie*/
         $result2 = sql_query("SELECT topicimage, topictext FROM ".$NPDS_Prefix."agendsujet WHERE topicid = '$topicid'");
         list($topicimage, $topictext) = sql_fetch_row($result2);
         $titrefiche = stripslashes(aff_langue($titre));
         $imgsuj = '<img src="'.$tipath.''.$topicimage.'" /><br />'.stripslashes(aff_langue($topictext));
         $resume = str_replace("\n","<br />",stripslashes(aff_langue($intro)));
         $detail = stripslashes(aff_langue($descript));
         $lieu = stripslashes(aff_langue($lieu));
      }
   }

   //debut theme html partie 2/2
   ob_start();
   include ($inclusion);
   $Xcontent = ob_get_contents();
   ob_end_clean();
   $npds_METALANG_words = array(
      "'!bandeau!'i"=>"$bandeau",
      "'!bandeau1!'i"=>"$bandeau1",
      "'!lejour!'i"=>"$lejour",
      "'!vide!'i"=>"$vide",
      "'!imgfle!'i"=>"$imgfle",
      "'!listjour!'i"=>"$listjour",
      "'!titrefiche!'i"=>"$titrefiche",
      "'!imgsuj!'i"=>"$imgsuj",
      "'!resume!'i"=>"$resume",
      "'!detail!'i"=>"$detail",
      "'!lieu!'i"=>"$lieu",
      "'!postepar!'i"=>"$postepar"
   );
   echo meta_lang(aff_langue(preg_replace(array_keys($npds_METALANG_words),array_values($npds_METALANG_words), $Xcontent)));
//fin theme html partie 2/2

}
/// FIN FICHE ///

////////////////////
/// FIN FONCTION ///
////////////////////

   include ('modules/'.$ModPath.'/admin/pages.php');
   global $pdst, $language;
   include_once('modules/'.$ModPath.'/lang/agenda-'.$language.'.php');
   settype($subop,'string');
   settype($an,'integer');
   settype($month,'integer');

   /*Paramètres utilisés par le script*/
   $ThisFile = 'modules.php?ModPath='.$ModPath.'&amp;ModStart='.$ModStart;
   $ThisRedo = 'modules.php?ModPath='.$ModPath.'&ModStart='.$ModStart;
   $tipath = 'modules/'.$ModPath.'/images/categories/';
   include('header.php');
   include('modules/'.$ModPath.'/admin/config.php');
   require_once('modules/'.$ModPath.'/ag_fonc.php');
   include ('modules/'.$ModPath.'/cache.timings.php');
   if ($SuperCache) {
      $cache_obj = new cacheManager();
      $cache_obj->startCachingPage();
   }
   else $cache_obj = new SuperCacheEmpty();

   if (($cache_obj->genereting_output == 1) or ($cache_obj->genereting_output == -1) or (!$SuperCache)) {
      switch($subop) {
         default:
            echo suj();
            echo calend($an, $month, 0);
         break;
         case 'listsuj':
            echo suj();
            listsuj($sujet, $niv);
         break;
         case 'jour':
            echo suj();
            jour($date);
         break;
         case 'fiche':
            echo suj();
            fiche($date, $id);
         break;
      }
   }
   if ($SuperCache) $cache_obj->endCachingPage();
include("footer.php");
?>