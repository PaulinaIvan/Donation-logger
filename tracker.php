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

function main () {

    do {
        echo "\nWelcome to donation tracker!\n";
        echo "\nSelect an option: \n";
        echo "1. Import charities in CSV \n";
        echo "2. View charities \n";
        echo "3. Add charity \n";
        echo "4. Edit charity \n";
        echo "5. Delete charity \n";
        echo "6. Add donation \n\n";

        $selected_option = readline("Enter option (1-6) or enter X to exit: ");

        switch ($selected_option) {
            case '1':
                echo "You selected option 1 \n";
                break;
            case '2':
                echo "You selected option 2 \n";
                break;
            case '3':
                echo "You selected option 3 \n";
                break;
            case '4':
                echo "You selected option 4 \n";
                break;
            case '5':
                echo "You selected option 5 \n";
                break;
            case '6':
                echo "You selected option 6 \n";
                break;
            case 'X':
                exit();
            default:
                echo "\nInvalid option selected. Please try again. \n";
        }
    } while (true);
}

main ();