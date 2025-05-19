<?php
namespace OCA\DocuDesk\Sections;

use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class DocuDeskAdmin implements IIconSection
{
    private IL10N $l;
    private IURLGenerator $urlGenerator;

    public function __construct(IL10N $l, IURLGenerator $urlGenerator)
    {
        $this->l = $l;
        $this->urlGenerator = $urlGenerator;
    }

    public function getIcon(): string
    {
        return $this->urlGenerator->imagePath('core', 'actions/settings-dark.svg');
    }

    public function getID(): string
    {
        return 'docudesk';
    }

    public function getName(): string
    {
        return $this->l->t('DocuDesk');
    }

    public function getPriority(): int
    {
        return 97;
    }
}