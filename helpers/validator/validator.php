<?php
if (!class_exists('starfish')) { die(); }

class validator
{
        /*
        Data
        - fields to be validated

        Rules for validation

        - allow-blank
        - min-length
        - max-length
        - maskre
        - equals - equals a certain value

        - errortxt  - text for the error
        - usable for: maskre, equals

        Error
        - nume_camp => nume_camp_eroare
        - if nothing is specified, then the name of the field is the destination of the error
        ----> if same name for more fields, do interpretation of array in the tpl error displayer

        */
        function exec($data, $rules=array(), $error=array())
        {
                $error_list = array();
                $valid_overall = 1;

                foreach ($data as $key=>$value)
                {
                        if (isset($rules[$key]))
                        {
                                $valid[$key] = 1;

                                foreach ($rules[$key] as $key2=>$value2)
                                {
                                        switch ($key2)
                                        {
                                                case 'allow-blank':
                                                if ($valid[$key] == 1 && strlen($value) == 0)
                                                {
                                                        $error_list[$key] = "This is a mandatory field.";
                                                        $valid[$key] = 0;
                                                        $valid_overall = 0;
                                                }
                                                break;
                                                case 'min-length':
                                                if ($valid[$key] == 1 && strlen($value) < $value2)
                                                {
                                                        $error_list[$key] = "The minimum length is ".$value2." characters.";
                                                        $valid[$key] = 0;
                                                        $valid_overall = 0;
                                                }
                                                break;
                                                case 'max-length':
                                                if ($valid[$key] == 1 && strlen($value) > $value2)
                                                {
                                                        $error_list[$key] = "The maximum length is ".$value2." characters.";
                                                        $valid[$key] = 0;
                                                        $valid_overall = 0;
                                                }
                                                break;
                                                case 'maskre':
                                                if ($valid[$key] == 1 && preg_match($value2, $value) != true)
                                                {
                                                        if (strlen($rules[$key]['errortxt']) > 0)
                                                        {
                                                                $error_list[$key] = $rules[$key]['errortxt'];
                                                        }
                                                        else
                                                        {
                                                                $error_list[$key] = "The input format is not correct.";
                                                        }
                                                        $valid[$key] = 0;
                                                        $valid_overall = 0;
                                                }
                                                break;

                                                case 'equals':
                                                if ($valid[$key] == 1 && $value2 != $value)
                                                {
                                                        if (strlen($rules[$key]['errortxt']) > 0)
                                                        {
                                                                $error_list[$key] = $rules[$key]['errortxt'];
                                                        }
                                                        else
                                                        {
                                                                $error_list[$key] = "Invalid information.";
                                                        }
                                                        $valid[$key] = 0;
                                                        $valid_overall = 0;
                                                }
                                                break;

                                                case 'in-array':
                                                if ($valid[$key] == 1 && is_array($value2))
                                                {
                                                        $exists = false;
                                                        foreach ($value2 as $k3=>$v3)
                                                        {
                                                                if ($v3 == $value) { $exists = true; break; }
                                                        }

                                                        if ($exists == false)
                                                        {
                                                                if (strlen($rules[$key]['errortxt']) > 0)
                                                                {
                                                                        $error_list[$key] = $rules[$key]['errortxt'];
                                                                }
                                                                else
                                                                {
                                                                        $error_list[$key] = "Invalid information.";
                                                                }
                                                                $valid[$key] = 0;
                                                                $valid_overall = 0;
                                                        }
                                                }
                                                break;

                                                case 'numeric':
                                                if ($valid[$key] == 1 && (!is_numeric($value) && strlen($value) > 0) && $value2 == true)
                                                {
                                                        if (strlen($rules[$key]['errortxt']) > 0)
                                                        {
                                                                $error_list[$key] = $rules[$key]['errortxt'];
                                                        }
                                                        else
                                                        {
                                                                $error_list[$key] = "The input data is not a number.";
                                                        }
                                                        $valid[$key] = 0;
                                                        $valid_overall = 0;
                                                }
                                                if ($valid[$key] == 1 && (is_numeric($value) && strlen($value) > 0) && $value2 == false)
                                                {
                                                        if (strlen($rules[$key]['errortxt']) > 0)
                                                        {
                                                                $error_list[$key] = $rules[$key]['errortxt'];
                                                        }
                                                        else
                                                        {
                                                                $error_list[$key] = "The input data is a number.";
                                                        }
                                                        $valid[$key] = 0;
                                                        $valid_overall = 0;
                                                }
                                                break;

                                                case 'numeric-min':
                                                if ($valid[$key] == 1 && is_numeric($value2) && (!is_numeric($value) || $value >= $value2))
                                                {
                                                        if (strlen($rules[$key]['errortxt']) > 0)
                                                        {
                                                                $error_list[$key] = $rules[$key]['errortxt'];
                                                        }
                                                        else
                                                        {
                                                                $error_list[$key] = "Invalid information.";
                                                        }
                                                        $valid[$key] = 0;
                                                        $valid_overall = 0;
                                                }
                                                break;

                                                case 'numeric-max':
                                                if ($valid[$key] == 1 && is_numeric($value2) && (!is_numeric($value) || $value <= $value2))
                                                {
                                                        if (strlen($rules[$key]['errortxt']) > 0)
                                                        {
                                                                $error_list[$key] = $rules[$key]['errortxt'];
                                                        }
                                                        else
                                                        {
                                                                $error_list[$key] = "Invalid information.";
                                                        }
                                                        $valid[$key] = 0;
                                                        $valid_overall = 0;
                                                }
                                                break;
                                        }
                                }

                                // add into error list
                                if (isset($error_list[$key]) && strlen($error_list[$key]) > 0)
                                {
                                        if (isset($error[$key]))
                                        {
                                                err($error[$key], $error_list[$key]);
                                        }
                                        else
                                        {
                                                err($key, $error_list[$key]);
                                        }
                                }
                        }
                }

                // Return the result
                if ($valid_overall == 1)
                {
                        return true;
                }
                else
                {
                        return $error_list;
                }
        }
}

?>