<?php

class Donation {

    public $id;
    public $donor_name;
    public $amount;
    public $charity_id;
    public $date_time;

    public function __construct($donor_name, $amount, $charity_id) {
        $this->id = uniqid();
        $this->donor_name = $donor_name;
        $this->amount = $amount;
        $this->charity_id = $charity_id;
        $this->date_time = date('Y-m-d H:i:s');
    }
}

class Charity {

    public $id;
    public $name;
    public $representative_email;

    public function __construct($name, $representative_email) {
        $this->id = uniqid();
        $this->name = $name;
        $this->representative_email = $representative_email;
    }
}