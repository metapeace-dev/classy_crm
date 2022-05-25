<?php
namespace App\Traits;
/**
 * Created by PhpStorm.
 * User: hosseini
 * Date: 1/27/18
 * Time: 12:43 PM
 */
use Illuminate\Support\Facades\DB;

trait EnumValue
{
    public static function getEnumColumnValues($table_name,$column_name)
    {
        $type = DB::select(DB::raw("SHOW COLUMNS FROM $table_name WHERE Field = '{$column_name}'"))[0]->Type ;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $enum_values = array();
        foreach( explode(',', $matches[1]) as $value )
        {
            $v = trim( $value, "'" );
            array_push($enum_values, $v);
        }
        return $enum_values;
    }
}