<?php
$config = array(
	'members/login'=>array(
		array(
        	'field' => 'username',
            'label' => 'lang:username',
            'rules' => 'required'
		),
		array(
        	'field' => 'password',
            'label' => 'lang:password',
            'rules' => 'required'
		),
	),
	'video/add'=>array(
		array(
        	'field' => 'title',
            'label' => 'lang:video_title',
            'rules' => 'required'
		),
		array(
        	'field' => 'width',
            'label' => 'lang:video_width',
            'rules' => 'required'
		),
		array(
        	'field' => 'height',
            'label' => 'lang:video_height',
            'rules' => 'required'
		),
		array(
        	'field' => 'duration',
            'label' => 'lang:video_duration',
            'rules' => 'required'
		),
		array(
        	'field' => 'mime',
            'label' => 'lang:video_mime',
            'rules' => 'required'
		),
	),
	'video/edit'=>array(
		array(
        	'field' => 'title',
            'label' => 'lang:video_title',
            'rules' => 'required'
		),
		array(
        	'field' => 'width',
            'label' => 'lang:video_width',
            'rules' => 'required'
		),
		array(
        	'field' => 'height',
            'label' => 'lang:video_height',
            'rules' => 'required'
		),
		array(
        	'field' => 'duration',
            'label' => 'lang:video_duration',
            'rules' => 'required'
		),
		array(
        	'field' => 'mime',
            'label' => 'lang:video_mime',
            'rules' => 'required'
		),
	),
	'category/add'=>array(
		array(
        	'field' => 'ctitle',
            'label' => 'lang:video_title',
            'rules' => 'required'
		),
	),
	'category/edit'=>array(
		array(
        	'field' => 'ctitle',
            'label' => 'lang:video_title',
            'rules' => 'required'
		),
	),
	'server/add'=>array(
		array(
        	'field' => 'domain',
            'label' => 'lang:domain',
            'rules' => 'required'
		),
		array(
        	'field' => 'ip',
            'label' => 'lang:ip',
            'rules' => 'valid_ip'
		),
	),
	'server/edit'=>array(
		array(
        	'field' => 'domain',
            'label' => 'lang:domain',
            'rules' => 'required'
		),
		array(
        	'field' => 'ip',
            'label' => 'lang:ip',
            'rules' => 'valid_ip'
		),
	),
	'actor/add'=>array(
		array(
        	'field' => 'name',
            'label' => 'lang:name',
            'rules' => 'required'
		),
		array(
        	'field' => 'gender',
            'label' => 'lang:gender',
            'rules' => 'required'
		),
		array(
        	'field' => 'nationality',
            'label' => 'lang:nationality',
            'rules' => 'required'
		),
	),
	'actor/edit'=>array(
		array(
        	'field' => 'name',
            'label' => 'lang:name',
            'rules' => 'required'
		),
		array(
        	'field' => 'gender',
            'label' => 'lang:gender',
            'rules' => 'required'
		),
		array(
        	'field' => 'nationality',
            'label' => 'lang:nationality',
            'rules' => 'required'
		),
	),
);