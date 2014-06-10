<?php

//
// Creates a SQL string literal that is immune to SQL injection
// attacks from the given string variable.
//
// $value: The value to create a SQL string literal to represent.
//
// Brad Kimmel - 2007/09/14
//
function makeSqlString($value) {
	return "'" . str_replace("'", "''", $value) . "'";
}

?>
