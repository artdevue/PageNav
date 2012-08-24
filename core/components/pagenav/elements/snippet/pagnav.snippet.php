<?php
/**
 * PageNav
 *
 * Copyright 2012 by Valentin Rasulov <info@artdevue.com>
 *
 * This file is part of PageNav.
 *
 * PageNav is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * PageNav is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Babel; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package PageNav
 * @version 0.0.1-beta7 - August 24, 2012
 */
/**
 * PageNav snippet to paginate results from your database in a clean and user friendly way.
 *
 *  TEMPLATES
 * pageNavTpl          - Name of a chunk serving as the key element of the template, default pageNavTpl
 * pageNavOutTpl       - Name of a chunk serving as a general template, default pageNavOutTpl
 * tplActive           - Name of a chunk serving as active item of the template, default tplActive
 * separator     - Separator between the navigation unit and directions, default <li>...</li>
 * 
 *  Chunk pageNavTpl Parameters
 * classes - class for the item
 * href    - link for the item
 * pageNo  - text for the item
 * 
 *  Chunk pageNavOutTpl Parameters
 * navPg -         the main block paging
 * outUlstart -    button on the top of the page
 * outUlend -      button at the bottom of the page
 * outUlprevios -  button on the previous page
 * outUlnext -     button on the next page
 *
 *  CSS Class Name Parameters
 * classpn     - Class for the elements of the navigation buttons, default empty
 * classprev   - Сlass for elements prev buttons, default prev
 * classnext   - Сlass for elements next buttons, default next
 * classactive - Сlass for elements active buttons, default active
 * textprev    - The caption for the button prev, default < (&lt;)
 * textnext    - The caption for the button next, default > (&gt;)
 * textstart   - The caption for the button start, default 1
 * textend     - The caption for the button end, default total page
 *
 *  General Parameters
 * pageLimit - Maximum number of buttons for navigation, default 9
 * prefPles  - prefix for the output placeholder navigation, default 'pn'
 * direction - Left To Right (ltr) - default is 0 (zero) or 
 *             Right To Left (rtl) for Arabic language
 *
 *  Caching Properties
 * cache            - Indicates if the content of each page request should be cached, 
 *                    by a unique request URI (not just the pageVarKey)
 * cache_key_pn     - A key identifying a named xPDOCache instance to use for caching 
 *                    the page content, default 'pagenav'.
 * cache_expires_pn - Indicates the number of seconds for each item to live in the cache. 
 *                    Note that 0 indicates it will live in the cache until the cache is 
 *                    manually cleared, unless you have a custom handler caching data 
 *                    outside of the handler identified by the default cache_key, 
 *                    default 0
 */
$output = '';
$properties =& $scriptProperties;
/* set default properties */
$element = $modx->getOption('element',$properties,'getResources');
$toPlaceholder = $modx->getOption('toPlaceholder',$properties,'pagenav');
$totalVar = $modx->getOption('totalVar',$properties,'total');
$direction = $modx->getOption('direction',$properties,0);
$separator = $modx->getOption('separator',$properties,'<li>...</li>');
$classpn  = $modx->getOption('classpn',$properties,'');
$classprev  = $modx->getOption('classprev',$properties,'prev');
$classnext  = $modx->getOption('classnext',$properties,'next');
$classactive  = $modx->getOption('classactive',$properties,'active');
$textprev  = $modx->getOption('textprev',$properties,'&lt;');
$textnext  = $modx->getOption('textnext',$properties,'&gt;');
$textstart  = $modx->getOption('textstart',$properties,'1');
$textend  = $modx->getOption('textend',$properties,NULL);
$max_pages  = $modx->getOption('pageLimit',$properties,9);
$pageNavTpl  = $modx->getOption('pageNavTpl',$properties,'pageNavTpl');
$pageNavTplActive  = $modx->getOption('pageNavTplActive',$properties,'pageNavTplActive');
$pageNavOutTpl  = $modx->getOption('pageNavOutTpl',$properties,'pageNavOutTpl');
$tplActive = $modx->getOption('tplActive',$properties,NULL);
$prefPles  = $modx->getOption('prefPles',$properties,'pn');
$friendlyurls = $modx->getOption('friendly_urls') == 1 ? true : NULL;
/* cache */
$cache = isset($cache) ? (boolean) $cache : (boolean) $modx->getOption('cache_resource', null, false);
if (empty($cache_key)) $cache_key = $modx->getOption('cache_key_pn', null, 'pagenav/'.$modx->context->get('key'));
if (empty($cache_handler)) $cache_handler = $modx->getOption('cache_handler_pn', null, $modx->getOption('cache_handler'));
if (empty($cache_expires)) $cache_expires = (integer) $modx->getOption('cache_expires_pn', null, 0);

/* data correction */
$pageGet = intval($_REQUEST['page']);
if($pageGet <= 0 && isset($_REQUEST['page'])) $modx->sendErrorPage();
if($pageGet == 1) $modx->sendRedirect($modx->makeUrl($modx->resource->get('id')));
$properties['toPlaceholder'] = $toPlaceholder;
$properties['offset'] = !empty($pageGet) ? ($pageGet-1)*$limit : 0;
if($prefPles != '') $prefPles = $prefPles.'.';
if($classpn != '') $classpn = ' class="'.$classpn.'"';

