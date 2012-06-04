<?php
/**
 * @package pagenav
 * @version 0.0.1-beta4 - June 5, 2012
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
	$cacheOptions = array(
	  xPDO::OPT_CACHE_KEY => $modx->getOption('cache_resource_key', null, 'resource'),
	  xPDO::OPT_CACHE_HANDLER => $modx->getOption('cache_handler', null, 'xPDOFileCache'),
	  xPDO::OPT_CACHE_EXPIRES => (integer) $modx->getOption('cache_resource_expires', null, 0)
	);
	$keynp = md5('pagenav::'.$arrayURI[$coun-1]);
	$inCache = false;
	if($idRsource = $modx->cacheManager->get($keynp,$cacheOptions)) $inCache = true;
	
      }
      if($modx->getCacheManager() && !$inCache){
	  $resource = $modx->getObject('modResource',array('alias'=>$arrayURI[$coun-1]));
	  $idRsource = $resource->get('id'); //20;
	  $modx->cacheManager->set($keynp, $idRsource, $cacheOptions[xPDO::OPT_CACHE_EXPIRES],$cacheOptions);
      }
      if($regpage == 1) $modx->sendRedirect($modx->makeUrl($idRsource));
      $modx->sendForward($idRsource);
    }
    break;
    case 'OnSiteRefresh':
    //if($modx->cacheManager->clearCache(array('/pagenav')))
    $modx->cacheManager->refresh();
    if($modx->cacheManager->refresh(array('/pagenav'=> array())))
      $modx->log(modX::LOG_LEVEL_INFO,'PageNav clear cache. '.$modx->lexicon('refresh_success'));
    break;
  }