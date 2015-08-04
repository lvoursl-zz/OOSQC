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
