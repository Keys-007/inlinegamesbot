<?php
/**
 * Inline Games - Telegram Bot (@inlinegamesbot)
 *
 * (c) 2017 Jack'lul <jacklulcat@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Bot\Storage\Driver;

use Bot\Exception\StorageException;

/**
 * Class File
 *
 * Stores data in json formatted text files
 *
 * @package Bot\Storage\Driver
 */
class File
{
    /**
     * Initialize - define paths
     */
    public static function initializeStorage()
    {
        if (!defined('STORGE_GAME_PATH')) {
            define("STORGE_GAME_PATH", VAR_PATH . '/game');

            if (!is_dir(STORGE_GAME_PATH)) {
                mkdir(STORGE_GAME_PATH, 0755, true);
            }
        }

        return true;
    }

    /**
     * Dummy function
     *
     * @return bool
     */
    public static function createStructure()
    {
        return true;
    }

    /**
     * Read data from the file
     *
     * @param $id
     *
     * @return array|bool
     * @throws StorageException
     */
    public static function selectFromGame($id)
    {
        if (empty($id)) {
            throw new StorageException('Id is empty!');
        }

        if (file_exists(STORGE_GAME_PATH . '/' . $id . '.json')) {
            return json_decode(file_get_contents(STORGE_GAME_PATH . '/' . $id . '.json'), true);
        }

        return false;
    }

    /**
     * Place data to the file
     *
     * @param $id
     * @param $data
     *
     * @return bool
     * @throws StorageException
     */
    public static function insertToGame($id, $data)
    {
        if (empty($id)) {
            throw new StorageException('Id is empty!');
        }

        $data['updated_at'] = time();

        if (!isset($data['created_at'])) {
            $data['created_at'] = $data['updated_at'];
        }

        if (file_exists(STORGE_GAME_PATH . '/' . $id .  '.json')) {
            return file_put_contents(STORGE_GAME_PATH . '/' . $id . '.json', json_encode($data));
        }

        return false;
    }

    /**
     * Remove data file
     *
     * @param $id
     *
     * @return bool
     * @throws StorageException
     */
    public static function deleteFromGame($id)
    {
        if (empty($id)) {
            throw new StorageException('Id is empty!');
        }

        if (file_exists(STORGE_GAME_PATH . '/' . $id .  '.json')) {
            return unlink(STORGE_GAME_PATH . '/' . $id . '.json');
        }

        return false;
    }

    /**
     * Lock the file to prevent another process modifying it
     *
     * @param $id
     *
     * @return bool
     * @throws StorageException
     */
    public static function lockGame($id)
    {
        if (empty($id)) {
            throw new StorageException('Id is empty!');
        }

        if (!file_exists(STORGE_GAME_PATH . '/' . $id . '.json')) {
            $timestamp = time();
            file_put_contents(STORGE_GAME_PATH . '/' . $id . '.json', json_encode(['created_at' => $timestamp, 'updated_at' => $timestamp]));
        }

        if (flock(fopen(STORGE_GAME_PATH . '/' . $id .  '.json', "a+"), LOCK_EX)) {
            return true;
        }

        return false;
    }

    /**
     * Unlock the file after
     *
     * @param $id
     *
     * @return bool
     * @throws StorageException
     */
    public static function unlockGame($id)
    {
        if (empty($id)) {
            throw new StorageException('Id is empty!');
        }

        if (flock(fopen(STORGE_GAME_PATH . '/' . $id .  '.json', "a+"), LOCK_UN)) {
            return true;
        }

        return false;
    }

    /**
     * Select inactive data fields from database
     *
     * @param int $time
     *
     * @return array
     * @throws StorageException
     */
    public static function listFromGame($time = 0)
    {
        if (!is_numeric($time)) {
            throw new StorageException('Time must be a number!');
        }

        $ids = [];
        foreach (new \DirectoryIterator(STORGE_GAME_PATH) as $file) {
            if (!$file->isDir() && !$file->isDot()) {
                if (($file->getMTime() > strtotime('-' . abs($time) . ' seconds')) || ($file->getMTime() <= strtotime('-' . abs($time) . ' seconds'))) {
                    $ids[] = ['id' => trim(basename($file->getFilename(), '.json'))];
                }
            }
        }

        return $ids;
    }
}