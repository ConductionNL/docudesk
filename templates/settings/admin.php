<?php
/**
 * @copyright Copyright (c) 2024 Conduction B.V. <info@conduction.nl>
 * @license EUPL-1.2
 */

use OCP\Util;

$appId = OCA\DocuDesk\AppInfo\Application::APP_ID;
Util::addScript('docudesk', 'docudesk-settings');
Util::addStyle('docudesk', 'main');

/** @var array $_ */
/** @var \OCP\IL10N $l */
?>

<div id="docudesk-admin-settings" class="section">
    <h2><?php p($l->t('DocuDesk Settings')); ?></h2>
    
    <p class="settings-hint">
        <?php p($l->t('Configure DocuDesk integration settings.')); ?>
    </p>
    
    <div class="docudesk-settings-section">
        <h3><?php p($l->t('Presidio Integration')); ?></h3>
        
        <div class="docudesk-setting">
            <label for="docudesk-presidio-analyzer-url">
                <?php p($l->t('Presidio Analyzer API URL')); ?>
            </label>
            <input type="text" id="docudesk-presidio-analyzer-url" name="presidioAnalyzerUrl" 
                   value="<?php p($_['presidioAnalyzerUrl']); ?>" 
                   placeholder="http://presidio-api:8080/analyze" />
            <p class="docudesk-setting-hint">
                <?php p($l->t('URL of the Presidio Analyzer API for entity detection.')); ?>
            </p>
        </div>
        
        <div class="docudesk-setting">
            <label for="docudesk-presidio-anonymizer-url">
                <?php p($l->t('Presidio Anonymizer API URL')); ?>
            </label>
            <input type="text" id="docudesk-presidio-anonymizer-url" name="presidioAnonymizerUrl" 
                   value="<?php p($_['presidioAnonymizerUrl']); ?>" 
                   placeholder="http://presidio-api:8080/anonymize" />
            <p class="docudesk-setting-hint">
                <?php p($l->t('URL of the Presidio Anonymizer API for text anonymization.')); ?>
            </p>
        </div>
        
        <div class="docudesk-setting">
            <label for="docudesk-confidence-threshold">
                <?php p($l->t('Confidence Threshold')); ?>
            </label>
            <input type="number" id="docudesk-confidence-threshold" name="confidenceThreshold" 
                   value="<?php p($_['confidenceThreshold']); ?>" 
                   min="0" max="1" step="0.1" />
            <p class="docudesk-setting-hint">
                <?php p($l->t('Minimum confidence level (0.0 - 1.0) for entity detection.')); ?>
            </p>
        </div>
    </div>
    
    <div class="docudesk-settings-section">
        <h3><?php p($l->t('Feature Settings')); ?></h3>
        
        <div class="docudesk-setting">
            <input type="checkbox" id="docudesk-enable-reporting" name="enableReporting" 
                   class="checkbox" <?php if ($_['enableReporting']) { p('checked'); } ?> />
            <label for="docudesk-enable-reporting">
                <?php p($l->t('Enable Reporting')); ?>
            </label>
            <p class="docudesk-setting-hint">
                <?php p($l->t('Enable document analysis and reporting features.')); ?>
            </p>
        </div>
        
        <div class="docudesk-setting">
            <input type="checkbox" id="docudesk-enable-anonymization" name="enableAnonymization" 
                   class="checkbox" <?php if ($_['enableAnonymization']) { p('checked'); } ?> />
            <label for="docudesk-enable-anonymization">
                <?php p($l->t('Enable Anonymization')); ?>
            </label>
            <p class="docudesk-setting-hint">
                <?php p($l->t('Enable document anonymization features.')); ?>
            </p>
        </div>
        
        <div class="docudesk-setting">
            <input type="checkbox" id="docudesk-store-original-text" name="storeOriginalText" 
                   class="checkbox" <?php if ($_['storeOriginalText']) { p('checked'); } ?> />
            <label for="docudesk-store-original-text">
                <?php p($l->t('Store Original Text')); ?>
            </label>
            <p class="docudesk-setting-hint">
                <?php p($l->t('Store original text to enable de-anonymization. Disable for higher security but no de-anonymization.')); ?>
            </p>
        </div>
    </div>
    
    <div class="docudesk-settings-section">
        <h3><?php p($l->t('Document Processing')); ?></h3>
        
        <div class="docudesk-setting">
            <p class="docudesk-setting-info">
                <?php p($l->t('DocuDesk uses various libraries to extract text from documents:')); ?>
            </p>
            <ul class="docudesk-info-list">
                <li><?php p($l->t('PDF files: Smalot PDF Parser')); ?></li>
                <li><?php p($l->t('Word documents: PHPWord')); ?></li>
                <li><?php p($l->t('Excel spreadsheets: PHPSpreadsheet')); ?></li>
                <li><?php p($l->t('PowerPoint presentations: PHPPresentation')); ?></li>
            </ul>
        </div>
    </div>
    
    <div class="docudesk-settings-buttons">
        <button id="docudesk-save-settings" class="primary">
            <?php p($l->t('Save Settings')); ?>
        </button>
        <span id="docudesk-settings-status" class="msg"></span>
    </div>
</div>