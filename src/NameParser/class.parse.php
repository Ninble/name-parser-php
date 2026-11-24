<?php

/* clsParseName
 *
 * Parse a name into useful components.
 * This class is meant to integrate the Name Parser API into your PHP project.
 * This class contains all functionality of the "parse" endpoint which can be found on:
 * https://parser.name/api/parse-name/
 *
 * Version 2.0 (23 November 2026): letter salutation in five languages, titles, suffix, middle names, name area, the
 * phone hint and IPv6 addresses. password() is kept for older code but always returns "" (the API no longer returns
 * a password).
 *
 * Requires at least PHP 7.1 to run.
 */

class clsParseName {

    private $apiKey = "";
    private $language = "";
    private $phone = "";
    private $use_strict_validation = false;
    private $response = array();
    private $salutation = "";
    private $letter = "";
    private $letter_language = "";
    private $titles = array();
    private $firstname = "";
    private $middlenames = array();
    private $nickname = "";
    private $lastname = "";
    private $suffix = "";
    private $gender = "";
    private $gender_formatted = "";
    private $country_code = "";
    private $country = "";
    private $country_certainty = 0;
    private $name_area = "";
    private $name_area_certainty = 0;
    private $currency = "";
    private $email = "";
    private $valid = false;

    private $remaining_hour = 250; //Default rate limit
    private $remaining_day = 250; //Default rate limit
    private $remaining_month = 0;

    public function __construct(string $apiKey)
    {
        if($apiKey == ""){
            throw new InvalidArgumentException("Missing API key or API key is invalid.");
        }
        $this->apiKey = $apiKey;
    }

    //The language of the letter salutation: en, nl, de, fr or es; "" lets the country of the name decide.
    public function setLanguage(string $language) : void
    {
        $language = strtolower(trim($language));
        if ($language != "" && in_array($language, array("en", "nl", "de", "fr", "es")) == false) {
            throw new InvalidArgumentException("The language must be one of en, nl, de, fr or es.");
        }
        $this->language = $language;
    }

    //A phone number in international form (+61 2 9374 4000) as a hint for the country of the name.
    public function setPhone(string $phone) : void
    {
        $this->phone = trim($phone);
    }

    public function response() : array
    {
        return $this->response;
    }

    public function salutation() : string
    {
        return $this->salutation;
    }

    //The opening line of a formal letter, such as "Dear Mr. Smith" or "Geachte heer Jansen".
    public function letter() : string
    {
        return $this->letter;
    }

    public function letterLanguage() : string
    {
        return $this->letter_language;
    }

    //Every title in the name, such as array("Prof.", "Dr.").
    public function titles() : array
    {
        return $this->titles;
    }

    public function firstname() : string
    {
        return $this->firstname;
    }

    public function middlenames() : array
    {
        return $this->middlenames;
    }

    public function nickname() : string
    {
        return $this->nickname;
    }

    public function lastname() : string
    {
        return $this->lastname;
    }

    //Jr., Sr. or a Roman numeral such as III.
    public function suffix() : string
    {
        return $this->suffix;
    }

    public function gender() : string
    {
        return $this->gender;
    }

    public function genderFormatted() : string
    {
        return $this->gender_formatted;
    }

    public function countryCode() : string
    {
        return $this->country_code;
    }

    public function country() : string
    {
        return $this->country;
    }

    public function countryCertainty() : int
    {
        return $this->country_certainty;
    }

    //The group of countries that share their names through a language, such as "English" or "Spanish".
    public function nameArea() : string
    {
        return $this->name_area;
    }

    public function nameAreaCertainty() : int
    {
        return $this->name_area_certainty;
    }

    public function currency() : string
    {
        return $this->currency;
    }

    public function email() : string
    {
        return $this->email;
    }

    //Kept for older code: the API no longer returns a password.
    public function password() : string
    {
        return "";
    }

    public function valid() : bool
    {
        return $this->valid;
    }

    public function remainingHour() : int
    {
        return $this->remaining_hour;
    }

    public function remainingDay() : int
    {
        return $this->remaining_day;
    }

    public function remainingMonth() : int
    {
        return $this->remaining_month;
    }

    //This endpoint parses a complete name and returns the first name, middle names and last name.
    //Refine is a country code (US) or an IP address (IPv4 or IPv6) of the person.
    public function fromCompleteName(string $name, string $refine = "") : bool
    {
        return $this->_cUrl($this->_buildUrl(array("name" => $name), $refine));
    }

