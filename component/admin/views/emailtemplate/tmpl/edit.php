<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

//JHtml::_('behavior.formvalidator');

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

$params = $this->form->getFieldsets('params');

?>

<script type="text/javascript">
    Joomla.submitbutton = function(task)
    {
        if (task == 'emailtemplate.cancel' || document.formvalidator.isValid(document.getElementById('mpoll-form'))) {
            Joomla.submitform(task, document.getElementById('mpoll-form'));
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_mpoll&layout=edit&tmpl_id='.(int) $this->item->tmpl_id); ?>" method="post" name="adminForm" id="mpoll-form" class="form-validate">

    <div class="row row-fluid title-alias form-vertical mb-3">
        <div class="col-12 col-md-12">
            <?php echo $this->form->renderField('tmpl_name'); ?>
        </div>
    </div>

    <div class="form-horizontal main-card">

        <?php
        echo HTMLHelper::_( 'uitab.startTabSet', 'myTab', array( 'active' => 'details', 'recall' => true, 'breakpoint' => 768 ) );
        echo HTMLHelper::_('uitab.addTab', 'myTab', 'setup', Text::_('COM_MPOLL_MPOLL_SETUP'));
        ?>

        <div class="row-fluid row">
            <div class="col-md-8">
                <fieldset class="adminform">
                    <div class="form-horizontal form-grid">
                        <?php echo $this->form->renderFieldset('emaildetails'); ?>
                    </div>
                    <div class="form-horizontal form-grid">
                        <?php echo $this->form->renderFieldset('emailcontent'); ?>
                    </div>
                </fieldset>
            </div>
            <div class="col-md-4">
                <fieldset class="adminform">
                    <div class="form-horizontal form-grid">
                        <?php echo $this->form->renderFieldset('setup'); ?>
                    </div>
                </fieldset>
                <h3>Merge Tags</h3>
                <table class="table table-striped">
                    <thead><th>Tag</th><th>Description/Field</th></thead>
                    <tbody>
                        <tr>
                            <td>{name}</td>
                            <td>Associated users full name</td>
                        </tr>
                        <tr>
                            <td>{email}</td>
                            <td>Associated users email</td>
                        </tr>
                        <tr>
                            <td>{username}</td>
                            <td>Associated users username</td>
                        </tr>
                        <tr>
                            <td>{resid}</td>
                            <td>Results id</td>
                        </tr>
                        <tr>
                            <td>{resurl}</td>
                            <td>Results URL</td>
                        </tr>
                        <tr>
                            <td>{payurl}</td>
                            <td>Payment URL</td>
                        </tr>
                        <?php
                        if ($this->item->tmpl_poll) {
                            foreach ($this->questions as $q) {
                                echo '<tr><td>{i'.$q->value.'}</td>';
                                echo '<td>'.$q->text.'</td></tr>';
                            }
                        } else {
                            echo '<tr><td colspan="2"><em>Save to see list of question merge tags</em></td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php
        echo HTMLHelper::_('uitab.endTab');
        echo HTMLHelper::_('uitab.endTabSet');
        ?>
    </div>
    <div>
        <input type="hidden" name="task" value="emailtemplate.edit" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>


