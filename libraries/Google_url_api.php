<?php
/**
 * This is a CodeIgniter Google URL Shorten API.
 *
 * @category  API
 * @version   codeigniter-google-url-shorten 1.0 
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @author    Bo-Yi Wu <appleboy.tw@gmail.com>
 * @created   2011-02-17
 * @reference http://code.google.com/intl/zh-TW/apis/urlshortener/
 *  
 */ 

class Google_url_api {

    private $_api_url;
    private $_api_key;
    private $_enable_debug = FALSE;
    private $http_status;
    
    function __construct()
    {
        $this->_obj =& get_instance();
        $this->_obj->load->config('google_url_api');
        $this->_api_url = $this->_obj->config->item("google_api_url");
        $this->_api_key = $this->_obj->config->item("google_api_key");
        $this->_api_url = $this->_api_url . '?key=' . $this->_api_key;
    }

    /**
     * Shorten a long URL
     *      
     * @param $url
     * @return JSON object                       
     */ 
    public function shorten($url)
    {
        $response = $this->send($url, 'shorten');
        ($this->_enable_debug) AND $this->_dump($response);
        return $response;    
    }

    /**
     * Expand a short URL
     *      
     * @param $url
     * @return JSON object                       
     */     
    public function expand($url)
    {
        $response = $this->send($url, 'expand');
        ($this->_enable_debug) AND $this->_dump($response);
        return $response;    
    }

    /**
     * Look up a short URL's analytics
     *      
     * @param $url
     * @param $projection
     * FULL - returns the creation timestamp and all available analytics
     * ANALYTICS_CLICKS - returns only click counts
     * ANALYTICS_TOP_STRINGS - returns only top string counts (e.g. referrers, countries, etc)                  
     */    
    public function analytics($url, $projection = NULL)
    {
        $projection = (isset($projection)) ? $projection : 'FULL';
        $data = array("projection" => $projection);
        $response = $this->send($url, 'analytics', $data);
        ($this->_enable_debug) AND $this->_dump($response);
        return $response;    
    }        

    /**
     * function send
     * Connect to Google URL API     
     *      
     * @param $url
     * @param $action
     * @param $data     
     * 
     * return JSON object                           
     */ 
    function send($url, $action = NULL, $data = NULL) 
    {
        
        $action = (isset($action)) ? $action : 'shorten';
        $data = (isset($data)) ?  $data : array();
        
        $ch = curl_init();

        if($action == 'shorten') 
        {
            /* POST Url */
            $data['longUrl'] = $url;
            curl_setopt($ch, CURLOPT_URL, $this->_api_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        }
        else if($action == 'expand' OR $action == 'analytics')
        {
            /* Get Url*/
            $data['shortUrl'] = $url;
            $api_url = $this->_api_url . '&' . http_build_query($data);
            curl_setopt($ch, CURLOPT_URL, $api_url);
            if($this->_enable_debug) echo $api_url . "<br />";
        }

        /* WARNING: this would prevent curl from detecting a 'man in the middle' attack */
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);

        /* show error message */
        if($this->_enable_debug)
        {
            if(!curl_errno($ch))
            {
                $info = curl_getinfo($ch);
                echo 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'] . "<br />";
            }
            else
            {
                echo 'Curl error: ' . curl_error($ch) . "<br />";
            }
        }

        $this->http_response = $result;
        $this->http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        return json_decode($result);
    } 

    /**
     * function get_http_status
     * Get HTTP Status Code
     *
     * @return int
     */
    public function get_http_status()
    {
        return (int) $this->http_status;
    }

    /**
     * protected function _dump
     * dump array or object data
     *
     * @return NULL
     */    
    protected function _dump($data)
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }

    public function enable_debug($debug = true)
    {
        $this->_enable_debug = (bool) $debug;
    }    
}

/* End of file Google_url_api.php */
/* Location: ./application/libraries/Google_url_api.php */

