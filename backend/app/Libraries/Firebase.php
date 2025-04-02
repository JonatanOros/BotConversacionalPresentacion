<?php
namespace App\Libraries;

use Kreait\Firebase\Factory;

class Firebase {
    private $database;

    public function __construct() {
        $firebase = (new Factory)->withServiceAccount(ROOTPATH . 'botconversacional-652e5-firebase-adminsdk-fbsvc-1810c4806f.json');
        $this->database = $firebase->createFirestore()->database();
    }

    public function getDatabase() {
        return $this->database;
    }
}
