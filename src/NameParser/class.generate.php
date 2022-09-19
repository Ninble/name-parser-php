<?php

/* clsExtractNames
 *
 * Parse a name into useful components.
 * This class is meant to integrate the Name Parser API into your PHP project.
 * This class contains all functionality of the "generate" endpoint which can be found on:
 * https://parser.name/api/generate-random-name/
 *
 * Requires at least PHP 7.1 to run.
 */

class clsGenerateNames {

    private $apiKey = "";
    private $response = [];
    private $list = [];

    private $remaining_hour = 250; //Default rate limit
    private $remaining_day = 250; //Default rate limit

    public function __construct(string $apiKey)
    {
        if($apiKey == ""){
            throw new InvalidArgumentException("Missing API key or API key is invalid.");
        }
        $this->apiKey = $apiKey;
    }

    public function list() : array
    {
        return $this->list;
    }

    public function details($name) : array
    {
        return $this->response[$name];
    }

    public function response() : array
    {
        return $this->response;
    }

    public function remainingHour() : integer
    {
        return $this->remaining_hour;
    }

    public function remainingDay() : integer
    {
        return $this->remaining_day;
    }

    //This endpoint can generate random names for any given gender and country.
    public function generate(int $results=1, string $gender="", string $country_code = "") : bool
    {

        //Create the URL with parameters depending on the input.
        $url = "https://api.parser.name/?api_key=" . $this->apiKey . "&endpoint=generate&results=".urlencode($results);
        if(strtolower($gender) == "m" || strtolower($gender) == "f"){
            $url = $url."&gender=".urlencode($gender);
        }
        if($country_code != "" && $this->_validateCountryCode($country_code) == true){
            $url = $url."&country_code=".urlencode($country_code);
        }

        return $this->_cUrl($url);
    }

    //All public functions create a URL and use this curl function to execute the request.
    private function _cUrl(string $url) : bool {

        //Set up the cUrl request.
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 1,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        //Execute cUrl request.
        $response = curl_exec($curl);

        //Retrieve status code, header and body from response object.
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $json = json_decode($body, true);

        //Close connection.
        curl_close($curl);

        //Heading contains information about rate limits.
        $this->_processHeader($header);

        //Process the response if possible.
        if ($status_code == 200) {
            if(isset($json['data'])) {
                $this->_processResponse($json['data']);
            } else {
                throw new InvalidArgumentException("Response is missing data.");
            }
        } else {
            throw new InvalidArgumentException("API returned status code ".$status_code.".");
        }

        return true;
    }

    //Retrieve variables from API response and make it available in the class.
    private function _processResponse(array $response) : void
    {
        foreach($response as $object){
            $name = $object['name']['firstname']['name']." ".$object['name']['lastname']['name'];
            $this->list[] = $name;
            $this->response[$name] = $object;
        }
    }

    //The headers of the API response contain rate limit information.
    private function _processHeader(string $header) : void
    {
        $rows = explode(PHP_EOL, $header);
        foreach($rows as $row){
            if(stripos($row, "X-Requests-Remaining-Hour")>-1){
                $this->remaining_hour = (int) substr($row, stripos($row, ":") + 2);
            }
            if(stripos($row, "X-Requests-Remaining-Day")>-1){
                $this->remaining_day = (int) substr($row, stripos($row, ":") + 2);
            }
        }
    }

}
?>