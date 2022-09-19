<?php

//Get your API key on https://parser.name/
$apiKey = "";

//These classes hold all functionality.
require("src/NameParser/class.extract.php");
require("src/NameParser/class.generate.php");
require("src/NameParser/class.parse.php");

try {
    //Initialize the class that parses names.
    $name = new clsParseName($apiKey);

    //Parse a complete name.
    if ($name->fromCompleteName("Linus Benedict Torvalds")) {
        print_r($name->gender().PHP_EOL); //Returns "m".
    }

    //Parse an email address.
    if ($name->fromEmailAddress("linus.torvalds@protonmail.org")) {
        print_r($name->salutation().PHP_EOL); //Returns "Mr.".
        print_r($name->firstname().PHP_EOL); //Returns "Linus".
        print_r($name->lastname().PHP_EOL); //Returns "Torvalds".
        print_r($name->gender().PHP_EOL); //Returns "m".
    }

    //Validate a name.
    if ($name->validate("random_mnbas")) {
        var_dump($name->valid()); //Returns "bool(false)".
    }

    //Initialize the class that generates names.
    $names = new clsGenerateNames($apiKey);

    //Generate five random name.
    if ($names->generate(5)){
        foreach($names->list() as $name){
            print_r($name.PHP_EOL); //Returns five random names.
            $details = $names->details($name); //Returns all details we have on the generated name.
        }
    }

    //Initialize the class that extracts names.
    $names = new clsExtractNames($apiKey);

    //Extract names from text.
    if ($names->extract("Veteran quarterback Philip Rivers moved ahead of Matteo Federica on the NFL's all-time passing list.")) {
        foreach($names->list() as $name){
            print_r($name.PHP_EOL); //Returns "Philip Rivers" and "Matteo Federica".
            $details = $names->details($name); //Returns all data we have on the extracted name.
        }
    }

} catch (exception $e) {
    echo "Exception: ".$e->getMessage();
}

?>
