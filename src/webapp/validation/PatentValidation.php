<?php

namespace tdt4237\webapp\validation;

use tdt4237\webapp\models\Patent;

class PatentValidation {

    private $validationErrors = [];

    public function __construct($title, $description) {
        return $this->validate($title, $description);
    }

    public function isGoodToGo()
    {
        return \count($this->validationErrors) ===0;
    }

    public function getValidationErrors()
    {
    return $this->validationErrors;
    }

    public function validate($title, $description)
    {
        if (is_null($title)) {
            $this->validationErrors[] = 'Title must not be empty!';
        } else if (strlen(trim($title)) < 10) {
            $this->validationErrors[] = 'Title is too short!';
        } else if (strlen($title) > 100) {
            $this->validationErrors[] = 'Title is too long!';
        }

        if (is_null($description)) {
            $this->validationErrors[] = 'Description must not be empty!';
        } else if (strlen(trim($description)) < 100) {
            $this->validationErrors[] = 'Description is too short!';
        }

        return $this->validationErrors;
    }


}
