<?php

/* clsExtractNames
 *
 * Parse a name into useful components.
 * This class is meant to integrate the Name Parser API into your PHP project.
 * This class contains all functionality of the "extract" endpoint which can be found on:
 * https://parser.name/api/extract-names/
 *
 * Requires at least PHP 7.1 to run.
 */

class clsExtractNames {

    private $apiKey = "";
    private $min_frequency = 0;
    private $response = [];
    private $list = [];

    private $remaining_hour = 250; //Default rate limit
    private $remaining_day = 250; //Default rate limit

    public function __construct(string $apiKey, $min_frequency=100)
    {
        if($apiKey == ""){
            throw new InvalidArgumentException("Missing API key or API key is invalid.");
        }
        $this->apiKey = $apiKey;
        $this->min_frequency = $min_frequency;
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

    //This endpoint extracts all possible names from a piece of text.
    public function extract(string $text) : bool
    {
        if(strlen($text)<=2){
            throw new InvalidArgumentException("Text is to short to contain any names.");
        } elseif(strlen($text)>2048){
            throw new InvalidArgumentException("Text is to long to send to API.");
        }

        //Create the URL with parameters depending on the input.
        $url = "https://api.parser.name/?api_key=" . $this->apiKey . "&endpoint=extract&text=".urlencode($text);

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
            if($object['frequency']>=$this->min_frequency) {
                $this->list[] = $object['name'];
                $this->response[$object['name']] = $object['parsed'];
            }
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