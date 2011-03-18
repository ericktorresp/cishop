<?php
$config = array(
	'user/register' => array(
		array(
			'field'=>'username',
			'label'=>'lang:username',
			'rules'=>'trim|required|min_length[5]|max_length[12]|xss_clean'
		),
		array(
			'field'=>'password',
			'label'=>'lang:password',
			'rules'=>'trim|required|matches[passwordconfirm]'
		),
		array(
			'field'=>'passwordconfirm',
			'label'=>'lang:password_confirm',
			'rules'=>'trim|required'
		),
		array(
			'field'=>'email',
			'label'=>'lang:email',
			'rules'=>'trim|required|valid_email'
		),
		array(
			'field'=>'agree',
			'label'=>'lang:agree',
			'rules'=>'required'
		)
	),
	'user/step2' => array(
		array(
			'field'=>'gender',
			'label'=>'lang:Gender',
			'rules'=>'required'
		),
		array(
			'field'=>'fname',
			'label'=>'lang:First Name',
			'rules'=>'trim|required'
		),
		array(
			'field'=>'lname',
			'label'=>'lang:Last Name',
			'rules'=>'trim|required'
		),
		array(
			'field'=>'phone',
			'label'=>'lang:Phone',
			'rules'=>'trim|required|regex_match[/^[0-9]{7,11}$/]|min_length[7]|max_length[11]'
		),
		array(
			'field'=>'birth_month',
			'label'=>'lang:Month',
			'rules'=>'required'
		),
		array(
			'field'=>'birth_day',
			'label'=>'lang:Day',
			'rules'=>'required'
		),
		array(
			'field'=>'birth_year',
			'label'=>'lang:Year',
			'rules'=>'required'
		),
		array(
			'field'=>'street_addr',
			'label'=>'lang:Street address',
			'rules'=>'trim|required'
		),
		array(
			'field'=>'city',
			'label'=>'lang:City',
			'rules'=>'trim|required'
		),
		array(
			'field'=>'zip',
			'label'=>'lang:Zip',
			'rules'=>'trim|required|regex_match[/^[0-9]{6}$/]'
		),
		array(
			'field'=>'country',
			'label'=>'lang:Country',
			'rules'=>'required'
		),
	),
	'user/step3' => array(
	
	),

);