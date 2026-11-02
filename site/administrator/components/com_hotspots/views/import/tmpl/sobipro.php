<?php
/**
 * @package    Hotspots
 * @author     DanielDimitrov <daniel@compojoom.com>
 * @date       12.10.14
 *
 * @copyright  Copyright (C) 2008 - 2013 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.keepalive');

if (JVERSION > 3)
{
	JHtml::_('formbehavior.chosen', 'select');
}
if (version_compare(JVERSION, '4.0', 'lt')) {
	CompojoomHtmlBehavior::bootstrap(false, true);
}

echo CompojoomHtmlCtemplate::getHead(HotspotsHelperMenu::getMenu(), 'cpanel', '', '');
?>
	<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
		<div class="box-info full">
			<h2>SobiPro Import</h2>

			<table width="100%" class="table">
				<tr>
					<td width="200px"><?php echo JText::_('COM_HOTSPOTS_SOBI_DETAILPAGE_LINK'); ?>:</td>
					<td width="100">
						<select id="sobi2_link" name="sobi2_link">
							<option value="0"><?php echo JText::_('JNO'); ?></option>
							<option value="1"><?php echo JText::_('JYES'); ?></option>
						</select>
					</td>
					<td><?php echo JText::_('COM_HOTSPOTS_SOBI_DETAILPAGE_LINK_DESC'); ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_HOTSPOTS_DELETE_OLD_SOBI_ENTRIES'); ?>:</td>
					<td>
						<select id="delete_old" name="delete_old">
							<option value="0"><?php echo JText::_('JNO'); ?></option>
							<option value="1"><?php echo JText::_('JYES'); ?></option>
						</select>
					</td>
					<td><?php echo JText::_('COM_HOTSPOTS_DELETE_OLD_SOBI_ENTRIES_DESC'); ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_HOTSPOTS_ONLY_PUBLISHED_SOBI'); ?>:</td>
					<td>
						<select id="sobi2published" name="sobi2published">
							<option value="0"><?php echo JText::_('JNO'); ?></option>
							<option value="1"><?php echo JText::_('JYES'); ?></option>
						</select>
					</td>
					<td><?php echo JText::_('COM_HOTSPOTS_ONLY_PUBLISHED_SOBI_DESC'); ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_HOTSPOTS_SOBI_GEOMAP_FIELD'); ?>:</td>
					<td>
						<select id="geomap" name="geomap">
							<option value="0"><?php echo JText::_('JNO'); ?></option>
							<option value="1"><?php echo JText::_('JYES'); ?></option>
						</select>
					</td>
					<td><?php echo JText::_('COM_HOTSPOTS_SOBI_GEOMAP_FIELD_DESC'); ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_HOTSPOTS_NAME_FIELD_ID'); ?>:</td>
					<td><input class="text_area" type="text" name="name_fieldid" id="name_fieldid" size="5" maxlength="5" value="1"/></td>
					<td><?php echo JText::_('COM_HOTSPOTS_NAME_FIELD_ID_DESC'); ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_HOTSPOTS_LAT_FIELD_ID'); ?>:</td>
					<td><input class="text_area" type="text" name="lat_fieldid" id="lat_fieldid" size="5" maxlength="5" value="1"/></td>
					<td><?php echo JText::_('COM_HOTSPOTS_LAT_FIELD_ID_DESC'); ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_HOTSPOTS_LNG_FIELD_ID'); ?>:</td>
					<td><input class="text_area" type="text" name="lng_fieldid" id="lng_fieldid" size="5" maxlength="5" value="1"/></td>
					<td><?php echo JText::_('COM_HOTSPOTS_LNG_FIELD_ID_DESC'); ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_HOTSPOTS_STREET_FIELD_ID'); ?>:</td>
					<td><input class="text_area" type="text" name="street_fieldid" id="street_fieldid" size="5" maxlength="5" value="35"/></td>
					<td><?php echo JText::_('COM_HOTSPOTS_STREET_FIELD_ID_DESC'); ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_HOTSPOTS_ZIP_CODE_FIELD_ID'); ?>:</td>
					<td><input class="text_area" type="text" name="zip_fieldid" id="zip_fieldid" size="5" maxlength="5" value="2"/></td>
					<td><?php echo JText::_('COM_HOTSPOTS_ZIP_CODE_FIELD_ID_DESC'); ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_HOTSPOTS_TOWN_FIELD_ID'); ?>:</td>
					<td><input class="text_area" type="text" name="town_fieldid" id="town_fieldid" size="5" maxlength="5" value="3"/></td>
					<td><?php echo JText::_('COM_HOTSPOTS_TOWN_FIELD_ID_DESC'); ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_HOTSPOTS_COUNTRY_FIELD_ID'); ?>:</td>
					<td><input class="text_area" type="text" name="country_fieldid" id="country_fieldid" size="5" maxlength="5" value="13"/></td>
					<td><?php echo JText::_('COM_HOTSPOTS_COUNTRY_FIELD_ID_DESC'); ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_HOTSPOTS_MAIL_FIELD_ID'); ?>:</td>
					<td><input class="text_area" type="text" name="mail_fieldid" id="mail_fieldid" size="5" maxlength="5" value="7"/></td>
					<td><?php echo JText::_('COM_HOTSPOTS_MAIL_FIELD_ID_DESC'); ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_HOTSPOTS_WEBADRESS_FIELD_ID'); ?>:</td>
					<td><input class="text_area" type="text" name="web_fieldid" id="web_fieldid" size="5" maxlength="5" value="11"/></td>
					<td><?php echo JText::_('COM_HOTSPOTS_WEBADRESS_FIELD_ID_DESC'); ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_HOTSPOTS_PHONE_FIELD_ID'); ?>:</td>
					<td><input class="text_area" type="text" name="phone_fieldid" id="phone_fieldid" size="5" maxlength="5" value="5"/></td>
					<td><?php echo JText::_('COM_HOTSPOTS_PHONE_FIELD_ID_DESC'); ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_HOTSPOTS_DESCRIPTION_FIELD_ID'); ?>:</td>
					<td><input class="text_area" type="text" name="description_fieldid" id="description_fieldid" size="5" maxlength="5" value="8"/>
					</td>
					<td><?php echo JText::_('COM_HOTSPOTS_DESCRIPTION_FIELD_ID_DESC'); ?></td>
				</tr>
				<tr>
					<td><font color="#FF0000"><?php echo JText::_('COM_HOTSPOTS_EXPERT_DB_MAX_EXECUTION_TIME'); ?>:</font></td>
					<td><input class="text_area" type="text" name="db_max_execution_time" id="db_max_execution_time" size="5" maxlength="5"
					           value="30"/></td>
					<td><?php echo JText::_('COM_HOTSPOTS_EXPERT_DB_MAX_EXECUTION_TIME_DESC'); ?></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td align="right"><input type="submit" class="button btn btn-primary" id="button"
					                         value="<?php echo JText::_('COM_HOTSPOTS_IMPORT'); ?>"/></td>
				</tr>
			</table>
		</div>
		<input type="hidden" name="option" value="com_hotspots"/>
		<input type="hidden" name="task" value="import.import"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>

<?php
// Show Footer
echo CompojoomHtmlCTemplate::getFooter(HotspotsHelperBasic::getFooterText());
