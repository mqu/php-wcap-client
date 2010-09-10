<?php

/**

	client interface on WCAP server (Sun Convergence)
	author : Marc Quinton / DSNA/DTI - july 2010.

	supported methods :
		- login, logout(),
		- get_freebusy(),
		- fetchcomponents_by_range(),
		- get_all_time_zones(),
		- store_event(),
		- get_userprefs(),
		- get_calprops(),
		- gettime(),
		- search_calprops(),
		- search_calprops(),
		- list_subscribed(),
		- _list(),  (list is a reserved word)
		- ping(),
		- check_id(),
		- version(),
		- subscribe_calendars(),
		- unsubscribe_calendars()
		- import, export,
		- fetch_events_by_id()


	default reading format is XML ; Ical format is harder to parse and use.

	documentation links :

		- http://docs.sun.com/app/docs/doc/819-4655/acbdh?l=en&a=view
		- http://dlc.sun.com/pdf/819-4655/819-4655.pdf
		- https://wiki.mozilla.org/Calendar:WCAP_Guide


	defined classes :
		- Date : date utilities functions (timestamps)
		- Ical : ical format conversions,
		- IcalDateRange : help specifying some range for Ical (and Wcap) formats
		- XmlWcapResponse : used to decode Wcap responses in Xml output format
		- WcapClient : the Wcap client
		- XmlIcal, XmlIcalEvent
**/

require_once('CURL.php');
require_once('Date.php');
require_once('Ical.php');
require_once('IcalDateRange.php');
require_once('XmlWcapResponse.php');
require_once('XmlIcal.php');
require_once('XmlIcalEvent.php');
require_once('XmlIcalFreebusyEvent.php');



define('WCAP_ERR_LOGOUT',                              -1);
define('WCAP_ERR_OK',                                   0);
define('WCAP_ERR_LOGIN_FAILED',                         1);
define('WCAP_ERR_LOGIN_OK_DEFAULT_CALENDAR_NOT_FOUND',  2);
define('WCAP_ERR_ACCESS_DENIED_TO_CALENDAR',           28);
define('WCAP_ERR_CALENDAR_DOES_NOT_EXIST',             29);
define('WCAP_ERR_CGET_FREEBUSY_FAILED',                39);


class WcapClient {

	protected $curl;
	protected $wcap_url;

	protected $session_id = null;
	protected $errno = 0;

	# default timezone.
	protected $tzid = 'Europe/Paris';

	public function __construct($wcap_url){
		$this->server_url = $wcap_url;
		$this->curl = new Curl();
	}

	public function login($user, $passwd,$extra_args=array()){
		$args = array(
			'fmt-out'   => 'text/xml',
			'user'      => $user,
			'password'  => $passwd
			);

		$resp = new XmlWcapResponse($this->get('login', $args));

		$this->errno = $resp->errno();
		$this->session_id = $resp->session_id();

		# login OK
		if($this->errno == WCAP_ERR_OK)
			return true;
		else
			return false;
	}

	public function logout(){
		$args = array(
			'fmt-out'   => 'text/xml',
			);
		$resp = new XmlWcapResponse($this->get('logout', $args));

		return ($resp->errno() == WCAP_ERR_LOGOUT);
	}


	public function get_free_busy($calid, $dtstart, $dtend){

		$args = array(
			'calid'     => $calid,
			'dtstart'   => $dtstart,
			'dtend'     => $dtend,
			'tzid'      => $this->get_tzid(),
			'tzidout'   => $this->get_tzid(),
			'fmt-out'   => 'text/xml',
		);

		$data = $this->get('get_freebusy', $args);
		$ical = new XmlIcal($data);
		return $ical->get_icals();
	}

	public function fetchevents_by_id($calid, $uid, $_args = array()){

		$args = array(
			'calid'     => $calid,
			'uid'       => $uid,
			'fmt-out'   => 'text/xml',
		);

		$data = $this->get('fetchevents_by_id', $args);
		$ical = new XmlIcal($data);
		$icals = $ical->get_icals();
		$events = $icals[0]->events();
		return $events[0];
	}