    //This endpoint parses an email address and returns the first name, middle names and last name.
    public function fromEmailAddress(string $email, string $refine = "") : bool
    {
        return $this->_cUrl($this->_buildUrl(array("email" => $email), $refine));
    }

    //This endpoint parses a name and validates the name as well.
    public function validate(string $name, string $refine = "", bool $use_strict_validation = false) : bool
    {
        //Strict validation = false : The first name must exist, the lastname could be possible and the complete name should not be listed in our database with famous, fictional and humorous names.
        //Strict validation = true  : The first name and last name must exist (validated = true) and the complete name should not be listed in our database with famous, fictional and humorous names.
        $this->use_strict_validation = $use_strict_validation;

        return $this->_cUrl($this->_buildUrl(array("validate" => "true", "name" => $name), $refine), true);
    }

    //The request URL with the parameters, the refine value, the language and the phone number.
    private function _buildUrl(array $parameters, string $refine) : string
    {
        $parameters = array_merge(array("api_key" => $this->apiKey, "endpoint" => "parse"), $parameters);
        if ($refine != "") {
            if ($this->_validateCountryCode($refine) == true) {
                $parameters['country_code'] = $refine;
            } elseif (filter_var($refine, FILTER_VALIDATE_IP) !== false) {
                $parameters['ip'] = $refine;
            } else {
                throw new InvalidArgumentException("Invalid refine parameter. Refine parameter should be a country code or an IP address.");
            }
        }
        if ($this->language != "") {
            $parameters['language'] = $this->language;
        }
        if ($this->phone != "") {
            $parameters['phone'] = $this->phone;
        }
        return "https://api.parser.name/?" . http_build_query($parameters);
    }

    //All public functions create a URL and use this curl function to execute the request.
    //With $validating, a name the API cannot find or does not accept (status 404 or 400) is not valid instead of an
    //exception, so validate("random_mnbas") gives valid() false.
    private function _cUrl(string $url, bool $validating = false) : bool {

        //Set up the cUrl request.
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        //Execute cUrl request.
        $response = curl_exec($curl);
        if ($response === false) {
            $message = curl_error($curl);
            throw new RuntimeException("The request to the API failed: " . $message);
        }

        //Retrieve status code, header and body from response object.
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $json = json_decode($body, true);

        //Close connection (only needed before PHP 8, deprecated since PHP 8.5).
        if (PHP_VERSION_ID < 80000) {
            curl_close($curl);
        }

        //Heading contains information about rate limits.
        $this->_processHeader($header);

        //Process the response if possible.
        if ($status_code == 200) {
            if(isset($json['data'][0])) {
                $this->_processResponse($json['data'][0]);
            } else {
                throw new InvalidArgumentException("Response is missing data.");
            }
        } elseif ($validating == true && ($status_code == 400 || $status_code == 404)) {
            $this->_processResponse(array());
            $this->valid = false;
        } else {
            $message = "API returned status code " . $status_code . ".";
            if (isset($json['error']) == true && $json['error'] != "") {
                $message = $message . " " . $json['error'];
            }
            throw new InvalidArgumentException($message);
        }

        return true;
    }

    //The headers of the API response contain rate limit information.
    private function _processHeader(string $header) : void
    {
        $rows = explode("\n", $header);
        foreach($rows as $row){
            if(stripos($row, "X-Requests-Remaining-Hour:") === 0){
                $this->remaining_hour = (int) trim(substr($row, stripos($row, ":") + 1));
            }
            if(stripos($row, "X-Requests-Remaining-Day:") === 0){
                $this->remaining_day = (int) trim(substr($row, stripos($row, ":") + 1));
            }
            if(stripos($row, "X-Requests-Remaining-Month:") === 0){
                $this->remaining_month = (int) trim(substr($row, stripos($row, ":") + 1));
            }
        }
    }

