<?php

error_reporting(E_ALL);
include __DIR__.'/SimpleRestClient.php';

/**
 * This example script provides two paths to configure your application's identity for
 * authentication against the SWS. Default is to use a standard identity-certificate /
 * key-file pair. Alternately you can set this to
 * @var $cert_file string|null
 * @var $key_file string|null
 */
$cert_file = null;
$key_file  = null;

/**
 * Alternately you can use a single P12 formatted certificate file
 * CAUTION: P12 certificate types are not supported by all OS/PHP/CURL platforms
 * @var $use_p12_certificate string|null
 */
$p12_cert_file = null;

/**
 * Private key and P12 files optionally have passwords. If yours does set it here.
 * @var $key_password string|null
 */
$key_password = null;

/**
 * Set additional CURL options or override the default options of the SimpleRestClient
 * If adding your own options use PHP CURLOPT_ constants as array indexes
 * @see http://php.net/manual/en/function.curl-setopt.php
 * @var $curl_opts array|null
 */
$curl_opts = null;

/**
 * SimpleRestClient provides wrapper to CURL library to interact with web service
 * @var $restclient SimpleRestClient
 */
$restclient = null;

/**
 * UW Student Web Service (SWS) XHTML response will be converted to PHP SimpleXMLElement instance
 * @var $xml SimpleXMLElement
 */
$xml = null;

/**
 * X Path queries against SimpleXMLElement will be stored in $result
 * @var $result SimpleXMLElement
 */
$result = null;

/**
 * Optionally post data. Not widely implemented in SWS, needs specific authorizations.
 * @var $post_data string|null
 */
$post_data = null;

/**
 * Identify what software is connecting to the SWS, helps them with reporting and debugging
 * @var $user_agent string
 */
$user_agent = "PHP Sample Rest Client";

/**
 * URL on the SWS that will be queried
 * @var $url string
 */
$url = 'https://ws.admin.washington.edu/student/v5/course/2015,winter,INFO,344/A.xhtml';


if ($p12_cert_file) {
    // Set up SimpleRestClient for P12 identity certificate
    $restclient = new SimpleRestClient(null, null, null, $user_agent, $curl_opts);
    $restclient->setP12CertFile($p12_cert_file, $key_password);
} else {
    // Set up SimpleRestClient using identity/key pair
    $restclient = new SimpleRestClient($cert_file, $key_file, $key_password, $user_agent, $curl_opts);
}


if ($post_data !== null) {
    $restclient->postWebRequest($url, $post_data);
} else {
    $restclient->getWebRequest($url);
}

?>
<html>
<head>
    <title>Sample App</title>
</head>
<body>
<span><b>Requested Url: </b><?php echo $url; ?> </span><br />
<br />
<span><b>Status Code: </b></span>
<div id="status_code">
    <?php
    if (!is_null($restclient))
    {
        //Get the Http_Status_Code
        echo 'Http Status Code: ' . $restclient->getStatusCode() . '<br />';
        $response = $restclient->getWebResponse();
        //Get the error message returned from web service
        $xml = simplexml_load_string($response);
        if (!is_null($xml))
        {
            $result = $xml->xpath('//div[@class="status_description"]');
            if (!is_null($result) && !empty($result))
            {
                echo 'Web Service Error Message: ' . $result[0] . '<br />';;
            }
        }
    }
    ?>
</div>
<br />
<span><b>Response: </b></span>
<div id="response">
        <textarea id="response_output" rows="10" cols="150">
            <?php
            if (!is_null($restclient))
            {
                echo $restclient->getWebResponse();
            }
            ?>
        </textarea>
</div>
<br />
<span><b>Class Detail: </b></span>
<div id="content">
    <?php
    if (!is_null($xml) && $restclient->getStatusCode() == 200 && is_null($post_data))
    {
        echo 'Title: ' . $xml->head->title . '<br />'; //Get xml data via object drill down created by simplexml
        //Get XML data via Xpath queries
        $result = $xml->xpath('//div/a[@class="PrimarySection"]/span[@class="CurriculumAbbreviation"]');
        echo "Curr Abbrev: " . $result[0]->asXml() . '<br />';
        $result = $xml->xpath('//div/a[@class="PrimarySection"]/span[@class="CourseNumber"]');
        echo "Course Number: " . $result[0]->asXml() . '<br />';
        $result = $xml->xpath('//div/a[@class="PrimarySection"]/span[@class="SectionID"]');
        echo "Section ID: " . $result[0]->asXml() . '<br />';
        $result = $xml->xpath('//div/span[@class="SLN"]');
        echo "SLN: " . $result[0]->asXml();
    }
    ?>
</div>
</body>
</html>
