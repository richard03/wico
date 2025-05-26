<?php
require_once __DIR__ . '/../models/Localization.php';

class LocalizationController {
    private $localization;
    
    public function __construct() {
        $this->localization = new Localization();
    }
    
    public function getAll($language) {
        if (empty($language)) {
            throw new Exception('Language is required');
        }
        return $this->localization->getAll($language);
    }
} 