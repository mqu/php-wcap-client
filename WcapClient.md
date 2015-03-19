# WcapClient class #

## properties ##
  * protected $curl;
  * protected $wcap\_url;
  * protected $session\_id = null;
  * protected $errno = 0;
  * protected $tzid = 'Europe/Paris';

## methods ##

  * login, logout(),
  * get\_freebusy(),
  * fetchcomponents\_by\_range(),
  * get\_all\_time\_zones(),
  * store\_event(),
  * get\_userprefs(),
  * get\_calprops(),
  * gettime(),
  * search\_calprops(),
  * search\_calprops(),
  * list\_subscribed(),
  * list(), (list is a reserved word)
  * ping(),
  * check\_id(),
  * version(),
  * subscribe\_calendars(),
  * unsubscribe\_calendars()
  * import, export