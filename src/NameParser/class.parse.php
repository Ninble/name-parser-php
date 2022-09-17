<?php

/* clsParseName
 *
 * Parse a name into useful components.
 * This class is meant to integrate the Name Parser API into your PHP project.
 * This class contains all functionality of the "parse" endpoint which can be found on:
 * https://parser.name/api/parse-name/
 *
 * Requires at least PHP 7.1 to run.
 */

class clsParseName {

    private $apiKey = "";
    private $response = [];
    private $salutation = "";
    private $firstname = "";
    private $nickname = "";
    private $lastname = "";
    private $gender = "";
    private $gender_formatted = "";
    private $country_code = "";
    private $country = "";
    private $currency = "";

    public function __construct(string $apiKey)
    {
        if($apiKey == ""){
            throw new InvalidArgumentException("Missing API key or API key is invalid.");
        }
        $this->apiKey = $apiKey;
    }

    public function response() : array
    {
        return $this->response;
    }

    public function salutation() : string
    {
        return $this->salutation;
    }

    public function firstname() : string
    {
        return $this->firstname;
    }

    public function nickname() : string
    {
        return $this->nickname;
    }

    public function lastname() : string
    {
        return $this->lastname;
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

    public function currency() : string
    {
        return $this->currency;
    }

    //This endpoint parses a complete name and returns the first name, middle names and last name.
    public function fromCompleteName(string $name, string $refine = "")
    {
        //Create the URL with parameters depending on the input.
        $url = "https://api.parser.name/?api_key=" . $this->apiKey . "&endpoint=parse&name=".urlencode($name);
        if($refine != ""){
            if($this->_validateCountryCode($refine)){
                $url = "https://api.parser.name/?api_key=" . $this->apiKey . "&endpoint=parse&name=".urlencode($name)."&country_code=".$refine;
            } elseif (filter_var($refine, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
                $url = "https://api.parser.name/?api_key=" . $this->apiKey . "&endpoint=parse&name=".urlencode($name)."&ip=".$refine;
            } else {
                throw new InvalidArgumentException("Invalid refine parameter. Refine parameter should be country code or IPv4 address.");
            }
        }

        //Setup cUrl.
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 1,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = json_decode(curl_exec($curl), true);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        //Process the response if possible.
        if ($status_code == 200) {
            if(isset($response['data'][0])) {
                $this->_processResponse($response['data'][0]);
            } else {
                throw new InvalidArgumentException("Response is missing data.");
            }
        } else {
            throw new InvalidArgumentException("API returned status code ".$status_code.".");
        }

        return true;
    }

    //This endpoint parses an email address and returns the first name, middle names and last name.
    public function fromEmailAddress(string $email, string $refine = "")
    {
        //Create the URL with parameters depending on the input.
        $url = "https://api.parser.name/?api_key=" . $this->apiKey . "&endpoint=parse&email=".urlencode($email);
        if($refine != ""){
            if($this->_validateCountryCode($refine)){
                $url = "https://api.parser.name/?api_key=" . $this->apiKey . "&endpoint=parse&email=".urlencode($email)."&country_code=".$refine;
            } elseif (filter_var($refine, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)){
                $url = "https://api.parser.name/?api_key=" . $this->apiKey . "&endpoint=parse&email=".urlencode($email)."&ip=".$refine;
            } else {
                throw new InvalidArgumentException("Invalid refine parameter. Refine parameter should be country code or IPv4 address.");
            }
        }

        //Setup cUrl.
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 1,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = json_decode(curl_exec($curl), true);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        //Process the response if possible.
        if ($status_code == 200) {
            if(isset($response['data'][0])) {
                $this->_processResponse($response['data'][0]);
            } else {
                throw new InvalidArgumentException("Response is missing data.");
            }
        } else {
            throw new InvalidArgumentException("API returned status code ".$status_code.".");
        }

        return true;
    }

    private function _processResponse(array $response) : void
    {
        if(isset($response['salutation']['salutation'])) {
            $this->salutation = $response['salutation']['salutation'];
        }
        if(isset($response['name']['firstname']['name'])) {
            $this->firstname = $response['name']['firstname']['name'];
        }
        if(isset($response['name']['nickname']['name'])) {
            $this->nickname = $response['name']['nickname']['name'];
        }
        if(isset($response['name']['lastname']['name'])) {
            $this->lastname = $response['name']['lastname']['name'];
        }
        if(isset($response['name']['firstname']['gender'])) {
            $this->gender = $response['name']['firstname']['gender'];
        }
        if(isset($response['name']['firstname']['gender_formatted'])) {
            $this->gender_formatted = $response['name']['firstname']['gender_formatted'];
        }
        if(isset($response['country']['country_code'])) {
            $this->country_code = $response['country']['country_code'];
        }
        if(isset($response['country']['country_code'])) {
            $this->country = $response['country']['name'];
        }
        if(isset($response['country']['currency'])) {
            $this->currency = $response['country']['currency'];
        }

        $this->response = $response;
    }

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