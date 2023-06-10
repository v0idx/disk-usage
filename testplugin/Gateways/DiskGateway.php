<?php
namespace Gateways;

//include dependencies
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;

class DiskGateway {
    
    // function to get the directory tree
    //need to make this either quicker or be able to be resumed.
    public function getTree() {
        //Iterating with RII and RDI based on the ABSPATH declared as a const by wordpress
        $basedir = ABSPATH;
        $recursiveDirectory = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basedir));

        //create an array to hold all files that we encounter
        $files = array();
        //iterate over the RII and check if the item is a file.
        foreach ($recursiveDirectory as $file) {
            if (!$file->isDir()) {
                //add the file to the files array
                $files[] = $file->getPathname();
            }
        }
        //return the files
        return $files;
    }

    public function getSize($start,$time) {
        //first get the total list of files
        $files = $this->getTree();
        //set the start index to the state given by the worker.
        $index = $start;

        //timing for job timing
        $currTime = time();
        //initialize return array
        $retArr = [];
        //work clause - if it takes too long, or if it reaches eod then stop and return what it has
        while ((time() - $currTime < $time*1000) && ($index < count($files))) {
            
            //if the file doesnt exist then return -1
            if (!file_exists($files[$index])) {
                return -1;
            } else {
                //otherwise push the filesize to the return array
                array_push($retArr,filesize($files[$index]));
                //increment the index to scan the next file
                $index++;
            }
        }

        //return the highest index reached and the array of currently found files
        return [$index,$retArr];
        
    }
}