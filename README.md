# PageNav

As your database grows, showing all the results of a query on a single page
is no longer practical. This is where pagination comes in handy. You can
display your results over a number of pages, each linked to the next,
to allow your users to browse your content in bite sized pieces.

PageNav snippet to paginate results from your database in a clean and user friendly way. 
With support for User Friendly URL and without. Do not create duplicates url

## Features

###TEMPLATES

- pageNavTpl    - Name of a chunk serving as the key element of the template, default pageNavTpl
- pageNavOutTpl - Name of a chunk serving as a general template, default pageNavOutTpl
- tplActive     - Name of a chunk serving as active item of the template, default tplActive

###Chunk pageNavTpl Parameters

- classes - class for the item
- href    - link for the item
- pageNo  - text for the item
 
###Chunk pageNavOutTpl Parameters
- navPg -         the main block paging
- outUlstart -    button on the top of the page
- outUlend -      button at the bottom of the page
- outUlprevios -  button on the previous page
- outUlnext -     button on the next page

###CSS Class Name Parameters
- classpn     - Class for the elements of the navigation buttons, default empty
- classprev   - Ñlass for elements prev buttons, default prev
- classnext   - Ñlass for elements next buttons, default next
- classactive - Ñlass for elements active buttons, default active
- textprev    - The caption for the button prev, default < (&lt;)
- textnext    - The caption for the button next, default > (&gt;)
- textstart   - The caption for the button start, default 1
- textend     - The caption for the button end, default total page

###General Parameters
- pageLimit - Maximum number of buttons for navigation, default 9
- prefPles  - prefix for the output placeholder navigation, default 'pn'
- direction - Left To Right (ltr) - default is 0 (zero) or Right To Left (rtl) for Arabic language

###Caching Properties
- cache            - Indicates if the content of each page request should be cached, by a unique request URI (not just the pageVarKey)
- cache_key_pn     - A key identifying a named xPDOCache instance to use for caching the page content, default 'pagenav'.
- cache_expires_pn - Indicates the number of seconds for each item to live in the cache. Note that 0 indicates it will live in the cache until the cache is manually cleared, unless you have a custom handler caching data outside of the handler identified by the default cache_key, default 0
***
## Example
** Example simple call **
```html
[[!PageNav? &language=`en`
	  &element=`getResources`
	  &parents=`[[*id]]`
	  &depth=`3`
	  &limit=`3`
	  &pageLimit=`5`
	  &sortby=`{"createdon":"DESC"}`
	  &includeContent=`1`
	  &tpl=`TestPub`
]]
[[!+pn.nav]]
```
** Example nclude cache on half an hour **
```html
[[!PageNav? &language=`en`
	  &element=`getResources`
	  &parents=`[[*id]]`
	  &depth=`3`
	  &limit=`3`
	  &pageLimit=`5`
	  &sortby=`{"createdon":"DESC"}`
	  &includeContent=`1`
	  &tpl=`TestPub`
	  &pageNavOutTpl=`pageNavOutTplMy`
	  &pageNavTpl=`pageNavTplMy`
	  &tplActive = `tplActiveMy`
	  &classpn=`page`
	  &classprev=`previouspostslink`
	  &classnext=`nextpostslink`
	  &classactive =`current`
	  &textprev=`&nbsp;`
	  &textnext=`&nbsp;`
	  &separator=`<span class="more">...</span>`
	  &cache=`true`
	  &cache_expires = `1800`
]]
[[!+pn.nav]]
```
