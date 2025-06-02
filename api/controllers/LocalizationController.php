<?php
require_once __DIR__ . '/../models/Localization.php';

class LocalizationController {
    private $localization;
    
    public function __construct() {
        $this->localization = new Localization();
    }
    
    /**
     * Get all localizations
     * GET /localizations
     * @param string $language
     * @return array
     */
    public function getAll($language) {
        if (empty($language)) {
            throw new Exception('Language is required');
        }
        return $this->localization->getAll($language);
    }

    /**
     * Set a localization
     * POST /localizations
     * @param string $language
     * @param string $message_key
     * @param string $message_text
     * @return array
     */
    public function set($language, $message_key, $message_text) {
        if (empty($language) || empty($message_key) || empty($message_text)) {
            throw new Exception('Language, message_key, and message_text are required');
        }
        return $this->localization->set($language, $message_key, $message_text);
    }
} 