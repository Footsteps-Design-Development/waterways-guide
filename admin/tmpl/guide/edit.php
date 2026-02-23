<?php

declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\WaterWaysGuide\Administrator\View\Guide\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
   ->useScript('form.validate');

$app   = Factory::getApplication();
$input = $app->getInput();

?>
<form action="<?php echo Route::_('index.php?option=com_waterways_guide&layout=edit&GuideID=' . (int) $this->item->GuideID); ?>"
      method="post" name="adminForm" id="adminForm" class="form-validate">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'basic', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'basic', Text::_('COM_WATERWAYS_GUIDE_FIELDSET_BASIC')); ?>
        <div class="row">
            <div class="col-lg-9">
                <fieldset class="adminform">
                    <?php echo $this->form->renderField('GuideName'); ?>
                    <?php echo $this->form->renderField('GuideSummary'); ?>
                </fieldset>
            </div>
            <div class="col-lg-3">
                <fieldset class="form-vertical">
                    <?php echo $this->form->renderField('GuideCountry'); ?>
                    <?php echo $this->form->renderField('GuideWaterway'); ?>
                    <?php echo $this->form->renderField('GuideCategory'); ?>
                    <?php echo $this->form->renderField('GuideStatus'); ?>
                </fieldset>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'location', Text::_('COM_WATERWAYS_GUIDE_FIELDSET_LOCATION')); ?>
        <div class="row">
            <div class="col-lg-6">
                <fieldset class="adminform">
                    <?php echo $this->form->renderField('GuideLat'); ?>
                    <?php echo $this->form->renderField('GuideLong'); ?>
                    <?php echo $this->form->renderField('GuideLatLong'); ?>
                </fieldset>
            </div>
            <div class="col-lg-6">
                <fieldset class="adminform">
                    <?php echo $this->form->renderField('GuideLocation'); ?>
                    <?php echo $this->form->renderField('GuideRef'); ?>
                    <?php echo $this->form->renderField('GuideOrder'); ?>
                </fieldset>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_WATERWAYS_GUIDE_FIELDSET_DETAILS')); ?>
        <div class="row">
            <div class="col-lg-6">
                <fieldset class="adminform">
                    <?php echo $this->form->renderField('GuideMooring'); ?>
                    <?php echo $this->form->renderField('GuideFacilities'); ?>
                    <?php echo $this->form->renderField('GuideCodes'); ?>
                </fieldset>
            </div>
            <div class="col-lg-6">
                <fieldset class="adminform">
                    <?php echo $this->form->renderField('GuideCosts'); ?>
                    <?php echo $this->form->renderField('GuideRating'); ?>
                    <?php echo $this->form->renderField('GuideAmenities'); ?>
                </fieldset>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'additional', Text::_('COM_WATERWAYS_GUIDE_FIELDSET_ADDITIONAL')); ?>
        <div class="row">
            <div class="col-12">
                <fieldset class="adminform">
                    <?php echo $this->form->renderField('GuideContributors'); ?>
                    <?php echo $this->form->renderField('GuideRemarks'); ?>
                    <?php echo $this->form->renderField('GuideDocs'); ?>
                </fieldset>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('COM_WATERWAYS_GUIDE_FIELDSET_PUBLISHING')); ?>
        <div class="row">
            <div class="col-lg-6">
                <fieldset class="adminform">
                    <?php echo $this->form->renderField('GuideID'); ?>
                    <?php echo $this->form->renderField('GuideNo'); ?>
                    <?php echo $this->form->renderField('GuideVer'); ?>
                </fieldset>
            </div>
            <div class="col-lg-6">
                <fieldset class="adminform">
                    <?php echo $this->form->renderField('GuidePostingDate'); ?>
                    <?php echo $this->form->renderField('GuideUpdate'); ?>
                    <?php echo $this->form->renderField('GuideEditorMemNo'); ?>
                </fieldset>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
