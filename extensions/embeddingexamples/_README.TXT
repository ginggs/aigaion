We still need to document something about how you write views that are intended for being embedded.

* usually, you'd want the embedded view to be behind a login check. However, a fail of the login should probably not lead to a 'normal' Aigaion login page being embedded in your site. So... have the embedding controller 'fail' instead of 'redirect' when login fails? See config/filters.php for an example with the authors/embed controller. But then it doesnt pick up forms or cookies either -- so there should be a halfway option,too. (attempt but no form on second fail, totalling at 'fail', 'redirect','redirectnoform')
* the cross-domain embedding places another restriction on the view: it should have this document.domain set somewhere... 
* and it should call the fx function...
* 