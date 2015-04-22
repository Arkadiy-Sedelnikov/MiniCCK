<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Аркадий
 * Date: 16.12.12
 * Time: 14:05
 * To change this template use File | Settings | File Templates.
 */
class dateConversion
{


    static function HumanDatePrecise($date)
    {
        $d = time() - strtotime($date);

        //отнимаем 4 часа для верности времени
        $d = (($d-14400) > 0) ? $d-14400 : $d;

        if ($d >= 0) {
            if ($d < 3600) {
//минут назад
                switch (substr(floor($d / 60), -1)) {
                    case 0:
                        $abc = "минут назад";
                        break;
                    case 1:
                        $abc = "минуту назад";
                        break;
                    case 2:
                    case 3:
                    case 4:
                        $abc = "минуты назад";
                        break;
                    default:
                        $abc = ' минут назад';
                        break;

                }
                ;
                return floor($d / 60) . ' ' . $abc;
            }
            elseif ($d < 86400) {
//часов назад
                $hour = floor($d / 3600);
                switch ($hour) {

                    case 1:
                    case 21:
                        $abc = "час назад";
                        break;
                    case 2:
                    case 22:
                    case 23:
                    case 24:
                    case 3:
                    case 4:
                        $abc = "часа назад";
                        break;
                    default:
                        $abc = ' часов назад';
                        break;
                }
                return $hour.' '.$abc;

            }
//            elseif ($d < 172800) {
//                return 'вчера';
//            }
            else {
                return date("d-m-Y", strtotime($date));
            }
        }
        else{
            return date("d-m-Y", strtotime($date));
        }
    }
}