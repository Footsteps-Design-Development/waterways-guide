<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\WaterWaysGuide\Administrator\View\Changelogs\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

?>
<form action="<?php echo Route::_('index.php?option=com_waterways_guide&view=changelogs'); ?>" method="post" name="adminForm" id="adminForm">
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
                    <table class="table itemList" id="changelogsList">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_WATERWAYS_GUIDE_CHANGELOGS_TABLE_CAPTION'); ?>
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col" class="w-5 text-center">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.LogID', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-15">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_WATERWAYS_GUIDE_HEADING_DATE', 'a.ChangeDate', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-15">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_WATERWAYS_GUIDE_HEADING_USER', 'a.User', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col" class="w-10">
                                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_WATERWAYS_GUIDE_HEADING_SUBJECT', 'a.Subject', $listDirn, $listOrder); ?>
                                </th>
                                <th scope="col">
                                    <?php echo Text::_('COM_WATERWAYS_GUIDE_HEADING_DESCRIPTION'); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->items as $i => $item) : ?>
                            <tr class="row<?php echo $i % 2; ?>">
                                <td class="text-center">
                                    <?php echo (int) $item->LogID; ?>
                                </td>
                                <td>
                                    <?php echo $item->ChangeDate ? HTMLHelper::_('date', $item->ChangeDate, Text::_('DATE_FORMAT_LC2')) : '-'; ?>
                                </td>
                                <td>
                                    <?php echo $this->escape($item->User); ?>
                                    <?php if ($item->MemberID) : ?>
                                        <div class="small text-muted">ID: <?php echo (int) $item->MemberID; ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo $this->escape($item->Subject); ?></span>
                                </td>
                                <td>
                                    <?php echo $this->escape($item->ChangeDesc); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php echo $this->pagination->getListFooter(); ?>
                <?php endif; ?>

                <input type="hidden" name="task" value="">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
