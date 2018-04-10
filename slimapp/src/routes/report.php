<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once 'HTTP/Request2.php';

$app = new \Slim\App;

//Endpoint: https://westcentralus.api.cognitive.microsoft.com/vision/v1.0

$app->get('/api/microsoft', function(Request $request, Response $response){
    $api_key = ""; //TODO: replace API Key value
    $body = '{"url": "http://www.quoteswords.com/wp-content/uploads/2017/04/Top-25-Inspiring-Quotes-for-Women-to-live-by-27-Women-quotes-Inspirational-quotes.jpg"}';
    $request = new HTTP_Request2('https://westcentralus.api.cognitive.microsoft.com/vision/v1.0/ocr');
    
    $url = $request->getUrl();

    $headers = array(
        // Request headers
        'Content-Type' => 'application/json',

        // NOTE: Replace the "Ocp-Apim-Subscription-Key" value with a valid subscription key.
        'Ocp-Apim-Subscription-Key' => $api_key,
    );

    $request->setHeader($headers);

    $parameters = array(
        // Request parameters
        'language' => 'unk',
        'detectOrientation ' => 'true',
    );
    
    $url->setQueryVariables($parameters);

    $request->setMethod(HTTP_Request2::METHOD_POST);

    // Request body
    $request->setBody($body);    // Replace "{body}" with the body. For example, '{"url": "http://www.example.com/images/image.jpg"}'
    
    try
    {
        $response = $request->send();
        
        echo $response->getBody();
    }
    catch (HttpException $ex)
    {
        echo $ex;
    }

}
);

$app->get('/api/google', function(Request $request, Response $response)
{
    $api_key = ""; //TODO: replace API Key value
    $cvurl = "https://vision.googleapis.com/v1/images:annotate?key=" . $api_key;
    $type = "TEXT_DETECTION";
    $imageuri = "http://www.quoteswords.com/wp-content/uploads/2017/04/Top-25-Inspiring-Quotes-for-Women-to-live-by-27-Women-quotes-Inspirational-quotes.jpg";
    $r_json ='{
        "requests": [
            {
                "image":{
                  "source":{
                    "imageUri":
                      "'.$imageuri.'",
                }
            },
            "features": [
                {
                    "type": "' .$type. '",
                    "maxResults": 1
                }
            ]
            }
        ]
    }';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $cvurl);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $r_json);
    $json_response = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ( $status != 200 ) {
        die("Error: $cvurl failed status $status" );
    }

    echo $json_response;

});

$app->get('/api/analyze', function(Request $request, Response $response){
    $theurl1 = "http://localhost/slimapp/public/index.php/api/google";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $theurl1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
    $json_response1 = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ( $status != 200 ) {
        die("Error: $theurl1 failed status $status" );
    }

    $theurl2 = "http://localhost/slimapp/public/index.php/api/microsoft";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $theurl2);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
    $json_response2 = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ( $status != 200 ) {
        die("Error: $theurl2 failed status $status" );
    }
    echo "{ \"google\":" . $json_response1 . ", \"microsoft\":" . $json_response2 . "}";
});
?>