<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Waterways_guide
 * @author     Russell English <russell@footsteps-design.co.uk>
 * @copyright  2024 Russell English
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Waterwaysguide\Component\Waterways_guide\Site\Controller;

// src/Controller/DisplayController.php
namespace Waterwaysguide\Component\Waterways_guide\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
    public function display($cachable = false, $urlparams = [])
    {
        $view = $this->input->get('view', 'wwg');
        $this->input->set('view', $view);
        parent::display($cachable, $urlparams);
    }
}
