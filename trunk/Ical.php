<?php

class Ical {

        static function isDateTime($var) {
            return (preg_match("/^([0-9]{8})T([0-9]{6})Z?$/", $var) > 0);
        }

        /**
         * Returned date-time will always be in UTC
         */
        static function timestamp2ICal($ts, $localtime = TRUE) {
            $ts = (int) $ts;
            if ($ts < 0)
                throw new Exception("$ts: invalid timestamp");
            if ($localtime) {
                $date = date('Ymd', $ts);
                $time = date('His', $ts);
                $res = sprintf("%sT%s", $date, $time);
            }
            else {
                $date = gmdate('Ymd', $ts);
                $time = gmdate('His', $ts);
                $res = sprintf("%sT%sZ", $date, $time);
            }
            return $res;
        }

        static function iCal2Timestamp($ical) {
            if (! self::isDateTime($ical)) {
                // test for badly formed all-day event
                //print "$ical";
                $res = preg_match("/^([0-9]{4})([0-9]{2})([0-9]{2})$/",
                    $ical, $parts);
                if ($res == 0)
                    throw new Exception("$ical: invalid CalDAV Date-Time");
                else {
                    $timepart = array('00', '00', '00');
                    $parts = array_merge($parts, $timepart);
                }
            }
            else {
                $date = "([0-9]{4})([0-9]{2})([0-9]{2})";
                $time = "([0-9]{2})([0-9]{2})([0-9]{2})";
                preg_match("/^${date}T${time}(Z?)$/", $ical, $parts);
            }
            if (count($parts) == 8)
                return gmmktime($parts[4], $parts[5], $parts[6],
                    $parts[2], $parts[3], $parts[1]);
            else
                return mktime($parts[4], $parts[5], $parts[6],
                    $parts[2], $parts[3], $parts[1]);
        }

        static function datecmp($date_a, $date_b) {
            $date_a = self::iCal2Timestamp($date_a);
            $date_b = self::iCal2Timestamp(self::down_hour($date_b));
            if ($date_a < $date_b)
                $res = -1;
            else if ($date_a > $date_b)
                $res = 1;
            else
                $res = 0;
            return $res;
        }
}

?>
