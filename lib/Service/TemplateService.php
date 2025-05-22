<?php
/**
 * DocuDesk is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * DocuDesk is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * EUPL-1.2 License for more details.
 *
 * @category Service
 * @package  OCA\DocuDesk\Service
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * @link     https://www.DocuDesk.nl
 */

namespace OCA\DocuDesk\Service;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCA\DocuDesk\Db\Template;
use OCA\DocuDesk\Db\TemplateMapper;
use OCP\IAppConfig;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * Service for managing document templates
 *
 * This service provides functionality to create, retrieve, and manage document templates.
 * It utilizes Twig for template rendering and supports various output formats such as PDF and DOCX.
 * The service interacts with the database through the TemplateMapper to persist template data.
 *
 * @category Service
 * @package  OCA\DocuDesk\Service
 * @author   Conduction B.V. <info@conduction.nl>
 * @license  EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * @link     https://www.DocuDesk.nl
 */
class TemplateService
{
    /**
     * @var TemplateMapper 
     */
    private $mapper;
    
    /**
     * @var Environment 
     */
    private $twig;

    /**
     * Constructor for TemplateService
     *
     * @param TemplateMapper $mapper Database mapper for templates
     */
    public function __construct(TemplateMapper $mapper)
    {
        $this->mapper = $mapper;
        
        // Initialize Twig with array loader for dynamic templates
        $loader = new ArrayLoader([]);
        $this->twig = new Environment($loader);
    }

    /**
     * Creates a new template
     *
     * @param  string $name         Template name
     * @param  string $content      Template content in Twig format
     * @param  string $category     Template category
     * @param  string $outputFormat Desired output format (pdf/docx)
     * @return Template The created template
     */
    public function createTemplate(string $name, string $content, string $category, string $outputFormat): Template
    {
        $template = new Template();
        $template->setName($name);
        $template->setContent($content);
        $template->setCategory($category);
        $template->setOutputFormat($outputFormat);
        
        return $this->mapper->insert($template);
    }

    /**
     * Renders a template with provided data
     *
     * @param  int    $templateId ID of the template to render
     * @param  array  $data       Data to render the template with
     * @param  string $format     Output format (pdf/docx)
     * @return string Rendered content
     * @throws DoesNotExistException If template not found
     */
    public function renderTemplate(int $templateId, array $data, string $format): string
    {
        $template = $this->mapper->find($templateId);
        
        // Create a new template in Twig environment
        $this->twig->setLoader(
            new ArrayLoader(
                [
                'template' => $template->getContent()
                ]
            )
        );
        
        // Render the template with provided data
        $html = $this->twig->render('template', $data);
        
        // Convert to requested format
        if ($format === 'pdf') {
            return $this->convertToPdf($html);
        } elseif ($format === 'docx') {
            return $this->convertToWord($html);
        }
        
        return $html;
    }

    /**
     * Converts HTML content to PDF
     *
     * @param  string $html HTML content to convert
     * @return string PDF content
     */
    private function convertToPdf(string $html): string
    {
        // @TODO: Implement PDF conversion using library like wkhtmltopdf
        // This is a placeholder for actual implementation
        return $html;
    }

    /**
     * Converts HTML content to Word document
     *
     * @param  string $html HTML content to convert
     * @return string Word document content
     */
    private function convertToWord(string $html): string
    {
        // @TODO: Implement Word conversion using PHPWord
        // This is a placeholder for actual implementation
        return $html;
    }
} 