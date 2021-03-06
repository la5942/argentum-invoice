<?php
/**
 * Invoice model
 *
 * @package		Argentum
 * @author		Argentum Team
 * @copyright 	(c) 2008 Argentum Team
 * @license		http://www.argentuminvoice.com/license.txt
 */

class Invoice_Model extends Auto_Modeler_ORM
{
	protected $table_name = 'invoices';
	
	protected $data = array('id' => NULL,
	                        'title' => '',
	                        'date' => '',
	                        'comments' => '',
	                        'client_id' => '');

	/**
	 * Calculates the total income for this invoice
	 * @return double
	 */
	public function total_income()
	{
		if ( ! $this->data['id'])
			return 0;

		$total_income = 0;
		// Find all the tickets and get the total cost of them
		foreach ($this->find_related('tickets') as $ticket)
			$total_income+=$ticket->rate*$ticket->total_time;

		foreach ($this->find_related('non_hourly') as $non_hourly)
			$total_income+=$non_hourly->cost;

		$total_income+=$this->find_sales_tax();

		return $total_income;
	}

	/**
	 * Calculates the total amount paid for this invoice
	 * @return double
	 */
	public function total_paid()
	{
		$total_paid = 0;

		foreach (Auto_Modeler_ORM::factory('invoice_payment')->fetch_some(array('invoice_id' => $this->data['id'])) as $payment)
			$total_paid+=$payment->amount;

		return $total_paid;
	}

	/**
	 * Finds the operation types for this invoice
	 * @return array operation type id with values as the total data for that operation
	 * @todo Fix bug if ticket rate is different than operation type rate.
	 */
	public function find_operation_types()
	{
		if ( ! $this->data['id'])
			return array('name' => '', 'rate' => 0, 'time' => 0);

		$return = array();

		foreach ($this->find_related('tickets') as $ticket)
		{
			if ( ! isset($return[$ticket->operation_type->id]))
				$return[$ticket->operation_type->id] = array('name' => $ticket->operation_type->name,
				                                             'rate' => $ticket->rate,
				                                             'time' => $ticket->total_time);
			else
				$return[$ticket->operation_type->id]['time']+=$ticket->total_time;
		}

		return $return;
	}

	/**
	 * Finds all invoices created between a start and end date
	 * @param $start_date
	 * @param $end_date
	 * @return object
	 */
	public function find_invoices_by_date($start_date, $end_date)
	{
		$sql = 'SELECT * FROM `invoices` WHERE `date` >= ? AND `date` < ? ORDER BY `id` DESC';
		return $this->db->query($sql, array($start_date, $end_date))->result(TRUE, 'Invoice_Model');
	}

	/**
	 * Finds all invoices created between a start and end date
	 * @return float
	 */
	public function find_sales_tax()
	{
		$total = 0;

		foreach ($this->find_related('tickets') as $ticket)
		{
			if ($ticket->project->taxable)
				$total+=($ticket->project->client->tax_rate/100)*$ticket->total_time*$ticket->rate;
		}

		return $total;
	}
}