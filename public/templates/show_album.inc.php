<?php
/* vim:set softtabstop=4 shiftwidth=4 expandtab: */
/**
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
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

use Ampache\Config\AmpConfig;
use Ampache\Repository\Model\Album;
use Ampache\Repository\Model\Art;
use Ampache\Repository\Model\Catalog;
use Ampache\Repository\Model\Rating;
use Ampache\Repository\Model\Share;
use Ampache\Repository\Model\User;
use Ampache\Repository\Model\Userflag;
use Ampache\Module\Api\RefreshReordered\RefreshAlbumSongsAction;
use Ampache\Module\Authorization\Access;
use Ampache\Module\Api\Ajax;
use Ampache\Module\System\Core;
use Ampache\Module\Util\AmpacheRss;
use Ampache\Module\Playback\Stream_Playlist;
use Ampache\Repository\Model\Browse;
use Ampache\Module\Util\Ui;
use Ampache\Module\Util\ZipHandlerInterface;

/** @var Album $album */
/** @var bool $isAlbumEditable */

global $dic;
$zipHandler = $dic->get(ZipHandlerInterface::class);
$batch_dl   = Access::check_function('batch_download');
$zip_album  = $batch_dl && $zipHandler->isZipable('album');
// Title for this album
$web_path = AmpConfig::get('web_path');
$simple   = $album->get_fullname(true);
$f_name   = $album->get_fullname(false, true);
$title    = ($album->album_artist !== null)
    ? scrub_out($f_name) . '&nbsp;-&nbsp;' . $album->get_f_artist_link()
    : scrub_out($f_name);

$show_direct_play  = AmpConfig::get('directplay');
$show_playlist_add = Access::check('interface', 25);
$directplay_limit  = AmpConfig::get('direct_play_limit');
$hide_array        = (AmpConfig::get('hide_single_artist') && $album->get_artist_count() == 1)
    ? array('cel_artist', 'cel_album', 'cel_year', 'cel_drag')
    : array('cel_album', 'cel_year', 'cel_drag');

if ($directplay_limit > 0) {
    $show_playlist_add = ($album->song_count <= $directplay_limit);
    if ($show_direct_play) {
        $show_direct_play = $show_playlist_add;
    }
} ?>
<?php Ui::show_box_top($title, 'info-box'); ?>

<div class="item_right_info">
    <div class="external_links">
        <a href="http://www.google.com/search?q=%22<?php echo rawurlencode($album->get_artist_fullname()); ?>%22+%22<?php echo rawurlencode($simple); ?>%22" target="_blank"><?php echo Ui::get_icon('google', T_('Search on Google ...')); ?></a>
        <a href="https://www.duckduckgo.com/?q=%22<?php echo rawurlencode($album->f_artist_name); ?>%22+%22<?php echo rawurlencode($simple); ?>%22" target="_blank"><?php echo Ui::get_icon('duckduckgo', T_('Search on DuckDuckGo ...')); ?></a>
        <a href="http://en.wikipedia.org/wiki/Special:Search?search=%22<?php echo rawurlencode($simple); ?>%22&go=Go" target="_blank"><?php echo Ui::get_icon('wikipedia', T_('Search on Wikipedia ...')); ?></a>
        <a href="http://www.last.fm/search?q=%22<?php echo rawurlencode($album->f_artist_name); ?>%22+%22<?php echo rawurlencode($simple); ?>%22&type=album" target="_blank"><?php echo Ui::get_icon('lastfm', T_('Search on Last.fm ...')); ?></a>
    <?php if ($album->mbid) { ?>
        <a href="https://musicbrainz.org/release/<?php echo $album->mbid; ?>" target="_blank"><?php echo Ui::get_icon('musicbrainz', T_('Search on Musicbrainz ...')); ?></a>
    <?php } else { ?>
        <a href="https://musicbrainz.org/search?query=%22<?php echo rawurlencode($simple); ?>%22&type=release" target="_blank"><?php echo Ui::get_icon('musicbrainz', T_('Search on Musicbrainz ...')); ?></a>
    <?php } ?>
    </div>
    <?php
        $name  = '[' . scrub_out($album->f_artist_name) . '] ' . scrub_out($f_name);
