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
    
    private $ID_LENGTH = 13;
    private $MAX_NAME_LENGTH = 30;
    private $MAX_EMAIL_LENGTH = 30;
    private $charities = array();
    private $donations = array();

    public function addCharity($name, $email) {
        if ($this->isCharityDuplicated($name, $email)) {
            echo "Duplicated charity: $name\n";
            return false;
        } else if (!$this->isNameValid($name) || !$this->isEmailValid($email)) {
            return false;
        } else {
            $charity = new Charity($name, $email);
            $this->charities[$charity->id] = $charity;
            return true;
        }
    }

    private function isCharityDuplicated($name, $email) {
        foreach ($this->charities as $charity) {
            if ($charity->name === $name && 
                $charity->representative_email === $email) {
                echo "Duplicated charity: $name\n";
                return true;
            }
        }
        return false;
    }

    private function isNameValid($name) {
        if (!preg_match("/^[a-zA-Z-' ]*$/",$name) || strlen($name) > $this->MAX_NAME_LENGTH) {
            echo "Invalid name: $name\n";
            return false;
        }
        return true;
    }
    private function isEmailValid($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > $this->MAX_EMAIL_LENGTH) {
            echo "Invalid email: $email\n";
            return false;
        }
        return true;
    }
    private function printCharitiesHeader() {
        echo sprintf("+-%s-+-%s-+-%s-+\n", str_repeat('-', $this->ID_LENGTH), str_repeat('-', $this->MAX_NAME_LENGTH), str_repeat('-', $this->MAX_EMAIL_LENGTH));
        echo sprintf("| %-{$this->ID_LENGTH}s | %-{$this->MAX_NAME_LENGTH}s | %-{$this->MAX_EMAIL_LENGTH}s |\n", "ID", "Name", "Representative Email");
        echo sprintf("+-%s-+-%s-+-%s-+\n", str_repeat('-', $this->ID_LENGTH), str_repeat('-', $this->MAX_NAME_LENGTH), str_repeat('-', $this->MAX_EMAIL_LENGTH));
    }

    private function printDonationsHeader() {
        echo sprintf("+-%s-+-%s-+-%s-+-%s-+-%s-+\n", str_repeat('-', $this->ID_LENGTH), str_repeat('-', $this->MAX_NAME_LENGTH), str_repeat('-', 10), str_repeat('-', $this->ID_LENGTH), str_repeat('-', 19));
        echo sprintf("| %-{$this->ID_LENGTH}s | %-{$this->MAX_NAME_LENGTH}s | %-10s | %-{$this->ID_LENGTH}s | %-19s |\n", "ID", "Donor Name", "Amount", "Charity ID", "Date Time");
        echo sprintf("+-%s-+-%s-+-%s-+-%s-+-%s-+\n", str_repeat('-', $this->ID_LENGTH), str_repeat('-', $this->MAX_NAME_LENGTH), str_repeat('-', 10), str_repeat('-', $this->ID_LENGTH), str_repeat('-', 19));
    }

    private function printDonationsByCharity($charity_id) {
        $this->printDonationsHeader();
        foreach ($this->donations as $donation) {
            if ($donation->charity_id === $charity_id) {
                echo sprintf("| %-{$this->ID_LENGTH}s | %-{$this->MAX_NAME_LENGTH}s | %-10s | %-{$this->ID_LENGTH}s | %-19s |\n", 
                    $donation->id, 
                    $donation->donor_name, 
                    $donation->amount, 
                    $donation->charity_id, 
                    $donation->date_time
                );
            }
        }
        echo sprintf("+-%s-+-%s-+-%s-+-%s-+-%s-+\n", str_repeat('-', $this->ID_LENGTH), str_repeat('-', $this->MAX_NAME_LENGTH), str_repeat('-', 10), str_repeat('-', $this->ID_LENGTH), str_repeat('-', 19));
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
            if ($this->addCharity($name, $email)) {
                $new_charities++;
            }
        }
        
        fclose($file);

        echo ("$new_charities new charities added.\n");
    }

    public function viewCharities() {
        if (empty($this->charities)) {
            echo "No charities found.\n";
        } else {
            $this->printCharitiesHeader();
            foreach ($this->charities as $charity) {
                echo sprintf("| %-{$this->ID_LENGTH}s | %-{$this->MAX_NAME_LENGTH}s | %-{$this->MAX_EMAIL_LENGTH}s |\n", 
                $charity->id, 
                $charity->name, 
                $charity->representative_email
                );            
            }
            echo sprintf("+-%s-+-%s-+-%s-+\n\n", str_repeat('-', $this->ID_LENGTH), str_repeat('-', $this->MAX_NAME_LENGTH), str_repeat('-', $this->MAX_EMAIL_LENGTH));
            $charity_id = readline("Enter charity ID to view donations (press enter to skip): ");
            if (isset($this->charities[$charity_id])) {
                echo "Donations for charity: " . $this->charities[$charity_id]->name . "\n";
                $this->printDonationsByCharity($charity_id);
            } else if ($charity_id == '') {
                return;
            } else {
                echo "Charity not found.\n";
            }   
        }
    }

    public function editCharity($charity_id, $name = null, $email = null) {
        if (!isset($this->charities[$charity_id])) {
            echo "Charity not found.\n";
            return;
        }
        $charity = $this->charities[$charity_id];
        $oldCharity = clone $charity;
        if ($name !== null) {
            if ($this->isNameValid($name)) {
                $charity->name = $name;
            } else {
                return;
            }
        }
        if ($email !== null) {
            if ($this->isEmailValid($email)) {
                $charity->representative_email = $email;
            } else {
                return;
            }
        }
        echo "Charity updated succesfully.\n";
    }

    public function deleteCharity($charity_id) {
        if (!isset($this->charities[$charity_id])) {
            echo "Charity not found.\n";
            return;
        }
        unset($this->charities[$charity_id]);
        echo "Charity deleted succesfully.\n";
    }

    public function addDonation($donor_name, $amount, $charity_id) {
        if (!isset($this->charities[$charity_id])) {
            echo "Charity not found.\n";
            return;
        }
        if (!$this->isNameValid($donor_name)) {
            return;
        } else if (!is_numeric($amount) || $amount <= 0 || !preg_match('/^\d+(\.\d{1,2})?$/', $amount)) {
            echo "Invalid donation amount.\n";
            return;
        }
        $donation = new Donation($donor_name, $amount, $charity_id);
        $this->donations[] = $donation;
        echo "Donation added succesfully.\n";
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
        echo "6. Add donation \n";
        echo "7. Exit \n\n";

        $selected_option = readline("Enter option (1-7): ");

        switch ($selected_option) {
            case '1':
                $csv_filename = readline("Enter the CSV filename: ");
                $tracker->importCharitiesFromCsv($csv_filename);
                break;
            case '2':
                $tracker->viewCharities();
                break;
            case '3':
                $name = readline("Enter charity name: ");
                $email = readline("Enter representative email: ");
                if ($tracker->addCharity($name, $email)) {
                    echo "Charity added successfully.\n";
                }
                break;
            case '4':
                $charity_id = readline("Enter charity ID to edit: ");
                $name = readline("Enter new name (press enter to skip): ");
                $email = readline("Enter new email (press enter to skip): ");
                $tracker->editCharity($charity_id, $name ?: null, $email ?: null);
                break;
            case '5':
                $charity_id = readline("Enter charity ID to delete: ");
                $tracker->deleteCharity($charity_id);
                break;
            case '6':
                $donor_name = readline("Enter donor name: ");
                $amount = readline("Enter donation amount: ");
                $charity_id = readline("Enter charity ID: ");
                $tracker->addDonation($donor_name, $amount, $charity_id);
                break;
            case '7':
                exit();
            default:
                echo "\nInvalid option selected. Please try again. \n";
        }
    } while (true);
}

main ();