#WcapClient test cases

# Introduction #

Some quick test cases for WcapClient library.


# Details #

To be done.

```
<?php


error_reporting(E_ALL);

include_once('amelia-config.php');  # declare $config['*']
require_once('WcapClient.php');

$wcap = new WcapClient($config['calendar/server/url']);


$status = $wcap->login($config['calendar/server/user'], $config['calendar/server/passwd']);
if($status == false)
	throw new Exception ("connexion error");

function test_fetch_components($wcap){

	$format = 'd/m/Y | H:i:s'; # date format

	$calids = array(
		'prenom.nom@your-domain.fr'
	);
	
	
	$calids = join(';', $calids);
	
	# this week
	$week = Date::week_number();
	
	$range = IcalDateRange::week($week);
	$dtstart = $range[0];
	$dtend   = $range[1];
	
	$icals = $wcap->fetchcomponents_by_range($calids, $dtstart, $dtend);
	print_r($icals);
	
	foreach($icals as $ical){
		printf("cal : %s\n", $ical->name());
		printf(" # error : %s (%s)\n", $ical->errno(), WcapClient::error_to_string($ical->errno()));
	
		if($ical->errno() == 0){
			foreach($ical->events() as $ev){
				$len = $ev->length() / 60;
				printf(" - %s | %02d:%02d | %s\n", $ev->start($format), intval($len/60), intval($len%60), $ev->summary());
			}
		}
	}
}

function test_get_freebuzy($wcap){

	$format = 'd/m/Y | H:i:s'; # date format

	$calids = array(
		        'prenom.nom@your-domain.fr'
	);

	$calids = join(';', $calids);

	# this week
	$week = Date::week_number();
	
	$range = IcalDateRange::week($week);
	$dtstart = $range[0];
	$dtend   = $range[1];
	
	$icals = $wcap->get_free_busy($calids, $dtstart, $dtend);
	print_r($icals);

}

function test_fetchevents_by_id($wcap){
	$uid = '0000000000000000000000000000000082a7774ce25df36d1e000000d74f0000';
	$calid = 'prenom.nom@your-domain.fr';
	$event = $wcap->fetchevents_by_id($calid, $uid);

	printf("organizer = %s\n", $event->organizer());
}


function test_store_event($wcap){
	$calid = 'prenom.nom@your-domain.fr';

	$dtstart = Date::this_day();
	$dtend   = $dtstart + 24 * 60 * 60;
	$summary = "test wcap-client : n'est-il pas ? &";

	# évenement simple
	$event = array(
		'dtstart' => Ical::timestamp2ICal($dtstart),
		'dtend'   => Ical::timestamp2ICal($dtend),
		'summary' => "test wcap-client : n'est-il pas ? &",
		# 'desc'    => file_get_contents('/etc/passwd')
	);

	# $result = $wcap->store_event($calid, $event);
	# print_r($result);

	# evenement sur une journée
	$event['dtstart'] = '20100810';
	$event['isAllDay'] = 1;
	unset($event['dtend']);
	$event['summary'] = 'une journée';
	# $result = $wcap->store_event($calid, $event);

	# evenement sur plusieurs jours
	$event = array(
		'dtstart' => '20100810',
		'isAllDay' => 1,
		# 'duration' => 'P3D',  # 3 jours
		'duration' => 'P3W',    # 3 weeks
		'summary'  => 'ev. 3 jours'
	);
	$result = $wcap->store_event($calid, $event);
	print_r($result);
}

function test_get_userprefs($wcap){
	$calid='marc.quinton@aviation-civile.gouv.fr';
	$args = array();
	print_r($wcap->get_userprefs($calid, $args));
}

function test_get_calprops($wcap){
	$calid='prenom.nom@your-domain.fr';
	print_r($wcap->get_userprefs($calid));
}

function test($wcap){
	$calid='prenom.nom@your-domain.fr';
	print_r($wcap->export($calid, $args=array()));
}
function test_search_calprops($wcap){

	$args = array(
		# 'calid' => 'prenom.nom@your-domain.fr'
		'calid'  => 0,
		'name'   => 0,
		'primaryOwner' => 0
	);

	$search_string = 'nom.prenom';
	print_r($wcap->search_calprops($search_string, $args));
}

$test = 'get_freebuzy';
# $test = 'test';
switch($test){
	case 'logout':           $wcap->logout(); break;
	case 'fetch_components': test_fetch_components($wcap); break;
	case 'get_freebuzy':     test_get_freebuzy($wcap); break;
	case 'store_event':      test_store_event($wcap); break;
	case 'get_userprefs':    test_get_userprefs($wcap); break;
	case 'get_calprops':     test_get_calprops($wcap); break;
	case 'test':             test($wcap); break;
	case 'search_calprops':  test_search_calprops($wcap); break;
	case 'test_resa_salle':  test_resa_salle($wcap); break;
	case 'test_fetchevents_by_id':  test_fetchevents_by_id($wcap); break;

}

?>

```