$thumb         = Ui::is_grid_view('album') ? 32 : 11;
Art::display('album', $album->id, $name, $thumb); ?>
</div>
<?php if (User::is_registered()) {
    if (AmpConfig::get('ratings')) { ?>
        <span id="rating_<?php echo $album->id; ?>_album">
            <?php echo Rating::show($album->id, 'album', true); ?>
        </span>
        <span id="userflag_<?php echo $album->id; ?>_album">
            <?php echo Userflag::show($album->id, 'album'); ?>
        </span>
        <?php }
    } ?>
<?php
if (AmpConfig::get('show_played_times')) { ?>
<br />
<div style="display:inline;">
    <?php echo T_('Played') . ' ' .
    /* HINT: Number of times an object has been played */
    sprintf(nT_('%d time', '%d times', $album->total_count), $album->total_count); ?>
</div>
<?php } ?>
<?php
$owner_id = $album->get_user_owner();
if (AmpConfig::get('sociable') && $owner_id > 0) {
    $owner = new User($owner_id); ?>
<div class="item_uploaded_by">
    <?php echo T_('Uploaded by'); ?> <?php echo $owner->get_f_link(); ?>
</div>
<?php
} ?>

<div id="information_actions">
    <h3><?php echo T_('Actions'); ?>:</h3>
    <ul>
        <?php if ($show_direct_play) {
            $play       = T_('Play');
            $playnext   = T_('Play next');
            $playlast   = T_('Play last'); ?>
        <li>
            <?php echo Ajax::button_with_text('?page=stream&action=directplay&object_type=album&object_id=' . $album->id, 'play', $play, 'directplay_full_' . $album->id); ?>
        </li>
            <?php if (Stream_Playlist::check_autoplay_next()) { ?>
        <li>
            <?php echo Ajax::button_with_text('?page=stream&action=directplay&object_type=album&object_id=' . $album->id . '&playnext=true', 'play_next', $playnext, 'nextplay_album_' . $album->id); ?>
        </li>
            <?php }
            if (Stream_Playlist::check_autoplay_append()) { ?>
        <li>
            <?php echo Ajax::button_with_text('?page=stream&action=directplay&object_type=album&object_id=' . $album->id . '&append=true', 'play_add', $playlast, 'addplay_album_' . $album->id); ?>
        </li>
            <?php } ?>
        <?php
        } ?>

        <?php if ($show_playlist_add) {
            $addtotemp  = T_('Add to Temporary Playlist');
            $randtotemp = T_('Random to Temporary Playlist');
            $addtoexist = T_('Add to playlist'); ?>
        <li>
            <?php echo Ajax::button_with_text('?action=basket&type=album&id=' . $album->id, 'add', $addtotemp, 'play_full_' . $album->id); ?>
        </li>
        <li>
            <?php echo Ajax::button_with_text('?action=basket&type=album_random&id=' . $album->id, 'random', $randtotemp, 'play_random_' . $album->id); ?>
        </li>
        <li>
            <a id="<?php echo 'add_playlist_' . $album->id ?>" onclick="showPlaylistDialog(event, 'album', '<?php echo $album->id ?>')">
                <?php echo Ui::get_icon('playlist_add', $addtoexist);
            echo $addtoexist ?>
            </a>
        </li>
        <?php
        }
if (AmpConfig::get('use_rss')) { ?>
        <li>
            <?php echo AmpacheRss::get_display('podcast', Core::get_global('user')->id, T_('RSS Feed'), array('object_type' => 'album', 'object_id' => $album->id)); ?>
        </li>
        <?php }
if (!AmpConfig::get('use_auth') || Access::check('interface', 25)) {
    if (AmpConfig::get('sociable')) {
        $postshout = T_('Post Shout'); ?>
            <li>
                <a href="<?php echo $web_path; ?>/shout.php?action=show_add_shout&type=album&id=<?php echo $album->id; ?>">
                    <?php echo Ui::get_icon('comment', $postshout);
        echo $postshout; ?>
                </a>
            </li>
            <?php
    }
}
if (Access::check('interface', 25)) {
    if (AmpConfig::get('share')) { ?>
            <li>
                <?php echo Share::display_ui('album', $album->id); ?>
            </li>
            <?php }
    }
