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
class Tracker {
    private $charities = array();
    private $donations = array();

    public function addCharity($name, $email) {
        $charity = new Charity($name, $email);
        $this->charities[$charity->id] = $charity;
    }

    private function isCharityDuplicated($name, $email) {
        foreach ($this->charities as $charity) {
            if ($charity->name === $name && 
                $charity->representative_email === $email) {
                return true;
            }
        }
        return false;
    }

    public function importCharitiesFromCsv($filename) {
        if (!file_exists($filename)) {
            echo "File $filename not found.\n";
            return;
        }
        
        $file = fopen($filename, 'r');
        fgetcsv($file);

        $new_charities = 0;
        
        while (($row = fgetcsv($file)) !== false) {
            if (count($row) != 2) {
                echo "Skipping invalid row: " . implode(', ', $row) . "\n";
                continue;
            }
            list($name, $email) = $row;
            if ($this->isCharityDuplicated($name, $email)) {
                echo "Skipping duplicated charity: $name\n";
                continue;
            } else {
                $new_charities++;
                $this->addCharity($name, $email);
            }
        }
        
        fclose($file);

        echo ("$new_charities new charities added.\n");
    }

    public function viewCharities() {
        if (empty($this->charities)) {
            echo "No charities found.\n";
        } else {
            foreach ($this->charities as $charity) {
                echo "$charity->name\n";
            }
        }
    }

}

function main () {
    $tracker = new Tracker();

    echo "\nWelcome to donation tracker!\n";

    do {
        echo "\nSelect an option: \n";
        echo "1. Import charities from CSV \n";
        echo "2. View charities \n";
        echo "3. Add charity \n";
        echo "4. Edit charity \n";
        echo "5. Delete charity \n";
        echo "6. Add donation \n\n";

        $selected_option = readline("Enter option (1-6) or enter X to exit: ");

        switch ($selected_option) {
            case '1':
                echo "You selected option 1 \n\n";
                $csv_filename = readline("Enter the CSV filename: ");
                $tracker->importCharitiesFromCsv($csv_filename);
                break;
            case '2':
                echo "You selected option 2 \n";
                $tracker->viewCharities();
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