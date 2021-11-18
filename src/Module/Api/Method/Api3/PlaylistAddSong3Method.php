<?php

/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 *  LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright 2001 - 2020 Ampache.org
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
use Ampache\Repository\Model\Playlist;

/**
 * Class PlaylistAddSong3Method
 */
final class PlaylistAddSong3Method
{
    public const ACTION = 'playlist_add_song';

    /**
     * playlist_add_song
     * This add a song to a playlist
     * @param array $input
     */
    public static function playlist_add_song(array $input)
    {
        ob_end_clean();
        $playlist = new Playlist($input['filter']);
        $song     = $input['song'];
        if (!$playlist->has_access()) {
            echo Xml3_Data::error('401', T_('Access denied to this playlist.'));
        } else {
            $playlist->add_songs(array($song));
            echo Xml3_Data::single_string('success');
        }
    } // playlist_add_song
}