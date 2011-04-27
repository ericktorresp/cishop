<?php

/***************************************************************
 * Live Admin Standalone
 * Copyright 2008-2011 Dayana Networks Ltd.
 * All rights reserved, Live Admin  is  protected  by  Canada and
 * International copyright laws. Unauthorized use or distribution
 * of  Live Admin  is  strictly  prohibited,  violators  will  be
 * prosecuted. To  obtain  a license for using Live Admin, please
 * register at http://www.liveadmin.net/register.php
 *
 * For more information please refer to Live Admin official site:
 *    http://www.liveadmin.net
 *
 * Translation service provided by Google Inc.
 ***************************************************************/

if(!defined('LIVEADMIN')) exit;
if(is_file(LIVEADMIN_GO))
{
	include_once("geoipcity.php");
	include_once("geoipregionvars.php");
}
class LV_Geoip
{
	private $GeoIpEnabled;
	function __construct()
	{
		if(!is_file(LIVEADMIN_GO))
		{
			$this->GeoIpEnabled = false;
		}
		else
		{
			$this->gi = geoip_open(LIVEADMIN_GO,GEOIP_STANDARD);
			$this->GeoIpEnabled = true;
		}
	}
	function __destruct()
	{
		if($this->GeoIpEnabled) geoip_close($this->gi);
	}
	function GetRecord($ip)
	{
		global $GEOIP_REGION_NAME;
		$rec = array ( 'country_code'=>'', 'country_code3'=>'', 'country_name'=>'', 'region'=>'', 'city'=>'', 'postal_code'=>'', 'latitude'=>0, 'longitude'=>0, 'dma_code'=>'', 'area_code'=>'', 'region_text'=>'' );
		if(!$this->GeoIpEnabled) return($rec);
		$record = geoip_record_by_addr($this->gi,$ip);
		if(!$record) return($rec);
		if(lv_property_exists($record,'country_code')) $rec['country_code'] = $record->country_code;
		if(lv_property_exists($record,'country_code3')) $rec['country_code3'] = $record->country_code3;
		if(lv_property_exists($record,'country_name')) $rec['country_name'] = $record->country_name;
		if(lv_property_exists($record,'region')) $rec['region'] = $record->region;
		if(lv_property_exists($record,'city')) $rec['city'] = $record->city;
		if(lv_property_exists($record,'postal_code')) $rec['postal_code'] = $record->postal_code;
		if(lv_property_exists($record,'latitude')) $rec['latitude'] = $record->latitude;
		if(lv_property_exists($record,'longitude')) $rec['longitude'] = $record->longitude;
		if(lv_property_exists($record,'dma_code')) $rec['dma_code'] = $record->dma_code;
		if(lv_property_exists($record,'area_code')) $rec['area_code'] = $record->area_code;
		if(isset($GEOIP_REGION_NAME[$rec['country_code']]) && isset($GEOIP_REGION_NAME[$rec['country_code']][$rec['region']])) $rec['region_text'] = $GEOIP_REGION_NAME[$rec['country_code']][$rec['region']];
		return($rec);
	}
	function GetInfoString($ip,$mode='')
	{
		$record = $this->GetRecord($ip);
		switch($mode)
		{
			default: case 'region_city_country': $RV = $record['region_text'].' '.$record['city'].' '.$record['country_name'];
			break;
			case 'region_city_country_code': $RV = $record['region_text'].' '.$record['city'].' '.$record['country_name'].' ['.$record['country_code'].']';
			break;
			case 'full_for_email': $RV = 'City                 : '.$record['city']."\n";
			$RV .= 'Region               : '.$record['region_text']."\n";
			$RV .= 'Country              : '.$record['country_name']." (".$record['country_code'].")"."\n";
			$RV .= 'Postal Code          : '.$record['postal_code']."\n";
			$RV .= 'Latitude             : '.$record['latitude']."\n";
			$RV .= 'longitude            : '.$record['longitude']."\n";
			$RV .= 'Dma Code             : '.$record['dma_code']."\n";
			$RV .= 'Area Code            : '.$record['area_code']."\n";
			break;
			case 'country_code': $RV = $record['country_code'];
			break;
			case 'country_and_code': $RV = '['.$record['country_code'].'] '.$record['country_name'];
			break;
		}
		return($RV);
	}
	function CalculateDistance($longitude_1,$latitude_1,$longitude_2,$latitude_2)
	{
		$long1=$longitude_1*M_PI/180;
		$lat1=$latitude_1*M_PI/180;
		$long2=$longitude_2*M_PI/180;
		$lat2=$latitude_2*M_PI/180;
		return(111*180/M_PI*acos(sin($lat1)*sin($lat2)+cos($lat1)*cos($lat2)*cos($long2-$long1)));
	}
}
?>