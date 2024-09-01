<?php

class Donation {

    private $id;
    private $donor_name;
    private $amount;
    private $charity_id;
    private $date_time;

    public function __construct($donor_name, $amount, $charity_id) {
        $this->id = uniqid();
        $this->__set('donor_name', $donor_name);
        $this->__set('amount', $amount);
        $this->__set('charity_id', $charity_id);
        $this->date_time = date('Y-m-d H:i:s');
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        throw new Exception("Property $property does not exist on Charity.");
    }

    public function __set($name, $value) {
        switch ($name) {
            case 'donor_name':
                $this->donor_name = $value;
                break;
            case 'amount':
                $this->amount = $value;
                break;
            case 'charity_id':
                $this->charity_id = $value;
                break;
            default:
                throw new Exception("Cannot set property $name on Donation.");
        }
    }

    public function donationToRow($id_length, $max_name_length) {
        return sprintf("| %-{$id_length}s | %-{$max_name_length}s | %-10s | %-{$id_length}s | %-19s |\n", 
            $this->id, 
            $this->donor_name, 
            $this->amount, 
            $this->charity_id, 
            $this->date_time
        );  
    }

}

class Charity {

    private $id;
    private $name;
    private $representative_email;

    public function __construct($name, $representative_email) {
        $this->id = uniqid();
        $this->__set('name', $name);
        $this->__set('representative_email', $representative_email);
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        throw new Exception("Property $property does not exist on Charity.");
    }

    public function __set($property, $value) {
        switch ($property) {
            case 'name':
                $this->name = $value;
                break;
            case 'representative_email':
                $this->representative_email = $value;
                break;
            default:
                throw new Exception("Cannot set property $property on Charity.");
        }
    }

    public function charityToRow($id_length, $max_name_length, $max_email_length) {
        return sprintf("| %-{$id_length}s | %-{$max_name_length}s | %-{$max_email_length}s |\n", 
            $this->id, 
            $this->name, 
            $this->representative_email
        );  
    }
}

class Tracker {
    private $charities = array();
    private $donations = array();
    private $max_name_length;
    private $max_email_length;
    private $id_length;

    public function __construct($max_name_length, $max_email_length, $id_length) {
        $this->__set('max_name_length', $max_name_length);
        $this->__set('max_email_length', $max_email_length);
        $this->__set('id_length', $id_length);
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        throw new Exception("Property $property does not exist on Charity.");
    }

    public function __set($property, $value) {
        switch ($property) {
            case 'max_name_length':
                $this->max_name_length = $value;
                break;
            case 'max_email_length':
                $this->max_email_length = $value;
                break;
            case 'id_length':
                $this->id_length = $value;
                break;
            default:
                throw new Exception("Cannot set property $property on Charity.");
        }
    }

    private function isNameValid($name) {
        if (!preg_match("/^[a-zA-Z-' ]*$/",$name) || strlen($name) > $this->max_name_length) {
            echo "Invalid name: $name\n";
            return false;
        }
        return true;
    }

    private function isEmailValid($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > $this->max_email_length) {
            echo "Invalid email: $email\n";
            return false;
        }
        return true;
    }

    private function isAmountValid($amount) {
        if (!is_numeric($amount) || $amount <= 0 || !preg_match('/^\d+(\.\d{1,2})?$/', $amount)) {
            echo "Invalid donation amount: $amount\n";
            return false;
        }
        return true;
    }

    private function isCharityDuplicated($name, $email) {
        foreach ($this->charities as $charity) {
            if (is_object($charity) && $charity->name === $name && 
                $charity->representative_email === $email) {
                echo "Duplicated charity: $name\n";
                return true;
            }
        }
        return false;
    }

    private function printCharitiesHeader() {
        $this->printCharitiesFooter();
        echo sprintf("| %-13s | %-30s | %-30s |\n", "ID", "Name", "Representative Email");
        $this->printCharitiesFooter();
    }

    private function printCharitiesFooter() {
        echo sprintf("+-%s-+-%s-+-%s-+\n", str_repeat('-', $this->id_length), str_repeat('-', $this->max_name_length), str_repeat('-', $this->max_email_length));
    }

    private function printDonationsHeader() {
        $this->printDonationsFooter();
        echo sprintf("| %-13s | %-30s | %-10s | %-13s | %-19s |\n", "ID", "Donor Name", "Amount", "Charity ID", "Date Time");
        $this->printDonationsFooter();
    }

    private function printDonationsFooter() {
        echo sprintf("+-%s-+-%s-+-%s-+-%s-+-%s-+\n", str_repeat('-', $this->id_length), str_repeat('-', $this->max_name_length), str_repeat('-', 10), str_repeat('-', $this->id_length), str_repeat('-', 19));
    }

    private function printDonationsByCharity($charity_id) {
        $this->printDonationsHeader();
        foreach ($this->donations as $donation) {
            if ($donation->charity_id === $charity_id) {
                echo $donation->donationToRow($this->id_length, $this->max_name_length);
            }
        }
        $this->printDonationsFooter();
    }

    public function addCharity($name,  $email) {
        if ($this->isCharityDuplicated($name, $email)) {
            return false;
        } else if (!$this->isNameValid($name) || !$this->isEmailValid($email)) {
            return false;
        } else {
            $charity = new Charity($name, $email);
            $this->charities[$charity->id] = $charity;
            return true;
        }
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
                echo $charity->charityToRow($this->id_length, $this->max_name_length, $this->max_email_length);         
            }
            $this->printCharitiesFooter();
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
        } else if (!$this->isAmountValid($amount)) {
            return;
        }
        $donation = new Donation($donor_name, $amount, $charity_id);
        $this->donations[] = $donation;
        echo "Donation added succesfully.\n";
    }

}

function main () {
    $tracker = new Tracker(id_length: 13, max_name_length: 30, max_email_length: 30);

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