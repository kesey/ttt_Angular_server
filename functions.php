<?php
function get_price($find){
	$books = [
		"java" => 299,
		"c" => 340,
		"php" => 267
	];
	
	foreach($books as $book=>$price)
	{
		if($book == $find)
		{
			return $price;
			break;
		}
	}
}