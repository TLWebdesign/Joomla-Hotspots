<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       02.07.13
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

require_once JPATH_COMPONENT_ADMINISTRATOR . '/models/hotspot.php';

/**
 * Class hotspotsModelForm
 *
 * @since  3.0
 */
class HotspotsModelForm extends HotspotsModelHotspot
{
    /**
     * Stock method to auto-populate the model state.
     *
     * @return  void
     */
    protected function populateState()
    {
        $input = JFactory::getApplication()->input;

        $return = $input->get('return', null, 'base64');
        $this->setState('returnPage', base64_decode($return));

        parent::populateState();
    }

    /**
     * Get the return URL.
     *
     * @return   string     The return URL.
     *
     * @return string
     */
    public function getReturnPage()
    {
        return base64_encode($this->getState('returnPage'));
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = parent::getForm($data, $loadData);

        // If the user has specified a category in the menu, then set this category as default
        if (HotspotsHelperSettings::get('default_category', 0)) {
            $form->setFieldAttribute('catid', 'default', HotspotsHelperSettings::get('default_category', 0));
        }

        return $form;
    }
}
