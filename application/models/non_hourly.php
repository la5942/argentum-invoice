<?php

class Non_hourly_Model extends Auto_Modeler_ORM
{
	protected $table_name = 'non_hourly';
	
	protected $data = array('id' => '',
	                        'project_id' => '',
	                        'quantity' => '',
	                        'description' => '',
	                        'cost' => '',
	                        'invoiced' => FALSE,
	                        'invoice_id' => FALSE,
	                        'creation_date' => '');

	protected $rules = array('project_id' => array('required'),
	                         'quantity' => array('required', 'numeric'),
	                         'description' => array('required'),
	                         'cost' => array('required', 'numeric'));
}