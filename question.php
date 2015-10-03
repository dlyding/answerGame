<?php

class question 
{
	public $type;
	public $title;
	public $optionA;
	public $optionB;
	public $optionC;
	public $optionD;
	public $answer;

	function __construct($type, $title, $optionA, $optionB, $optionC, $optionD, $answer)
	{
		$this->type = $type;
		$this->title = $title;
		$this->optionA = $optionA;
		$this->optionB = $optionB;
		$this->optionC = $optionC;
		$this->optionD = $optionD;
		$this->answer = $answer;
	}
	
}