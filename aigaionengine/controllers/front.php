<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Front extends Controller {

	function Front()
	{
		parent::Controller();	
	}
	
	/** A simple front page, for testing. */
	function index()
	{
		$this->load->view('front');
	}
}
?>