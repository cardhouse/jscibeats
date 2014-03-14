<?php
class ModelModuleKulerSitetools extends Model
{
    /* Database */
    public function getTables() {
        $table_data = array();
        $row_name = 'Tables_in_' . DB_DATABASE;
        $exclude_tables = array(DB_PREFIX . 'user', DB_PREFIX . 'user_group');

        $query = $this->db->query("SHOW TABLES FROM `" . DB_DATABASE . "`");

        foreach ($query->rows as $result)
        {
            $table_name = str_replace(DB_PREFIX, '', $result[$row_name]);

            if ((utf8_substr($result[$row_name], 0, strlen(DB_PREFIX)) == DB_PREFIX && !in_array($result[$row_name], $exclude_tables)) || $result[$row_name] == 'resources')
            {
                $table_data[] = $result['Tables_in_' . DB_DATABASE];
            }
        }

        return $table_data;
    }

    public function exportTables($tables) {
        $output = '';

        foreach ($tables as $table)
        {
            $output .= 'TRUNCATE TABLE `' . $table . '`;' . "\n\n";

            $query = $this->db->query("SELECT * FROM `" . $table . "`");

            foreach ($query->rows as $result) {
                $fields = '';

                foreach (array_keys($result) as $value) {
                    $fields .= '`' . $value . '`, ';
                }

                $values = '';

                foreach (array_values($result) as $value) {
                    $value = str_replace(array("\x00", "\x0a", "\x0d", "\x1a"), array('\0', '\n', '\r', '\Z'), $value);
                    $value = str_replace(array("\n", "\r", "\t"), array('\n', '\r', '\t'), $value);
                    $value = str_replace('\\', '\\\\',	$value);
                    $value = str_replace('\'', '\\\'',	$value);
                    $value = str_replace('\\\n', '\n',	$value);
                    $value = str_replace('\\\r', '\r',	$value);
                    $value = str_replace('\\\t', '\t',	$value);

                    $values .= '\'' . $value . '\', ';
                }

                $output .= 'INSERT INTO `' . $table . '` (' . preg_replace('/, $/', '', $fields) . ') VALUES (' . preg_replace('/, $/', '', $values) . ');' . "\n";
            }

            $output .= "\n\n";
        }

        return $output;
    }

    /* Zip */
    public function compress(array $sources, $destination)
    {
        $zip = new ZipArchive();
        $res = $zip->open($destination, ZipArchive::CREATE);

        if ($res !== true)
        {
            return false;
        }

        $destination = rtrim($destination, '/');

        foreach ($sources as $src => $path)
        {
            $src = rtrim($src, '/');

            if (is_file($src))
            {
                $zip->addFile($src, $path);
            }
            else if (is_dir($src))
            {
                $this->processRecursive($src, $zip, $path);
            }
        }

        $zip->close();

        return true;
    }

    private function processRecursive($src, &$zip, $path)
    {
        $dir = opendir($src);
        while (false !== ($file = readdir($dir)))
        {
            if (($file != '.') && ($file != '..'))
            {
                if (is_dir($src . '/' . $file))
                {
                    $this->processRecursive($src . '/' . $file, $zip, $path . '/' . $file);
                }
                else
                {
                    $zip->addFile($src . '/' . $file, $path . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}