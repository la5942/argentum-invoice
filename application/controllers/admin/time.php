<?php

class Time_Controller extends Website_Controller {

	public function add($ticket_id)
	{
		$time = new Time_Model();
		$time->ticket_id = $ticket_id;

		if ( ! $_POST) // Display the form
		{
			$this->template->body = new View('admin/time/add');
			$this->template->body->errors = '';
			$this->template->body->time = $time;
		}
		else
		{
			$time->set_fields($this->input->post());

			try
			{
				$time->save();
				url::redirect('ticket/'.($time->ticket->complete ? 'closed' : 'active').'/'.$time->ticket->project->id);
			}
			catch (Kohana_User_Exception $e)
			{
				$this->template->body = new View('admin/time/add');
				$this->template->body->time = $time;
				$this->template->body->errors = $e;
				$this->template->body->set($this->input->post());
			}
		}
	}

	public function delete()
	{
		$time = new Time_Model($this->input->post('id'));
		$time->delete();
		url::redirect('ticket/'.($time->ticket->complete ? 'closed' : 'active').'/'.$time->ticket->project->id);
	}
}