	public function fetchcomponents_by_range($calid, $dtstart, $dtend, $extra_args = array(), $options=array()){
		if(count($extra_args) == 0)
			$extra_args=array(
				'attrset'   => 1           # attributes of return event : 0:minimum, 1:middle, 2:full-event
			);

		if(count($options) == 0)
			$options=array(
				'auto'   => true    # if true : try to get freebusy if calid is not readable (no acces)
			);

		$args = array(
			'calid'     => $calid,
			'dtstart'   => $dtstart,
			'dtend'     => $dtend,
			'fmt-out'   => 'text/xml',
			'attrset'   => $extra_args['attrset'],
		);

		foreach($extra_args as $key=>$val)
			$args[$key] = $val;

		$data = $this->get('fetchcomponents_by_range', $args);
		$ical = new XmlIcal($data);
		$icals = $ical->get_icals();

		if($options['auto'] == false)
			return $icals;

		# get freebusy for calendar with restricted acces
		$list = array();
		foreach($icals as $key=>$cal)
			if($cal->errno() == WCAP_ERR_ACCESS_DENIED_TO_CALENDAR){
				$list[] = $cal->name();
				unset($icals[$key]);
			}
		# no calendar with acces denied error
		if(count($list) == 0)
			return $icals;

		$icals2 = $this->get_free_busy(join(';', $list), $dtstart, $dtend);

		return array_merge($icals, $icals2);
	}


/**
	For example, this URL would call storeevents.wcap and would result in storing an event in the calendar john,
	http://calendarserver/storeevents.wcap
		?id=3423423asdfasf
		&calid=john
		&dtstart=20020101T103000
		&dtend=20020101T113000&uid=001
		&summary=new%20year%20event

	The above example results in the following entry in an iCalendar database:

		BEGIN:VCALENDAR
		PRODID:-//Sun/Calendar Server//EN
		METHOD:PUBLISH
		VERSION:2.0
		X-NSCP-WCAP-ERRNO:0
		BEGIN:VEVENT
		REQUEST-STATUS:2.0;Success.  Store successful.
		UID:000000000000000000000000000000005fac5f4c0c0c142b5e2d000030540000
		END:VEVENT
		END:VCALENDAR

	errors :

		39 - CANNOT_MODIFY_LINKED_EVENTS
		40 - STORE_FAILED_DOUBLE_BOOKED

*/

	public function store_event($calid, $args, $extra=array()){

		$args['calid'] = $calid;
		$args['tzid']  = $this->get_tzid();
		$args['fmt-out'] = 'text/xml';

		if(count($extra) != 0)
			foreach($extra as $key=>$val)
				$args[$key] = $val;

		$data = $this->get('storeevents', $args);
		print_r($data);
		return new XmlWcapResponse($data);
	}

	public function get_userprefs($calid, $args = null){

		$args['calid'] = $calid;
		$args['fmt-out'] = 'text/xml';
		$data = $this->get('get_userprefs', $args);
		return new XmlWcapResponse($data);
	}

	public function get_calprops($calid, $args = null){

		$args['calid'] = $calid;
		$args['fmt-out'] = 'text/xml';
		$data = $this->get('get_calprops', $args);
		return new XmlWcapResponse($data);
	}

	public function get_all_timezones(){
		$args = array();
		$args['fmt-out'] = 'text/xml';
		return $this->get('get_all_timezones', $args);
	}

	public function gettime(){
		$args = array();
		$args['fmt-out'] = 'text/xml';
		$data = $this->get('gettime', $args);
		return new XmlWcapResponse($data);
	}

	# args
	#  - calid        - bool -
	#  - name         - bool
	#  - primaryOwner - bool

	#  - maxResults  - int - 
	#  - searchOpts   - int [0,1,2,3]
	#  - search-string

	public function search_calprops($search_string, $args = array()){
		$args['fmt-out'] = 'text/xml';
		$args['search-string'] = $search_string;
		$data = $this->get('search_calprops', $args);
		return new XmlWcapResponse($data);
	}

	# only available for administrator
	# need to be tested
	public function list_subscribed($userid){

		$args['userid'] = $userid;
		$args['fmt-out'] = 'text/xml';
		$data = $this->get('list_subscribed', $args);
		return new XmlWcapResponse($data);
	}

	public function _list($userid){

		$args['userid'] = $userid;
		$args['fmt-out'] = 'text/xml';
		$data = $this->get('list', $args);
		return new XmlWcapResponse($data);
	}

	# reserved to administrators
	public function ping(){
		$args = array();
		$data = $this->get('ping', $args);
		return new XmlWcapResponse($data);
	}

	public function check_id(){
		$args['fmt-out'] = 'text/xml';
		$data = $this->get('check_id', $args);
		return new XmlWcapResponse($data);
	}

	public function version(){
		$args['fmt-out'] = 'text/xml';
		$data = $this->get('version', $args);
		return new XmlWcapResponse($data);
	}

	# need to be tested
	public function subscribe_calendars($calid, $args){
		$args['fmt-out'] = 'text/xml';
		$args['calid'] = $calid;
		$data = $this->get('subscribe_calendars', $args);
		return new XmlWcapResponse($data);
	}