    //Retrieve variables from API response and make it available in the class. Every value is reset first, so a
    //second request never keeps a value of the first.
    private function _processResponse(array $response) : void
    {
        $this->salutation = "";
        $this->letter = "";
        $this->letter_language = "";
        $this->titles = array();
        $this->firstname = "";
        $this->middlenames = array();
        $this->nickname = "";
        $this->lastname = "";
        $this->suffix = "";
        $this->gender = "";
        $this->gender_formatted = "";
        $this->country_code = "";
        $this->country = "";
        $this->country_certainty = 0;
        $this->name_area = "";
        $this->name_area_certainty = 0;
        $this->currency = "";
        $this->email = "";

        if(isset($response['salutation']['salutation'])) {
            $this->salutation = (string)$response['salutation']['salutation'];
        }
        if(isset($response['salutation']['letter'])) {
            $this->letter = (string)$response['salutation']['letter'];
        }
        if(isset($response['salutation']['letter_language'])) {
            $this->letter_language = (string)$response['salutation']['letter_language'];
        }
        if(isset($response['titles']) && is_array($response['titles'])) {
            foreach ($response['titles'] as $title) {
                if (isset($title['abbriviation'])) {
                    $this->titles[] = (string)$title['abbriviation'];
                }
            }
        }
        if(isset($response['name']['firstname']['name'])) {
            $this->firstname = (string)$response['name']['firstname']['name'];
        }
        if(isset($response['name']['middlenames']) && is_array($response['name']['middlenames'])) {
            foreach ($response['name']['middlenames'] as $middlename) {
                if (isset($middlename['name'])) {
                    $this->middlenames[] = (string)$middlename['name'];
                }
            }
        }
        if(isset($response['name']['nickname'])) {
            $this->nickname = (string)$response['name']['nickname'];
        }
        if(isset($response['name']['lastname']['name'])) {
            $this->lastname = (string)$response['name']['lastname']['name'];
        }
        if(isset($response['name']['suffix'])) {
            $this->suffix = (string)$response['name']['suffix'];
        }
        if(isset($response['name']['firstname']['gender'])) {
            $this->gender = (string)$response['name']['firstname']['gender'];
        }
        if(isset($response['name']['firstname']['gender_formatted'])) {
            $this->gender_formatted = (string)$response['name']['firstname']['gender_formatted'];
        }
        if(isset($response['country']['country_code'])) {
            $this->country_code = (string)$response['country']['country_code'];
        }
        if(isset($response['country']['name'])) {
            $this->country = (string)$response['country']['name'];
        }
        if(isset($response['country']['country_certainty'])) {
            $this->country_certainty = (int)$response['country']['country_certainty'];
        }
        if(isset($response['country']['name_area'])) {
            $this->name_area = (string)$response['country']['name_area'];
        }
        if(isset($response['country']['name_area_certainty'])) {
            $this->name_area_certainty = (int)$response['country']['name_area_certainty'];
        }
        if(isset($response['country']['currency'])) {
            $this->currency = (string)$response['country']['currency'];
        }
        if(isset($response['email']['address'])) {
            $this->email = (string)$response['email']['address'];
        }

        //If response contains validation section then it was used.
        if(isset($response['validation'])){
            if($this->use_strict_validation == true){
                $this->valid = (bool) $response['validation']['pass_strict'];
            } else {
                $this->valid = (bool) $response['validation']['pass_loose'];
            }
        } else {
            $this->valid = true;
        }

        $this->response = $response;
    }

