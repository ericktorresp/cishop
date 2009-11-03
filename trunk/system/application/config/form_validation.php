<?php
$config = array(
	'style/add'=>array(
		array(
			'field' => 'code',
			'label' => 'lang:ui_style_code',
			'rules' => 'trim|required|integer'
		),
		array(
			'field' => 'name',
			'label' => 'lang:ui_style_name',
			'rules' => 'trim|required'
		)
	)
);