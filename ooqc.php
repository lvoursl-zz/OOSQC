<?php

    $db_file = fopen("exchange.sql", "r");
    $output_script_file = fopen("db_api.php", "w");

    if (($db_file) && (is_writable("db_api.php"))) {
        fwrite($output_script_file, "<?php\n");

        while (($current_string = fgets($db_file, 4096)) !== false) {
            if (strpos($current_string, "CREATE TABLE") !== false) {
                
                if (strpos($current_string, "`") !== false) {
                    $left_symbol_position = strpos($current_string, "`") + 1;
                    $right_symbol_position = strrpos($current_string, "`") -
                                              strpos($current_string, "`") - 1;
                    $table_name = substr($current_string,
                                         $left_symbol_position,
                                         $right_symbol_position);
                    echo $table_name . '<br>';
                    // we found table with name == $table_name, lets go through DB fields
                    while (($table_string = fgets($db_file, 4096)) !== false) {
                        if (strpos($table_string, ") ENGINE=") !== false) {
                            echo '<br>';
                            break;
                        }
                        echo $table_string . '<br>';
                    }

                }






            }

        }

        if (!feof($db_file)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($db_file);

    }

?>
