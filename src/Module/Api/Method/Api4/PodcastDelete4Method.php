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

namespace Ampache\Module\Api\Method\Api4;

use Ampache\Config\AmpConfig;
use Ampache\Repository\Model\Podcast;
use Ampache\Repository\Model\User;
use Ampache\Module\Api\Api4;

/**
 * Class PodcastDelete4Method
 */
final class PodcastDelete4Method
{
    public const ACTION = 'podcast_delete';

    /**
     * podcast_delete
     * MINIMUM_API_VERSION=420000
     *
     * Delete an existing podcast.
     *
     * @param array $input
     * @param User $user
     * filter = (string) UID of podcast to delete
     * @return boolean
     */
    public static function podcast_delete(array $input, User $user): bool
    {
        if (!AmpConfig::get('podcast')) {
            Api4::message('error', T_('Access Denied: podcast features are not enabled.'), '400', $input['api_format']);

            return false;
        }
        if (!Api4::check_access('interface', 75, $user->id, 'update_podcast', $input['api_format'])) {
            return false;
        }
        if (!Api4::check_parameter($input, array('filter'), self::ACTION)) {
            return false;
        }
        $object_id = (int) $input['filter'];
        $podcast   = new Podcast($object_id);
        if ($podcast->id > 0) {
            if ($podcast->remove()) {
                Api4::message('success', 'podcast ' . $object_id . ' deleted', null, $input['api_format']);
            } else {
                Api4::message('error', 'podcast ' . $object_id . ' was not deleted', '401', $input['api_format']);
            }
        } else {
            Api4::message('error', 'podcast ' . $object_id . ' was not found', '404', $input['api_format']);
        }

        return true;
    } // podcast_delete
}
