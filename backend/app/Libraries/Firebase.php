<?php
namespace App\Libraries;

use Kreait\Firebase\Factory;

class Firebase {
    private $database;

    public function __construct() {
        $firebase = (new Factory)->withServiceAccount(APPPATH . '../../botconversacional-652e5-firebase-adminsdk-fbsvc-8cd38fbb87.json');
        $this->database = $firebase->createFirestore()->database();
    }

    public function getDatabase() {
        return $this->database;
    }
}