    //Check if a given country code is valid.
    private function _validateCountryCode(string $country_code) : bool
    {
        $country_codes = array(
            'Afghanistan' => 'AF',
            'Albania' => 'AL',
            'Algeria' => 'DZ',
            'American Samoa' => 'AS',
            'Andorra' => 'AD',
            'Angola' => 'AO',
            'Anguilla' => 'AI',
            'Antarctica' => 'AQ',
            'Antigua and Barbuda' => 'AG',
            'Argentina' => 'AR',
            'Armenia' => 'AM',
            'Aruba' => 'AW',
            'Australia' => 'AU',
            'Austria' => 'AT',
            'Azerbaijan' => 'AZ',
            'Bahamas' => 'BS',
            'Bahrain' => 'BH',
            'Bangladesh' => 'BD',
            'Barbados' => 'BB',
            'Belarus' => 'BY',
            'Belgium' => 'BE',
            'Belize' => 'BZ',
            'Benin' => 'BJ',
            'Bermuda' => 'BM',
            'Bhutan' => 'BT',
            'Bolivia' => 'BO',
            'Bosnia and Herzegovina' => 'BA',
            'Botswana' => 'BW',
            'Bouvet Island' => 'BV',
            'Brazil' => 'BR',
            'British Antarctic Territory' => 'BQ',
            'British Indian Ocean Territory' => 'IO',
            'British Virgin Islands' => 'VG',
            'Brunei' => 'BN',
            'Bulgaria' => 'BG',
            'Burkina Faso' => 'BF',
            'Burundi' => 'BI',
            'Cambodia' => 'KH',
            'Cameroon' => 'CM',
            'Canada' => 'CA',
            'Canton and Enderbury Islands' => 'CT',
            'Cape Verde' => 'CV',
            'Cayman Islands' => 'KY',
            'Central African Republic' => 'CF',
            'Chad' => 'TD',
            'Chile' => 'CL',
            'China' => 'CN',
            'Paracel Islands' => 'CN',
            'Christmas Island' => 'CX',
            'Cocos [Keeling] Islands' => 'CC',
            'Colombia' => 'CO',
            'Comoros' => 'KM',
            'Congo - Brazzaville' => 'CG',
            'Congo - Kinshasa' => 'CD',
            'Congo (Kinshasa)' => 'CG',
            'Congo (Brazzaville)' => 'CG',
            'Cook Islands' => 'CK',
            'Costa Rica' => 'CR',
            'Croatia' => 'HR',
            'Cuba' => 'CU',
            'Cyprus' => 'CY',
            'Czech Republic' => 'CZ',
            'Côte d’Ivoire' => 'CI',
            'Cote D\'Ivoire' => 'CI',
            'Denmark' => 'DK',
            'Djibouti' => 'DJ',
            'Dominica' => 'DM',
            'Dominican Republic' => 'DO',
            'Dronning Maud Land' => 'NQ',
            'Ecuador' => 'EC',
            'Egypt' => 'EG',
            'El Salvador' => 'SV',
            'Equatorial Guinea' => 'GQ',
            'Eritrea' => 'ER',
            'Estonia' => 'EE',
            'Ethiopia' => 'ET',
            'Falkland Islands' => 'FK',
            'Faroe Islands' => 'FO',
            'Fiji' => 'FJ',
            'Finland' => 'FI',
            'France' => 'FR',
            'French Guiana' => 'GF',
            'French Polynesia' => 'PF',
            'French Southern Territories' => 'TF',
            'French Southern and Antarctic Territories' => 'FQ',
            'Gabon' => 'GA',
            'Gambia' => 'GM',
            'Georgia' => 'GE',
            'Germany' => 'DE',
            'Ghana' => 'GH',
            'Gibraltar' => 'GI',
            'Greece' => 'GR',
            'Greenland' => 'GL',
            'Grenada' => 'GD',
            'Guadeloupe' => 'GP',
            'Guam' => 'GU',
            'Guatemala' => 'GT',
            'Guernsey' => 'GG',
            'Guinea' => 'GN',
            'Guinea-Bissau' => 'GW',
            'Guyana' => 'GY',
            'Haiti' => 'HT',
            'Heard Island and McDonald Islands' => 'HM',
            'Honduras' => 'HN',
            'Hong Kong SAR China' => 'HK',
            'Hong Kong' => 'HK',
            'Hungary' => 'HU',
            'Iceland' => 'IS',
            'India' => 'IN',
            'Indonesia' => 'ID',
            'Iran' => 'IR',
            'Iraq' => 'IQ',
            'Ireland' => 'IE',
            'Isle of Man' => 'IM',
            'Israel' => 'IL',
            'Italy' => 'IT',
            'Jamaica' => 'JM',
            'Japan' => 'JP',
            'Jersey' => 'JE',
            'Johnston Island' => 'JT',
            'Jordan' => 'JO',
            'Kazakhstan' => 'KZ',
            'Kenya' => 'KE',
            'Kiribati' => 'KI',
            'Kuwait' => 'KW',
            'Kyrgyzstan' => 'KG',
            'Laos' => 'LA',
            'Latvia' => 'LV',
            'Lebanon' => 'LB',
            'Lesotho' => 'LS',
            'Liberia' => 'LR',
            'Libya' => 'LY',
            'Liechtenstein' => 'LI',
            'Lithuania' => 'LT',
            'Luxembourg' => 'LU',
            'Macau SAR China' => 'MO',
            'Macedonia' => 'MK',
            'Madagascar' => 'MG',
            'Malawi' => 'MW',
            'Malaysia' => 'MY',
            'Maldives' => 'MV',
            'Mali' => 'ML',
            'Malta' => 'MT',
            'Marshall Islands' => 'MH',
            'Martinique' => 'MQ',
            'Mauritania' => 'MR',
            'Mauritius' => 'MU',
            'Mayotte' => 'YT',
            'Metropolitan France' => 'FX',
            'Mexico' => 'MX',
            'Micronesia' => 'FM',
            'Federated States of Micronesia' => 'FM',
            'Midway Islands' => 'MI',
            'Moldova' => 'MD',
            'Monaco' => 'MC',
            'Mongolia' => 'MN',
            'Montenegro' => 'ME',
            'Montserrat' => 'MS',
            'Morocco' => 'MA',
            'Mozambique' => 'MZ',
            'Myanmar [Burma]' => 'MM',
            'Myanmar' => 'MM',
            'Namibia' => 'NA',
            'Nauru' => 'NR',
            'Nepal' => 'NP',
            'Netherlands' => 'NL',
            'Netherlands Antilles' => 'AN',
            'Neutral Zone' => 'NT',
            'New Caledonia' => 'NC',
            'New Zealand' => 'NZ',
            'Nicaragua' => 'NI',
            'Niger' => 'NE',
            'Nigeria' => 'NG',
            'Niue' => 'NU',
            'Norfolk Island' => 'NF',
            'North Korea' => 'KP',
            'North Vietnam' => 'VD',
            'Northern Mariana Islands' => 'MP',
            'Norway' => 'NO',
            'Oman' => 'OM',
            'Pacific Islands Trust Territory' => 'PC',
            'Pakistan' => 'PK',
            'Palau' => 'PW',
            'Palestinian Territories' => 'PS',
            'Panama' => 'PA',
            'Panama Canal Zone' => 'PZ',
            'Papua New Guinea' => 'PG',
            'Paraguay' => 'PY',
            'People\'s Democratic Republic of Yemen' => 'YD',
            'Peru' => 'PE',
            'Philippines' => 'PH',
            'Pitcairn Islands' => 'PN',
            'Poland' => 'PL',
            'Portugal' => 'PT',
            'Puerto Rico' => 'PR',
            'Qatar' => 'QA',
            'Romania' => 'RO',
            'Russia' => 'RU',
            'Rwanda' => 'RW',
            'Réunion' => 'RE',
            'Reunion' => 'RE',
            'Saint-Denis' => 'RE',
            'Saint Barthélemy' => 'BL',
            'Saint Helena' => 'SH',
            'Saint Kitts and Nevis' => 'KN',
            'Saint Lucia' => 'LC',
            'Saint Martin' => 'MF',
            'Saint Pierre and Miquelon' => 'PM',
            'Saint Vincent and the Grenadines' => 'VC',
            'Samoa' => 'WS',
            'San Marino' => 'SM',
            'Saudi Arabia' => 'SA',
            'Senegal' => 'SN',
            'Serbia' => 'RS',
            'Serbia and Montenegro' => 'CS',
            'Seychelles' => 'SC',
            'Sierra Leone' => 'SL',
            'Singapore' => 'SG',
            'Slovakia' => 'SK',
            'Slovenia' => 'SI',
            'Solomon Islands' => 'SB',
            'Somalia' => 'SO',
            'South Africa' => 'ZA',
            'South Georgia and the South Sandwich Islands' => 'GS',
            'South Korea' => 'KR',
            'Spain' => 'ES',
            'Sri Lanka' => 'LK',
            'Sudan' => 'SD',
            'South Sudan' => 'SD',
            'Suriname' => 'SR',
            'Svalbard and Jan Mayen' => 'SJ',
            'Swaziland' => 'SZ',
            'Sweden' => 'SE',
            'Malmö' => 'SE',
            'Switzerland' => 'CH',
            'Syria' => 'SY',
            'São Tomé and Príncipe' => 'ST',
            'Sao Tome and Principe' => 'ST',
            'Taiwan' => 'TW',
            'Tajikistan' => 'TJ',
            'Tanzania' => 'TZ',
            'Thailand' => 'TH',
            'Timor-Leste' => 'TL',
            'East Timor' => 'TL',
            'Togo' => 'TG',
            'Tokelau' => 'TK',
            'Tonga' => 'TO',
            'Trinidad and Tobago' => 'TT',
            'Tunisia' => 'TN',
            'Turkey' => 'TR',
            'Turkmenistan' => 'TM',
            'Turks and Caicos Islands' => 'TC',
            'Tuvalu' => 'TV',
            'U.S. Minor Outlying Islands' => 'UM',
            'U.S. Miscellaneous Pacific Islands' => 'PU',
            'U.S. Virgin Islands' => 'VI',
            'Uganda' => 'UG',
            'Ukraine' => 'UA',
            'United Arab Emirates' => 'AE',
            'United Kingdom' => 'GB',
            'United States' => 'US',
            'Uruguay' => 'UY',
            'Uzbekistan' => 'UZ',
            'Vanuatu' => 'VU',
            'Vatican City' => 'VA',
            'Venezuela' => 'VE',
            'Vietnam' => 'VN',
            'Wake Island' => 'WK',
            'Wallis and Futuna' => 'WF',
            'Western Sahara' => 'EH',
            'Yemen' => 'YE',
            'Zambia' => 'ZM',
            'Zimbabwe' => 'ZW',
            'Åland Islands' => 'AX',
        );
        if(in_array($country_code, $country_codes)){
            return true;
        }

        return false;
    }

}
?>