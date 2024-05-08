<?php

// site/src/View/Membership/HtmlView.php
namespace Waterwaysguide\Component\Waterways_guide\Site\View\Membership;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    protected $item;

    public function display($tpl = null)
    {
        $this->item = $this->get('Item');

        if (count($errors = $this->get('Errors')))
        {
            throw new \Exception(implode("\n", $errors), 500);
        }

        parent::display($tpl);
    }
}
