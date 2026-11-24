# Name Parser for PHP

Name Parser for PHP is a PHP client for the [name parser API](https://parser.name/api/).
It splits a full name or an email address into first name, middle names and surname.
It returns the gender, the salutation, a letter salutation, titles, the suffix and the most likely country of a name.

Homepage: <https://parser.name/>

## About

This PHP project works on PHP 7.1 and above.
The classes are in the `/src/NameParser/` folder and each class contains the functionality of one API endpoint.
Copy the `/src/NameParser/` folder into any new or existing PHP project.

API documentation: <https://parser.name/api/>

## API key

The classes need an API key, which gives access to the name parser API.
Register for a free account on our website; paid subscriptions have higher limits.

Get a free API key here: <https://parser.name/dashboard/>

## Parse a name

The following example parses the name "Linus Benedict Torvalds" and returns its parts.

```php
require("src/NameParser/class.parse.php");

try {
    $name = new clsParseName('Your-API-key-here');
    if ($name->fromCompleteName("Linus Benedict Torvalds")) {
        echo($name->firstname()); //Returns "Linus".
        print_r($name->middlenames()); //Returns array("Benedict").
        echo($name->lastname()); //Returns "Torvalds".
        echo($name->suffix()); //Returns "", or "Jr." for "Martin Luther King Jr.".
        print_r($name->titles()); //Returns array(), or array("Prof.", "Dr.") for "Prof. Dr. Klaus Schmidt".
    }

} catch (exception $e) {
    echo "Exception: ".$e->getMessage();
}
```

A country code or the IP address of the person (IPv4 or IPv6) as second parameter makes the gender and country more accurate.

```php
    if ($name->fromCompleteName("Linus Benedict Torvalds", "FI")) {
        ...
    }
```

## Gender and salutation

The gender comes from the first name in the country of the complete name.
Our database holds millions of official first names with their gender, received from governments and statistics offices.

```php
    if ($name->fromCompleteName("Linus Benedict Torvalds")) {
        echo($name->gender()); //Returns "m".
        echo($name->genderFormatted()); //Returns "male".
        echo($name->salutation()); //Returns "Mr.".
    }
```

## Letter salutation

The letter salutation is the opening line of a formal email or letter in English, Dutch, German, French or Spanish.
Without a language the country of the name decides.

```php
    if ($name->fromCompleteName("Prof. Dr. Klaus Schmidt")) {
        echo($name->letter()); //Returns "Sehr geehrter Herr Prof. Dr. Schmidt".
        echo($name->letterLanguage()); //Returns "de".
    }

    $name->setLanguage("fr");
    if ($name->fromCompleteName("Prof. Dr. Klaus Schmidt")) {
        echo($name->letter()); //Returns "Monsieur le Professeur".
    }
```

## Country and name area

The country is the most likely country of origin of the name.
The name area is the group of countries that share their names through a language, such as "English" or "Spanish".
A name such as "Emma Wilson" is as common in Australia as in the United States, so the name area is more certain than the country.

```php
    if ($name->fromCompleteName("Emma Wilson")) {
        echo($name->countryCode()); //Returns "GB".
        echo($name->countryCertainty()); //Returns 39.
        echo($name->nameArea()); //Returns "English".
        echo($name->nameAreaCertainty()); //Returns 73.
        echo($name->country()); //Returns "United Kingdom".
        echo($name->currency()); //Returns "GBP".
    }
```

A phone number in international form is a hint for the country, like an IP address.

```php
    $name->setPhone("+61 2 9374 4000");
    if ($name->fromCompleteName("Emma Wilson")) {
        echo($name->countryCode()); //Returns "AU".
    }
```

## Parse an email address

Many email addresses are based on a name.
The country domain of the address, such as .com.au or .de, is used as a hint for the country.

```php
    if ($name->fromEmailAddress("linus.torvalds@protonmail.org")) {
        echo($name->salutation()); //Returns "Mr.".
        echo($name->firstname()); //Returns "Linus".
        echo($name->lastname()); //Returns "Torvalds".
    }
```

## Validate a name

Validation checks whether the first name exists and whether the name is in our list of famous, fictional and humorous names.
A name the API cannot find is not valid.

```php
    if ($name->validate("random_mnbas")) {
        var_dump($name->valid()); //Returns "bool(false)".
    }
```

## Generate random names

The generate endpoint combines a random first name and a random surname with a fictional email address, for development databases.
Without a country code the country is random, or the country of an IP address.

```php
require("src/NameParser/class.generate.php");

try {
    $names = new clsGenerateNames('Your-API-key-here');
    if ($names->generate(5, "f", "DE")) {
        foreach($names->list() as $name){
            print_r($name.PHP_EOL); //Returns five random female names from Germany.
            $details = $names->details($name); //Returns all details of the generated name.
        }
    }

} catch (exception $e) {
    echo "Exception: ".$e->getMessage();
}
```

## Rate limits

After every request `remainingHour()`, `remainingDay()` and `remainingMonth()` return the requests left in the current hour, day (free plan) or month (paid plans).

## Changes in version 2.0 (November 2026)

- New: `letter()`, `letterLanguage()`, `setLanguage()`, `titles()`, `suffix()`, `middlenames()`, `countryCertainty()`, `nameArea()`, `nameAreaCertainty()`, `setPhone()`, `remainingMonth()`.
- IPv6 addresses as refine parameter, and an IP address for `generate()`.
- `validate()` returns `valid()` false for a name the API cannot find, instead of an exception.
- `nickname()` returns the nickname, and a second request no longer keeps values of the first.
- `remainingHour()` and `remainingDay()` return an int (they failed with a TypeError before).
- `password()` always returns "": the API no longer generates passwords.
- The extract class was removed: the extract endpoint was retired on 21 November 2026.

Do you have any questions about Name Parser?
Do you have feature requests or suggestions to improve our service?
Please reach out using our contact page.

<https://parser.name/contact/>
