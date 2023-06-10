<?php
namespace Gateways;

class JobStateGateway {
    private $db = null;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function getJobState() {
        $statement = "";

        try {
            $statement = $this->db->query($statement);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
}