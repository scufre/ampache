<?php
/* vim:set softtabstop=4 shiftwidth=4 expandtab: */
/**
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright 2001 - 2022 Ampache.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

use Ampache\Config\AmpConfig;
use Ampache\Module\Util\Ui;

Ui::show_box_top(T_('Missing Artists'), 'info-box'); ?>
<table class="tabledata striped-rows">
    <thead>
        <tr class="th-top">
            <th class="cel_artist"><?php echo T_('Artist'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($wartists) {
            $web_path = AmpConfig::get('web_path');
            foreach ($wartists as $libitem) { ?>
        <tr id="wartist_<?php echo $libitem['mbid']; ?>">
            <td class="cel_artist">
                <a href="<?php echo $web_path; ?>/artists.php?action=show_missing&amp;mbid=<?php echo $libitem['mbid']; ?>"><?php echo $libitem['name']; ?></a>
            </td>
        </tr>
        <?php
            }
        } ?>
        <?php if (!$wartists || !count($wartists)) { ?>
        <tr>
            <td colspan="<?php echo $thcount; ?>"><span class="nodata"><?php echo T_('No missing artists found'); ?></span></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<?php Ui::show_box_bottom(); ?>
