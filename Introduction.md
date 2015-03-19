# Introduction #

This is PHP WCAP client library.

  * this is a simple PHP framework to make requests to a SUN Calendar server using WCAP protocol.
  * [WCAP](http://docs.sun.com/app/docs/doc/819-4655) protocol is a SUN standard. With WCAP, you can :
    * connect to a calendar server based on authentication
    * retrieve some events,
    * add, modify or delete some events or calendars

# Details #

Actual Wcap client class implements this methods :
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
  * _list(),  (list is a reserved word)
  * ping(),
  * check\_id(),
  * version(),
  * subscribe\_calendars(),
  * unsubscribe\_calendars()
  * import, export_

Misc :

  * POST and GET Requests are make with CURL to the server and support HTTP and HTTPS protocol
  * XML return data are parsed with php native SimpleXml functions.