<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\WaterWaysGuide\Administrator\View\Guides\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
   ->useScript('multiselect');

$user      = Factory::getApplication()->getIdentity();
$userId    = $user->id;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

?>
<form action="<?php echo Route::_('index.php?option=com_waterways_guide&view=guides'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table itemList" id="guidesList">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_WATERWAYS_GUIDE_GUIDES_TABLE_CAPTION'); ?>
                        </caption>
                        <thead>
                            <tr>
                                <td class="w-1 text-center">
                                    <?php echo HTMLHelper::_('grid.checkall'); ?>
                                </td>
                                <th scope="col" class="w-1 text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.GuideStatus', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_WATERWAYS_GUIDE_HEADING_NAME', 'a.GuideName', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-15 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_WATERWAYS_GUIDE_HEADING_COUNTRY', 'country_name', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-15 d-none d-md-table-cell">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_WATERWAYS_GUIDE_HEADING_WATERWAY', 'a.GuideWaterway', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-10 d-none d-lg-table-cell">
                                    <?php echo Text::_('COM_WATERWAYS_GUIDE_HEADING_CATEGORY'); ?>
                                </th>
                                <th scope="col" class="w-5 d-none d-lg-table-cell text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.GuideID', $listDirn, $listOrder); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->items as $i => $item) :
                                $canEdit    = $user->authorise('core.edit', 'com_waterways_guide');
                                $canChange  = $user->authorise('core.edit.state', 'com_waterways_guide');

                                // Determine category label
                                $categoryLabels = [
                                    1 => 'COM_WATERWAYS_GUIDE_CATEGORY_MOORING',
                                    2 => 'COM_WATERWAYS_GUIDE_CATEGORY_HAZARD',
                                    3 => 'COM_WATERWAYS_GUIDE_CATEGORY_BRIDGE',
                                    4 => 'COM_WATERWAYS_GUIDE_CATEGORY_LOCK',
                                    5 => 'COM_WATERWAYS_GUIDE_CATEGORY_OTHER',
                                ];
                                $categoryLabel = $categoryLabels[$item->GuideCategory] ?? 'COM_WATERWAYS_GUIDE_CATEGORY_OTHER';
                            ?>
                            <tr class="row<?php echo $i % 2; ?>">
                                <td class="text-center">
                                    <?php echo HTMLHelper::_('grid.id', $i, $item->GuideID, false, 'cid', 'cb', $item->GuideName); ?>
                                </td>
                                <td class="text-center">
                                    <?php echo HTMLHelper::_('jgrid.published', $item->GuideStatus, $i, 'guides.', $canChange); ?>
                                </td>
                                <td>
                                    <?php if ($canEdit) : ?>
                                        <a href="<?php echo Route::_('index.php?option=com_waterways_guide&task=guide.edit&GuideID=' . $item->GuideID); ?>">
                                            <?php echo $this->escape($item->GuideName); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php echo $this->escape($item->GuideName); ?>
                                    <?php endif; ?>
                                    <?php if ($item->GuideSummary) : ?>
                                        <div class="small text-muted"><?php echo $this->escape(substr($item->GuideSummary, 0, 100)) . (strlen($item->GuideSummary) > 100 ? '...' : ''); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php echo $this->escape($item->country_name ?? $item->GuideCountry); ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php echo $this->escape($item->GuideWaterway); ?>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <span class="badge bg-secondary"><?php echo Text::_($categoryLabel); ?></span>
                                </td>
                                <td class="d-none d-lg-table-cell text-center">
                                    <?php echo (int) $item->GuideID; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php echo $this->pagination->getListFooter(); ?>
                <?php endif; ?>

                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
