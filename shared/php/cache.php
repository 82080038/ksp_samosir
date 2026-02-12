<?php

class SimpleCache {

    private static $cacheDir = __DIR__ . '/../../cache/';

    public static function get($key) {

        if (function_exists('apcu_fetch')) {

            return apcu_fetch($key);

        } else {

            $file = self::$cacheDir . md5($key) . '.cache';

            if (file_exists($file)) {

                $data = unserialize(file_get_contents($file));

                if ($data['ttl'] > time()) {

                    return $data['value'];

                } else {

                    unlink($file);

                }

            }

            return null;

        }

    }

    public static function set($key, $value, $ttl = 300) {

        if (function_exists('apcu_store')) {

            apcu_store($key, $value, $ttl);

        } else {

            if (!is_dir(self::$cacheDir)) {

                mkdir(self::$cacheDir, 0755, true);

            }

            $file = self::$cacheDir . md5($key) . '.cache';

            file_put_contents($file, serialize(['value' => $value, 'ttl' => time() + $ttl]));

        }

    }

}
