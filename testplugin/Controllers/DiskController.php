<?php
namespace Controllers;
//Include dependencies
require __DIR__ . "/../Gateways/DiskGateway.php";
use Gateways\DiskGateway;

//declare the Disck Controller
class DiskController {
    //private members
    private $db;
    private $requestMethod;
    private $start;
    private $time;
    private $diskGateway;

    //register constructor
    public function __construct($db, $requestMethod, $start, $time) {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->start = $start;
        $this->time = $time;

        //declare gateway
        $this->diskGateway = new DiskGateway($db);
    }

    //Function to handle requests made to this route
    public function processRequest() {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->start != null) {
                    //if there is a job start, get the disk usage.
                    $response = $this->getUsage($this->start, $this->time);
                } else {
                    //if no job start is sent, assume they want the disk tree
                    $response = $this->getDiskTree();
                }
        }
        //wordpress handles the response headers, just have to return the body here
        return $response['body'];
    }

    private function getUsage($start,$time) {
        // //state is uriencoded string
        // //first decode
        // $fp = urldecode($state);

        //now we need to pass the url to the gateway to get the result.

        $result = $this->diskGateway->getSize($start, $this->time);

        $response['body'] = json_encode($result);

        return $response;

    }

    //when the disk tree is requested
    private function getDiskTree() {
        //call the getTree function via the diskGateway
        $result = $this->diskGateway->getTree();
        //encode response body
        $response['body'] = json_encode($result);
        return $response;
    }

}