	# need to be tested
	public function unsubscribe_calendars($calid, $args){
		$args['fmt-out'] = 'text/xml';
		$args['calid'] = $calid;
		$data = $this->get('unsubscribe_calendars', $args);
		return new XmlWcapResponse($data);
	}

	# need to be tested
	public function export($calid, $args=array(), $format='text/xml'){
		$args['content-out'] = $format;
		$args['calid'] = $calid;
		$args['dtstart'] = 0;
		$args['dtend'] = 0;
		$data = $this->post('export', $args);
		return new XmlWcapResponse($data);
	}

	# need to be tested
	public function import($calid, $content, $args=array(), $format='text/xml'){
		$args['calid'] = $calid;
		$args['content-in'] = $content;
		$data = $this->post('import', $args);
		return new XmlWcapResponse($data);
	}


	protected function get($cmd, $args){
		return $this->do_request($cmd, $args, 'get');
	}

	protected function post($cmd, $args){
		return $this->do_request($cmd, $args, 'post');
	}

	protected function do_request($cmd, $args, $method='get'){

		if(!isset($args['id']))
			$args = array_merge(array('id'=>$this->session_id), $args);

		$list = array();
		foreach($args as $key => $val){
			$list[] = sprintf('%s=%s', $key, $this->transcode_arg($key, $val));
		}

		$url = sprintf('%s/%s.wcap?%s',
				$this->server_url,
				$cmd,
				join('&', $list)
			);

		switch($method){
			case 'get':  return $this->curl->get($url)->body();  break;
			case 'post': return $this->curl->post($url)->body();  break;

				/*
				$args = array(
					'file' => 'export.ics',
				);

				$headers = array(
					'Accept_Encoding: deflate,gzip',
					'Accept_Language: en',
					'Accept_Charset: iso-8859-1,*,utf-8',
					# 'Content-type: text/xml'
				);

				return $this->curl->post($url, $extra_args, $headers)->body(); 
				break; */
		}
	}

	# only transcode required args (in $list)
	public function transcode_arg($name, $val){
		$list = array('summary', 'desc',);
		if(in_array($name, $list))
			return $this->transcode($val);
		return $val;
	}

	public function transcode($str){
		$table = array(
			' ' => '%20',
			'&' => '%26',
			'"' => '%22',
			"'" => '%27',
			"?" => '%3F',
			"\n" => '%0A',
			"\r" => '%0D'
		);
		echo "transcode : " ; printf($str);
		return htmlspecialchars(strtr($str, (array) $table));
	}

	public function get_tzid(){
		return $this->tzid;
	}

	public function set_tzid($tzid){
		$this->tzid = $tzid;
	}

	static public function error_to_string($err){

		$list = array(
			WCAP_ERR_LOGOUT                              => 'logut',
			WCAP_ERR_OK                                  => 'OK',
			WCAP_ERR_LOGIN_FAILED                        => 'erreur de connexion',
			WCAP_ERR_LOGIN_OK_DEFAULT_CALENDAR_NOT_FOUND => 'calendrier non trouvé',
			WCAP_ERR_ACCESS_DENIED_TO_CALENDAR           => 'accès non autorisé au calendrier',
			WCAP_ERR_CALENDAR_DOES_NOT_EXIST             => 'calendrier non existant',
			WCAP_ERR_CGET_FREEBUSY_FAILED                => 'lecture freebusy non autorisée'
		);
		return $list[$err];
	}
}

/**

 example :

$wcap = new WcapClient('https://calendar.lfpo.aviation-civile.gouv.fr');
$user = 'login'; $passwd = 'xyz';
$status = $wcap->login($user, $passwd);

# some rooms with associated calendars in our calendar server.
$rooms = array(
	'a006', 'a102', 'a126', 'a127', 'a209', 
	'e115', 'e213', 'k054', 'k061', 'k121', 'k237',
	'm010', 'm010bis', 'm065', 'm161', 'm236', 'm263',
	'o119', 'o203', 's113', 'u'
	);


$range = IcalDateRange::this_month();
$dtstart = $range[0];
$dtend   = $range[1];


# change room name to calid real name.
foreach($rooms as $s)
	$ids[]=sprintf('dsna-dti-mnd-salle%s-rs@aviation-civile.gouv.fr',$s);
$calid=join(';', $ids);

$icals = $wcap->fetchcomponents_by_range($calid, $dtstart, $dtend);

# foreach rooms :
# display name, and each events (start, duration, summary)
# 
$format = 'd/m/Y H:i:s'; # date format
foreach($icals as $cal){
	echo $cal->name() . "\n";
	foreach($cal->events() as $ev){
		printf("  %s : %4dmn : %s\n", $ev->start($format), $ev->length() / 60, $ev->summary());
	}
	echo "\n";
}

*/

?>
