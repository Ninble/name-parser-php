# Name Parser API
Your name contains a lot of information. 
It tells if you're male or female and reveals your nationality. 
Our service turns unstructured names into actionable information.
The name parser name parsing software splits a name or email address into the first and last name. 
We can tell if a persons name is male or female and what the possible nationality is.

Homepage: <https://parser.name/>

About
------------
This PHP project works on PHP 7.1 and above.
The PHP classes are located in the /src/NameParser/ folder and work seamlessly with our name parser API.
Easily integrate the /NameParser/ folder into any new or existing PHP project. 
Each class contains the functionality for its endpoint from our API.

API documentation: <https://parser.name/api/>

API Key
-----------
To use the name parser classes you need to obtain an API Key.
The API key gives you access to our API service.
Register for a free account on our website.
If the free account is does not contain sufficient requests you can upgrade to a payed subscription.
When you initialize the class you must use your API key.

Get a free API key here: <https://parser.name/dashboard/>

Examples of how to use this class
---------
The following example will parse the name "Linus Benedict Torvalds".
After it is being parsed you get access to all the components from the name.
By providing the country code as an additional parameter the accuracy of the gender will be higher.
You can also use an IP address.
If you use the name parser on a contact or registration form you can pass the IP address of the visitor.
```php
require("src/NameParser/class.parse.php");

try {
    $name = new clsParseName('Your-API-key-here');
    if ($name->fromCompleteName("Linus Benedict Torvalds")) {
        echo($name->firstname()); //Returns "Linus"
    }

} catch (exception $e) {
    echo "Exception: ".$e->getMessage();
}
```
```php
    //By providing the country code the accuracy of the gender will be higher. 
    if ($name->fromCompleteName("Linus Benedict Torvalds", "FI")) {
        ...
    }
```
```php
    //You can also use an IP address of the visitor to increase the accuracy.
    if ($name->fromCompleteName("Linus Benedict Torvalds", "91.157.455.57")) {
        ...
    }
```

Split first and last name
---------
The following example will parse the name "Linus Benedict Torvalds".
After it is being parsed you can get all the components from the name.
In this example you'll get the first, middle and lastname.
```php
require("src/NameParser/class.parse.php");

try {
    $name = new clsParseName('Your-API-key-here');
    if ($name->fromCompleteName("Linus Benedict Torvalds")) {
        echo($name->firstname()); //Returns "Linus"
        echo($name->middlename()); //Returns "Benedict"
        echo($name->lastname()); //Returns "Torvalds"
    }

} catch (exception $e) {
    echo "Exception: ".$e->getMessage();
}
```

Gender by name
---------
We can tell a persons gender by looking at the first name. 
Our database holds millions of official first names and their gender
We received this data from governments and statistical agencies.
```php
require("src/NameParser/class.parse.php");

try {
    $name = new clsParseName('Your-API-key-here');
    if ($name->fromCompleteName("Linus Benedict Torvalds")) {
        echo($name->gender()); //Returns "m"
        echo($name->genderFormatted()); //Returns "male"
    }

} catch (exception $e) {
    echo "Exception: ".$e->getMessage();
}
```

Get Country and currency based upon name
---------
Based upon the first name and last name we can predict the country of origin of any given name. 
For training data we used the names and country codes of tens of millions publicly available social media profiles.
The following example will parse the name "Linus Benedict Torvalds" and return the country code, country and currency.
```php
require("src/NameParser/class.parse.php");

try {
    $name = new clsParseName('Your-API-key-here');
    if ($name->fromCompleteName("Linus Benedict Torvalds")) {
        echo($name->countryCode()); //Returns "SE"
        echo($name->country()); //Returns "Sweden"
        echo($name->currency()); //Returns "SEK"
    }

} catch (exception $e) {
    echo "Exception: ".$e->getMessage();
}
```

Get the name from an email address
---------
In many cases email addresses are based upon a persons name. 
Our service can extract the name from an email address and enrich it.
We also return if the email address is a personal or a business email address.
```php
require("src/NameParser/class.parse.php");

try {
    $name = new clsParseName('Your-API-key-here');
    if ($name->fromEmailAddress("linus.torvalds@protonmail.org")) {
        echo($name->salutation()); //Returns "Mr"
        echo($name->firstname()); //Returns "Linus"
        echo($name->lastname()); //Returns "Torvalds"
    }

} catch (exception $e) {
    echo "Exception: ".$e->getMessage();
}
```

Validate a complete name
---------
Our database holds millions of first names and last names. 
Improve the quality of your database. 
Check if a name exists, is not made up or misspelled.
```php
require("src/NameParser/class.parse.php");

try {
    $name = new clsParseName('Your-API-key-here');
    if ($name->validate("random_mnbas")) {
        var_dump($name->valid()); //Returns "bool(false)".
    }
    
} catch (exception $e) {
    echo "Exception: ".$e->getMessage();
}
```

Generate random names
---------
This endpoint generates names by combining a random first name and a random last name for any given country code. 
Additionally, the endpoint also generates a fictional email address and strong password making it a great solution to create development databases.
```php
require("src/NameParser/class.generate.php");

try {
    $names = new clsGenerateNames($apiKey);
    if ($names->generate(5)){
        foreach($names->list() as $name){
            print_r($name.PHP_EOL); //Returns five random names.
            $details = $names->details($name); //Returns all details we have on the generated name.
        }
    }
    
} catch (exception $e) {
    echo "Exception: ".$e->getMessage();
}
```

Extract names from text
---------
This endpoint extracts complete names from unstructured text very fast.
By using a combination of millions of first names and millions of last names we can identify and extract complete names with an extremely high precision and accuracy.

```php
require("src/NameParser/class.extract.php");

try {
    $names = new clsExtractNames($apiKey);
    if ($names->extract("Veteran quarterback Philip Rivers moved ahead of Matteo Federica on the NFL's all-time passing list.")) {
        foreach($names->list() as $name){
            print_r($name.PHP_EOL); //Returns "Philip Rivers" and "Matteo Federica".
            $details = $names->details($name); //Returns all data we have on the extracted name.
        }
    }
    
} catch (exception $e) {
    echo "Exception: ".$e->getMessage();
}
```

Do you have any questions about Name Parser?
Do you have feature requests or suggestions to improve our service?
Please reach out using our contact page.

<https://parser.name/contact/>