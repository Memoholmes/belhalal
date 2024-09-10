<?php

/**
 * YesFileStorage Helpers.
 *
 * Common helper functions for YesFileStorage applications
 *
 *-------------------------------------------------------- */
/*
      * Get Path by key
      *
      * @return string path.
      *-------------------------------------------------------- */

if (! function_exists('getPathByKey')) {
    function getPathByKey($item, $dynamicItems = null)
    {
        $storagePaths = config('yes-file-storage.storage_paths');

        if (! $storagePaths || empty($storagePaths)) {
            throw new Exception('storage_paths not defined in config/yes-file-storage.php', 1);
        }

        $storagePaths = __nestedKeyValues($storagePaths, '/');

        $itemPath = array_get($storagePaths, $item, null);

        if (! $itemPath) {
            throw new Exception("key@$item not found in storage_paths", 1);
        }

        if ($itemPath) {
            if ($dynamicItems and is_array($dynamicItems)) {
                $itemPath = strtr($itemPath, $dynamicItems);
            }

            return $itemPath;
        }

        return null;
    }
}
