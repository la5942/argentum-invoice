<?php
/**
 * Invoice Controller
 *
 * @package		Argentum
 * @author		Argentum Team
 * @copyright 	(c) 2008 Argentum Team
 * @license		http://www.argentuminvoice.com/license.txt
 */

class Invoice_Controller extends Website_Controller {

	/**
	 * Creates an invoice for a client
	*/
	public function create()
	{
		if (request::method() == 'post' AND ($this->input->post('tickets')) OR $this->input->post('non_hourly'))
		{
			$invoice = new Invoice_Model();

			// Save the new invoice
			$invoice->comments = $this->input->post('comments');
			$invoice->date = time();
			$invoice->client_id = $this->input->post('client_id');
			$invoice->template_name = $this->input->post('template_name');

			$invoice_id = $invoice->save();

			// Assign all the tickets to this invoice
			if ($this->input->post('tickets'))
				foreach ($this->input->post('tickets') as $ticket_id)
				{
					$ticket = new Ticket_Model($ticket_id);
					$ticket->invoiced = TRUE;
					$ticket->invoice_id = $invoice->id;
					$ticket->save();
				}

			// Assign all the tickets to this invoice
			if ($this->input->post('non_hourly'))
				foreach ($this->input->post('non_hourly') as $non_hourly_id)
				{
					$non_hourly = new Non_hourly_Model($non_hourly_id);
					$non_hourly->invoiced = TRUE;
					$non_hourly->invoice_id = $invoice->id;
					$non_hourly->save();
				}

			// Redirect to the invoice
			url::redirect('invoice/view/'.$invoice->id);
		}
		else
		{
			$client = new Client_Model($this->input->get('client_id'));

			$this->template->body = new View('admin/invoice/create');

			// Load all the unbill items for this client
			$this->template->body->projects = Auto_Modeler_ORM::factory('project')->find_unbilled($client->id);
			$this->template->body->client = $client;

			// Find all the invoice templates
			$d = dir(APPPATH.'views/invoice/templates/');
			$directories = array();
			while (($entry = $d->read()) !== FALSE)
			{
				// Don't include hidden folders
				if ($entry[0] != '.') $directories[$entry] = ucfirst(str_replace('_', ' ', $entry));
			}

			$this->template->body->templates = $directories;
		}
	}

	/**
	 * Adds a payment to an invoice
	 */
	public function post_payment($invoice_id)
	{
		$invoice_payment = new Invoice_Payment_Model();

		if (request::method() == 'post')
		{
			$invoice_payment->set_fields($this->input->post());
			$invoice_payment->date = $this->input->post('date');

			try
			{
				$invoice_payment->save();

				url::redirect('invoice/list_all');
			}
			catch (Kohana_User_Exception $e)
			{
				$this->template->body = new View('admin/invoice/post_payment');
				$this->template->body->errors = $e;
				$this->template->body->invoice_payment = $invoice_payment;
				$this->template->body->invoice_id = $this->uri->segment(4);
			}
		}
		else
		{
			$this->template->body = new View('admin/invoice/post_payment');
			$this->template->body->errors = '';
			$this->template->body->invoice_payment = $invoice_payment;
			$this->template->body->invoice_id = $this->uri->segment(4);
		}
	}

	/**
	 * Views all payments for an invoice
	 */
	public function view_payments($invoice_id)
	{
		$this->template->body = new View('admin/invoice/view_payments');
		$this->template->body->invoice_payments = Auto_Modeler_ORM::factory('invoice_payment')->fetch_some(array('invoice_id' => $invoice_id));
		$this->template->body->invoice_id = $invoice_id;
	}

	/**
	 * Deleted a payments for an invoice
	 */
	public function delete_payment()
	{
		$payment_id = $this->input->post('payment_id');
		$invoice_payment = new Invoice_Payment_Model($payment_id);
		$invoice_payment->delete();
		url::redirect('admin/invoice/view_payments/'.$invoice_payment->invoice_id);
	}
}