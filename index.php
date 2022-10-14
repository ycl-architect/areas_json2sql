<?php
    $json_file = __DIR__ . '/file/world_areas.json';
    $sql_file = __DIR__ . '/file/world_areas.sql';
    try {
        $json_str = file_get_contents($json_file);
        $json_arr = json_decode($json_str, true);
        $sql_title = "SET NAMES utf8;\nSET time_zone = '+00:00';\nSET foreign_key_checks = 0;\nSET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';\n\nDROP TABLE IF EXISTS `world_areas`;\nCREATE TABLE `world_areas` (`id` int(11) NOT NULL AUTO_INCREMENT,`name` varchar(20) NOT NULL,`code` varchar(20) NOT NULL,`parent_code` varchar(20) NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;\n\nINSERT INTO `world_areas` (`name`, `code`, `parent_code`) VALUES";
        writeFile($sql_file,$sql_title);
        $country_num = 0;
        foreach ($json_arr as $country) {
            $country_num += 1;
            $name = $country['name'];
            $code = $country['code'];
            $parent_code = '0';
            if ($country_num == count($json_arr) && ! $country['clist']) {
                writeFile($sql_file, "('$name','$code','$parent_code');\n");
            } else {
                writeFile($sql_file, "('$name','$code','$parent_code'),\n");
            }
            if ( ! $country['clist']) {
                continue;
            }
            $province_num = 0;
            foreach ($country['clist'] as $key1 => $province) {
                $province['code'] = $province['code'] ?: $country['code'] . $key1;
                $province_num += 1;
                $name = $province['name'];
                $code = $province['code'];
                $parent_code = $country['code'];
                if ($country_num == count($json_arr) && $province_num == count($country['clist']) && ! $province['pchilds']) {
                    writeFile($sql_file, "('$name','$code','$parent_code');\n");
                } else {
                    writeFile($sql_file, "('$name','$code','$parent_code'),\n");
                }
                if ( ! $province['pchilds']) {
                    continue;
                }
                $city_num = 0;
                foreach ($province['pchilds'] as $key2 => $city) {
                    $city_num += 1;
                    $name = $city['name'];
                    $code = $city['code'];
                    $parent_code = $province['code'];
                    $city['code'] = $city['code'] ?: $province['code'] . $key2;
                    if ($country_num == count($json_arr) && $province_num == count($country['clist']) && $city_num == count($province['pchilds']) && ! $city['cchilds']) {
                        writeFile($sql_file, "('$name','$code','$parent_code');\n");
                    } else {
                        writeFile($sql_file, "('$name','$code','$parent_code'),\n");
                    }
                    if ( ! $city['cchilds']) {
                        continue;
                    }
                    $region_num = 0;
                    foreach ($city['cchilds'] as $key3 => $region) {
                        $region_num += 1;
                        $name = $region['name'];
                        $code = $region['code'];
                        $parent_code = $city['code'];
                        $region['code'] = $region['code'] ?: $city['code'] . $key3;
                        if ($country_num == count($json_arr) && $province_num == count($country['clist']) && $city_num == count($province['pchilds']) && $region_num == count($city['cchilds'])) {
                            writeFile($sql_file, "('$name','$code','$parent_code');\n");
                        } else {
                            writeFile($sql_file, "('$name','$code','$parent_code'),\n");
                        }
                    }
                }
            }
        }
        echo "success";
    } catch (Exception $e) {
        echo "oh shit:" . $e->getMessage();
    }

    function writeFile($sql_file,$sql_line){
        $file = fopen($sql_file,"a");
        fwrite($file,$sql_line);
        fclose($file);
    }

