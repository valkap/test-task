<?php
/**
 * Created by PhpStorm.
 * User: Valera
 * Date: 23.02.2019
 * Time: 17:55
 */
class ValkapParser
{
    public static function GetPropertyId($propertyCode, $iblockID)
    {
        $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$iblockID,"CODE"=>$propertyCode));
        while ($prop_fields = $properties->GetNext())
        {
            return $prop_fields["ID"];
        }
    }
}