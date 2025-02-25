<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 *  LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright Ampache.org, 2001-2023
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

declare(strict_types=0);

namespace Ampache\Module\Api\Method;

use Ampache\Config\ConfigContainerInterface;
use Ampache\Module\System\Update;
use Ampache\Repository\Model\Preference;
use Ampache\Repository\Model\User;
use Ampache\Module\Api\Api;
use Ampache\Module\System\AutoUpdate;
use Ampache\Module\System\Session;

/**
 * Class SystemUpdateMethod
 * @package Lib\ApiMethods
 */
final class SystemUpdateMethod
{
    public const ACTION = 'system_update';

    /**
     * system_update
     * MINIMUM_API_VERSION=400001
     *
     * Check Ampache for updates and run the update if there is one.
     *
     * @param array $input
     * @param User $user
     * @return boolean
     */
    public static function system_update(array $input, User $user): bool
    {
        $updated = false;
        if (!Api::check_access('interface', 100, $user->id, self::ACTION, $input['api_format'])) {
            return false;
        }
        if (AutoUpdate::is_update_available(true)) {
            // run the update
            AutoUpdate::update_files(true);
            AutoUpdate::update_dependencies(static::getConfigContainer(), true);
            Preference::translate_db();
            // check that the update completed or failed failed.
            if (AutoUpdate::is_update_available(true)) {
                Api::error(T_('Bad Request'), '4710', self::ACTION, 'system', $input['api_format']);
                Session::extend($input['auth']);

                return false;
            }
            $updated = true;
        }
        // update the database
        if (Update::need_update()) {
            if (Update::run_update()) {
                $updated = true;
            }
        }
        if ($updated) {
            // there was an update and it was successful
            Api::message('update successful', $input['api_format']);
            Session::extend($input['auth']);

            return true;
        }
        //no update available but you are an admin so tell them
        Api::message('No update available', $input['api_format']);

        return true;
    }

    private static function getConfigContainer(): ConfigContainerInterface
    {
        global $dic;

        return $dic->get(ConfigContainerInterface::class);
    }
}
