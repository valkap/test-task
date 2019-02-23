<?php
/**
 * Created by PhpStorm.
 * User: Valera
 * Date: 23.02.2019
 * Time: 17:55
 */
class ValkapParser
{
    function GetPropertyId($propertyCode, $iblockID)
    {
        $properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$iblockID,"CODE"=>$propertyCode));
        while ($prop_fields = $properties->GetNext())
        {
            return $prop_fields["ID"];
        }
    }

    function GetPropertyVendor($iblockID)
    {
        $arVendor = array();
        $property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblockID, "CODE"=>"VENDOR"));
        while($enum_fields = $property_enums->GetNext())
        {
            $arVendor[$enum_fields["ID"]] = $enum_fields["VALUE"];
        }
        return $arVendor;
    }

    public function GetPropertyArray($arRes, $iblockID)
    {
        $PROP = array();

        if (in_array($arRes[2], self::GetPropertyVendor($iblockID))) //value property VENDOR isset
        {
            $PROP["VENDOR"] = Array("VALUE" => array_search($arRes[2], self::GetPropertyVendor($iblockID)));
        }
        else //new value property VENDOR
        {
            $propertyID = self::GetPropertyId("VENDOR", $iblockID);
            $ibpenum = new CIBlockPropertyEnum;
            if($PropID = $ibpenum->Add(Array('PROPERTY_ID'=> $propertyID, 'VALUE'=> $arRes[2])))
            {
                $PROP["VENDOR"] = Array("VALUE" => $PropID);
            }
        }
        $PROP["MATERIAL"] = $arRes[3];
        return $PROP;
    }
}