/* For use in conjunction with the component Gallery */
$properties['start'] = $properties['offset'];

/* Work with the cache */
if($modx->getCacheManager() && $cache){
  /* Creates a key, unique key will be created depending on the parameters passed to the function. */
  $keynav = md5('pagenav::'.implode(":",$properties).$pageGet);
    $cacheOptions = array(
        xPDO::OPT_CACHE_KEY => $cache_key,
        xPDO::OPT_CACHE_HANDLER => $cache_handler,
        xPDO::OPT_CACHE_EXPIRES => $cache_expires,
    );
  if ($cachnav = $modx->cacheManager->get($keynav,$cacheOptions)){
      /* If there is data in the cache, then we deduce */
      $modx->setPlaceholder($prefPles.'nav',$cachnav['outPl']);
      return $cachnav['output'];
    }else{
      $inCache = false;
    }
}
/* run the snippet with the parameters */
$postsRun = $modx->runSnippet($element,$properties);
$posts = $modx->getPlaceholder($totalVar);
$output = $modx->getPlaceholder($toPlaceholder);

/* Find the total number of pages */
$total = intval(($posts - 1) / $limit) + 1; 

$outPl = '';
/* If the total number of pages is greater than one, then form the pagination pages */
if($total > 1){
  if(empty($pageGet)) $pageGet = 1;
  /* if too large, the total */
  if($pageGet > $total) $pageGet = $total;
  /* Calculate the numbers from what should output messages */
  $start = $pageGet * $limit - $limit;
  /* form a prefix depending on the Parameter friendly urls */
  $navUrl = isset($friendlyurls) ? '/page-' : '&page=';
  
  $lincpone = $modx->makeUrl($modx->resource->get('id'));
  $alias = $modx->resource->get('alias');
  
  if(!isset($friendlyurls)) $lincponeNav = $lincpone.$navUrl;
  
  $navPg = array();
  $startp = $pageGet - ceil($max_pages / 2)+1;
  $end = $pageGet + floor($max_pages / 2);
  
  /* build the first and last pages of reference output pagination, if the criteria are not suitable, it is not deduce */
  if($startp <= 0){
    $startp = 1;
    $end = $max_pages > $total ? $total : $max_pages;
  }
  if($end >= $total){
    $end = $total;
    $startp = $end - $max_pages <=  0 ? 1 : $end - $max_pages + 1;
  }
  /* next button */
  if($pageGet != $total){
    $classnext = $classnext != '' ? ' class="'.$classnext.'"' : '';
    $tplOut['outUlnext'] = $modx->getChunk($pageNavTpl,array(
      'href'=>isset($friendlyurls) ? $modx->resource->getAliasPath($alias.$navUrl.($pageGet+1)) : $lincponeNav.($pageGet+1),
      'classes'=>$classnext,
      'pageNo'=>$textnext));
  }
  /* previos button */
  if($pageGet != 1){
    $classprev = $classprev != '' ? ' class="'.$classprev.'"' : '';
    $tplOut['outUlprevios'] = $modx->getChunk($pageNavTpl,array(
      'href'=>isset($friendlyurls) ? $modx->resource->getAliasPath($alias.$navUrl.($pageGet-1)) : $lincponeNav.($pageGet-1),
      'classes'=>$classprev,
      'pageNo'=>$textprev));
  }
  /* start button */
  if($startp > 1){
    $tplOut['outUlstart'] = $modx->getChunk($pageNavTpl,array(
      'href'=>$lincpone, 'classes'=>$classp,
      'pageNo'=>$textstart)).$separator;
  }
  /* end button */
  if($end < $total){
    $tplOut['outUlend'] = $separator.$modx->getChunk($pageNavTpl,array(
      'href'=>isset($friendlyurls) ? $modx->resource->getAliasPath($alias.$navUrl.$total) : $lincponeNav.$total,
      'classes'=>$classp,
      'pageNo'=>isset($textend) ? $textend : $total));
  }
  /* create an array for all the other navigation buttons */
  for($i = $startp; $i <= $end; $i ++) {
    $classp = $i == $pageGet ? ' class="'.$classactive.'"' : $classpn;
    $pNTpl = $i == $pageGet ? $tplActive : $pageNavTpl;
    $navPg[] = $modx->getChunk($pNTpl,array(
      'href'=>$i == 1 ? $lincpone : (isset($friendlyurls) ? $modx->resource->getAliasPath($alias.$navUrl.$i) : $lincponeNav.$i),
      'classes'=>$classp,
      'pageNo'=>$i));
  }
  /* Direction or PageNav : Left To Right (ltr) - default is 0 (zero) or Right To Left (rtl) for Arabic language */
  if($direction != 0) array_reverse($navPg);
  
  $tplOut['navPg'] = implode("\r\n",$navPg);
  $outPl = $modx->getChunk($pageNavOutTpl,$tplOut);
}

$modx->setPlaceholder($prefPles.'nav',$outPl);
/* add a page in the cache if there is an option */
if ($modx->getCacheManager() && !$inCache && $cache){
  $cachnav['outPl'] = $outPl;
  $cachnav['output'] = $output;
  $modx->cacheManager->set($keynav, $cachnav,$cacheOptions[xPDO::OPT_CACHE_EXPIRES],$cacheOptions);
}        

return $output;