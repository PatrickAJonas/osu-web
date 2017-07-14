<?php

/**
 *    Copyright 2015-2017 ppy Pty. Ltd.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Models;

class BeatmapPack extends Model
{
    const DEFAULT_TYPE = 'standard';
    private static $tagMappings = [
        'standard' => 'S',
        'theme' => 'T',
        'artist' => 'A',
        'chart' => 'R',
    ];

    protected $table = 'osu_beatmappacks';
    protected $primaryKey = 'pack_id';

    protected $dates = ['date'];
    public $timestamps = false;

    public function items()
    {
        return $this->hasMany(BeatmapPackItem::class, 'pack_id');
    }

    public function beatmapsets()
    {
        $thisTable = $this->getTable();
        $setsTable = (new Beatmapset)->getTable();
        $itemsTable = (new BeatmapPackItem)->getTable();
        return static::query()
            ->where("{$thisTable}.pack_id", '=', $this->pack_id)
            ->join($itemsTable, "{$itemsTable}.pack_id", '=', "{$thisTable}.pack_id")
            ->join($setsTable, "{$itemsTable}.beatmapset_id", '=', "{$setsTable}.beatmapset_id");
    }

    public function downloadUrls()
    {
        $array = [];
        foreach (explode(',', $this->url) as $url) {
            preg_match('@^(?:http[s]?://)?([^/]+)@i', $url, $hosts);
            $array[] = [
                'url' => $url,
                'host' => $hosts[1]
            ];
        }

        return $array;
    }

    public static function getPacks($type)
    {
        if (!in_array($type, array_keys(static::$tagMappings), true)) {
            return null;
        }

        static $packIdSortable = ['standard', 'chart'];

        $tag = static::$tagMappings[$type];
        $packs = BeatmapPack::where('tag', 'like', "{$tag}%");
        if (in_array($type, $packIdSortable)) {
            $packs = $packs->orderBy('pack_id', 'desc');
        } else {
            $packs = $packs->orderBy('name', 'asc');
        }

        return $packs->get();
    }
}
