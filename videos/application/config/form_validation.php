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
		/*array(
        	'field' => 'thumbnail',
            'label' => 'lang:video_thumbnail',
            'rules' => 'required'
		),*/
	),
);