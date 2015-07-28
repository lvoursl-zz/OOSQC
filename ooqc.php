<?php

    function cutWordInBrackersInString($string)
    {
        $left_symbol_position = strpos($string, "`") + 1;
        $right_symbol_position = strrpos($string, "`") -
                                  strpos($string, "`") - 1;
        $cropped_string = substr($string,
                                 $left_symbol_position,
                                 $right_symbol_position);
        return $cropped_string;
    }

    function cutTypeFromString($string)
    {
        if (strpos($string, "int") !== false) {
            return "int";
        } elseif (strpos($string, "text") !== false) {
            return "text";
        } elseif (strpos($string, "varchar") !== false) {
            return "varchar";
        } elseif (strpos($string, "date") !== false) {
            return "date";
        } else {
            return 0;
        }
    }

    $db_file = fopen("exchange.sql", "r");
    $output_script_file = fopen("db_api.php", "w");

    /* in the future we will take params from command line \ other file */
    $db_login = "";
    $db_password = "";
    $db_name = "";
    $db_type = "";
    $db_host = "";
    /* end params */

    if (($db_file) && (is_writable("db_api.php"))) {
        fwrite($output_script_file, "<?php\n\n");
        fwrite($output_script_file, "\tdefine('DB_LOGIN', $db_login);\n");
        fwrite($output_script_file, "\tdefine('DB_PASSWORD', $db_password);\n\n");

        /* lets write about connection */
        fwrite($output_script_file, "\t" . '$' . "conn = new PDO('mysql:host=$db_host;"
                                    . "dbname=$db_name', DB_USERNAME, DB_PASSWORD);\n");

        fwrite($output_script_file, "\t" . '$' . "conn->setAttribute(PDO::ATTR_ERRMODE, "
                                    . " PDO::ERRMODE_EXCEPTION);\n");

        /* reading SQL file line by line */
        while (($current_string = fgets($db_file, 4096)) !== false) {
            if (strpos($current_string, "CREATE TABLE") !== false) {
                //  finding a symbol from which starts table name
                if (strpos($current_string, "`") !== false) {

                    $table_name = cutWordInBrackersInString($current_string);
                    echo $table_name . '<br>';

                    $table_fields_data = array();
                    // we found table with name == $table_name, lets go through DB fields
                    while (($table_field = fgets($db_file, 4096)) !== false) {
                        if (strpos($table_field, ") ENGINE=") !== false) {
                            // thats for table name for function header
                            $table_name = ucfirst($table_name);

                            // write queries like SELECT * FROM
                            fwrite($output_script_file,
                                    "\n\tfunction getAll$table_name()\n\t{\n ");

                            fwrite($output_script_file, "\t\tglobal " . '$' . "conn;\n");
                            fwrite($output_script_file, "\t\t" . '$' . "query = "
                                                        . '$' . "conn->prepare("
                                                        . "'SELECT * FROM "
                                                        . lcfirst($table_name)
                                                        . "');\n");
                            fwrite($output_script_file, "\t\t" . '$' . "query->"
                                                        . "execute();\n");
                            fwrite($output_script_file, "\t\t" . '$' . "result = "
                                                        . '$' . "query->"
                                                        . "fetchAll();\n");
                            fwrite($output_script_file, "\t\treturn "
                                                        . '$' . "result;\n\t}\n");


                            // write queries for ALL fields combinations
                            $step = 1;
                            $fields_num = count($table_fields_data);

                            while ($step != $fields_num ) {
                                for ($i = 0; $i < $fields_num - $step; $i++) {
                                    $function_name = array();

                                    for ($s = $i; $s < $i + $step; $s++) {
                                        $function_name[] = $table_fields_data[$s];
                                    }

                                    for ($j = $i + $step; $j < $fields_num; $j++) {
                                        $function_name[] = $table_fields_data[$j];

                                        /* loop for writing function name and params*/
                                        fwrite($output_script_file,
                                                "\n\tfunction get"
                                                . $table_name
                                                . "By");

                                        $fn_len = count($function_name);
                                        for ($t = 0; $t < $fn_len; $t++) {
                                            //print_r($function_name[$t]['name']);
                                            fwrite($output_script_file,
                                                    ucfirst($function_name[$t]['name']));

                                        }
                                        fwrite($output_script_file,
                                                "()\n\t{ ");

                                        //echo '<br>';
                                        array_pop($function_name);
                                    }

                                }

                                $step++;
                            }

                            break;
                        }

                        $field_name = cutWordInBrackersInString($table_field);
                        $field_type = cutTypeFromString($table_field);

                        $field_data = array(
                            'name' => $field_name,
                            'type' => $field_type
                        );

                        $table_fields_data[] = $field_data;
                    }


                }

            }

        }

        if (!feof($db_file)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($db_file);

    }
