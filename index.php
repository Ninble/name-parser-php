<?php

//Get your API key on https://parser.name/
$apiKey = "";

//These classes hold all functionality.
require("src/NameParser/class.generate.php");
require("src/NameParser/class.parse.php");

try {
    //Initialize the class that parses names.
    $name = new clsParseName($apiKey);

    //Parse a complete name.
    if ($name->fromCompleteName("Linus Benedict Torvalds")) {
        print_r($name->gender().PHP_EOL); //Returns "m".
        print_r($name->letter().PHP_EOL); //Returns "Dear Mr. Torvalds".
        print_r($name->nameArea().PHP_EOL); //Returns "Nordic".
    }

    //Parse a name with titles in German.
    $name->setLanguage("de");
    if ($name->fromCompleteName("Prof. Dr. Klaus Schmidt")) {
        print_r($name->letter().PHP_EOL); //Returns "Sehr geehrter Herr Prof. Dr. Schmidt".
    }
    $name->setLanguage("");

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

    //Generate five random names.
    if ($names->generate(5)){
        foreach($names->list() as $generated){
            print_r($generated.PHP_EOL); //Returns five random names.
            $details = $names->details($generated); //Returns all details we have on the generated name.
        }
    }

} catch (exception $e) {
    echo "Exception: ".$e->getMessage();
}

?>
