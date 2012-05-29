<?php
/**
 * @package pagenav
 * @version 0.0.1-beta2 - May 29, 2012
 */
  switch ($modx->event->name) {    
    case 'OnPageNotFound':
    if($modx->getOption('friendly_urls') != 1) break;
    $arrayURI = explode('/',$_SERVER['REQUEST_URI']);
    $coun = 2;
    if(substr($_SERVER['REQUEST_URI'], -1) != '/') $coun = 1;
    $coun = count($arrayURI)-$coun;
    $pageId = explode('-',$arrayURI[$coun]);
    if($pageId[0] == 'page' && intval($pageId[1])){
      $consuf = $modx->getOption('container_suffix');
      if(substr($_SERVER['REQUEST_URI'], -1) != '/' && $consuf == '/'){
	header('Location: '.$_SERVER['REQUEST_URI'].'/');
      }
      if($consuf == '/'){
	$regpage = $pageId[1];
      }else{
	$regar = explode('.',$pageId[1]);
	$regpage = $regar[0];
      }
      $_REQUEST['page'] = $regpage;
      if($modx->getCacheManager()){
	$keynp = md5('pagenav::'.$arrayURI[$coun-1]);
	$idRsource = $modx->cacheManager->get($keynp);
	if(!isset($idRsource)){
	  $resource = $modx->getObject('modResource',array('alias'=>$arrayURI[$coun-1]));
	  $idRsource = $resource->get('id'); //20;
	  $modx->cacheManager->set($keynp, $idRsource);
	}
      }
      if($regpage == 1) $modx->sendRedirect($modx->makeUrl($idRsource));
      $modx->sendForward($idRsource);
    }
    break;
    case 'OnSiteRefresh':
    if($modx->cacheManager->clearCache(array('/pagenav')))
    $modx->log(modX::LOG_LEVEL_INFO,'PageNac clear cache ok!');
    break;
  }