<?php
$config = array(
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
);