if (($owner_id > 0 && !empty(Core::get_global('user')) && $owner_id == (int) Core::get_global('user')->id) || Access::check('interface', 50)) {
    $saveorder  = T_('Save Track Order');
    if (AmpConfig::get('statistical_graphs') && is_dir(__DIR__ . '/../../vendor/szymach/c-pchart/src/Chart/')) { ?>
            <li>
                <a href="<?php echo $web_path; ?>/stats.php?action=graph&object_type=album&object_id=<?php echo $album->id; ?>">
                    <?php echo Ui::get_icon('statistics', T_('Graphs'));
        echo T_('Graphs'); ?>
                </a>
            </li>
        <?php } ?>
        <li>
            <a onclick="submitNewItemsOrder('<?php echo $album->id; ?>', 'reorder_songs_table_<?php echo $album->id; ?>', 'song_',
                                            '<?php echo $web_path; ?>/albums.php?action=set_track_numbers', '<?php echo RefreshAlbumSongsAction::REQUEST_KEY; ?>')">
                <?php echo Ui::get_icon('save', $saveorder);
    echo $saveorder; ?>
            </a>
        </li>
        <li>
            <a href="javascript:NavigateTo('<?php echo $web_path; ?>/albums.php?action=update_from_tags&amp;album_id=<?php echo $album->id; ?>');" onclick="return confirm('<?php echo T_('Do you really want to update from tags?'); ?>');">
                <?php echo Ui::get_icon('file_refresh', T_('Update from tags'));
    echo T_('Update from tags'); ?>
            </a>
        </li>
        <?php
}
if ($isAlbumEditable) {
    $t_upload = T_('Upload');
    if (AmpConfig::get('allow_upload') && $album->album_artist > 0) { ?>
                <li>
                    <a href="<?php echo $web_path; ?>/upload.php?artist=<?php echo $album->album_artist; ?>&album=<?php echo $album->id ?>">
                        <?php echo Ui::get_icon('upload', $t_upload);
        echo $t_upload; ?>
                    </a>
                </li>
    <?php } ?>
        <li>
            <a id="<?php echo 'edit_album_' . $album->id ?>" onclick="showEditDialog('album_row', '<?php echo $album->id ?>', '<?php echo 'edit_album_' . $album->id ?>', '<?php echo addslashes(T_('Album Edit')) ?>', '')">
                <?php echo Ui::get_icon('edit', T_('Edit'));
    echo T_('Edit Album'); ?>
            </a>
        </li>
        <?php
}
if ($zip_album) {
    $download   = T_('Download'); ?>
        <li>
            <a class="nohtml" href="<?php echo $web_path; ?>/batch.php?action=album&id=<?php echo $album->id; ?>">
                <?php echo Ui::get_icon('batch_download', $download);
    echo $download; ?>
            </a>
        </li>
<?php
}
if (Catalog::can_remove($album)) {
    $delete = T_('Delete'); ?>
        <li>
            <a id="<?php echo 'delete_album_' . $album->id ?>" href="<?php echo $web_path; ?>/albums.php?action=delete&album_id=<?php echo $album->id; ?>">
                <?php echo Ui::get_icon('delete', $delete);
    echo $delete; ?>
            </a>
        </li>
        <?php
} ?>
    </ul>
</div>
<?php Ui::show_box_bottom(); ?>
<div id="additional_information">
&nbsp;
</div>
<div id='reordered_list_<?php echo $album->id; ?>'>
<?php $browse = new Browse();
$browse->set_type('song');
$browse->set_simple_browse(true);
$browse->set_filter('album', $album->id);
$browse->set_sort('track', 'ASC');
$browse->get_objects();
$browse->show_objects(null, array('hide' => $hide_array));
$browse->store(); ?>
</div>
