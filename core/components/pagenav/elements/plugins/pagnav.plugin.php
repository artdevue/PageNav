<?php
/**
 * @package pagenav
 * @version 0.0.1-beta7 - August 24, 2012
 */
  switch ($modx->event->name) {    
    case 'OnPageNotFound':
      // Check whether active friendly_urls, if not, then the interrupt
      if($modx->getOption('friendly_urls') != 1) break;
      // get key request_param_alias
      $q = $_REQUEST[$modx->getOption('request_param_alias')];
      // Find the last part of request
      $arrayURI = explode('/',$q);
      // by default 2
      $coun = 2;
      // If at the end of a slash, then coun = 1
      if(substr($q, -1) != '/') $coun = 1;
      $coun = count($arrayURI)-$coun;
      // We find the necessary parameters
      $pageId = explode('-',$arrayURI[$coun]);
      if($pageId[0] == 'page' && intval($pageId[1])){
	$consuf = $modx->getOption('container_suffix');
	// If the resource is a container, and in the end there is no slash, then add it
	if(substr($q, -1) != '/' && $consuf == '/'){
	  $q = $q.'/';
	  $regPage = explode('?',$_SERVER['REQUEST_URI']);
	  $rp = isset($regPage[1]) ? '?'.$regPage[1] : '';
	  header('Location: /'.$q.$rp);
	}
	if($consuf == '/'){
	  // Assigned page number
	  $regpage = $pageId[1];
	}else{
	  // Assigned to the page number, if it is not a container
	  $regar = explode('.',$pageId[1]);
	  $regpage = $regar[0];
	}
	// Add to our page number request
	$_REQUEST['page'] = $regpage;
	// Check the cache and redirect
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
	  $idRsource = $resource->get('id');
	  $modx->cacheManager->set($keynp, $idRsource, $cacheOptions[xPDO::OPT_CACHE_EXPIRES],$cacheOptions);
	}
	// When the page first, then just redirect with no parameters
	if($regpage == 1)
	  $modx->sendRedirect($modx->makeUrl($idRsource));
	  
	$modx->sendForward($idRsource);
      }
      break;
    // Cleanse our catalog
    case 'OnSiteRefresh':
    if($modx->cacheManager->refresh(array('/pagenav'=> array())))
      $modx->log(modX::LOG_LEVEL_INFO,'PageNav clear cache. '.$modx->lexicon('refresh_success'));
    break;
  }