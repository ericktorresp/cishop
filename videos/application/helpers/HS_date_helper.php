<?php
function get_start_and_end_date_from_week ($w)
{
	$y = date("Y", time());
	$o = 6;

	$days = ($w - 1) * 7 + $o;

	$firstdayofyear = getdate(mktime(0,0,0,1,1,$y));
	if ($firstdayofyear["wday"] == 0) $firstdayofyear["wday"] += 7;
	# in getdate, Sunday is 0 instead of 7
	$firstmonday = getdate(mktime(0,0,0,1,1-$firstdayofyear["wday"]+1,$y));
	$calcdate = getdate(mktime(0,0,0,$firstmonday["mon"], $firstmonday["mday"]+$days,$firstmonday["year"]));

	$sday = $calcdate["mday"];
	$smonth = $calcdate["mon"];
	$syear = $calcdate["year"];


	$timestamp['start_timestamp'] =  mktime(0, 0, 0, $smonth, $sday, $syear);
	$timestamp['end_timestamp'] =  $timestamp['start_timestamp'] + (60*60*24*7);

	return array('start'=>$syear.'-'.$smonth.'-'.$sday,'end'=>date('Y-m-d',$timestamp['end_timestamp']));
}