<?php

//Get your API key on https://parser.name/
$apiKey = "1bf857dfdabefb9a33cab34be438dbc2";

//This class holds all functionality you need.
require("src/NameParser/class.account.php");
require("src/NameParser/class.parse.php");
//require("src/NameParser/class.validate.php");
//require("src/NameParser/class.generate.php");

try {

    //Initialize the class as a new instance.
    $name = new clsParseName($apiKey);

    //Parse a complete name.
    if ($name->fromCompleteName("Linus Benedict Torvalds")) {
        print_r($name->gender()); //Returns "m"
    }

    //Parse an email address.
    if ($name->fromEmailAddress("linus.torvalds@protonmail.org")) {
        print_r($name->salutation()); //Returns "Mr."
        print_r($name->firstname()); //Returns "Linus"
        print_r($name->lastname()); //Returns "Torvalds"
        print_r($name->response()); //Returns "m"
    }

} catch (exception $e) {
    echo "Ecexption: ".$e->getMessage();
}

?>