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

namespace Ampache\Module\Api\Method\Api3;

use Ampache\Module\Api\Xml3_Data;
use Ampache\Module\Playback\Stream_Url;
use Ampache\Repository\Model\User;

/**
 * Class UrlToSong3Method
 */
final class UrlToSong3Method
{
    public const ACTION = 'url_to_song';

    /**
     * url_to_song
     *
     * This takes a url and returns the song object in question
     * @param array $input
     * @param User $user
     */
    public static function url_to_song(array $input, User $user)
    {
        // Don't scrub, the function needs her raw and juicy
        $url_data = Stream_URL::parse($input['url']);
        ob_end_clean();
        echo Xml3_Data::songs(array((int)$url_data['id']), $user);
    }
}
