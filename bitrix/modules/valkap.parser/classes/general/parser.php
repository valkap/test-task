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

    public static function Update($elID, $price, $count)
    {
        //add (update) count
        $fieldsCatalog = array(
            "ID" => $elID,
            "TYPE" => \Bitrix\Catalog\ProductTable::TYPE_PRODUCT,
            "QUANTITY" => $count,
        );

        $result = \Bitrix\Catalog\Model\Product::Update($elID, $fieldsCatalog);
        if ($result->isSuccess())
        {
            echo "Добавил количество товаров " . $elID . " Количество " . $count . PHP_EOL;
        }
        else {
            echo "Ошибка добавления количества товара " . $elID . " Ошибка " . $result->getErrorMessages() . PHP_EOL;
        }

        //add (update) price
        $arFieldsPrice = Array(
            "PRODUCT_ID" => $elID,
            "CATALOG_GROUP_ID" => 1,
            "PRICE" => $price,
            "CURRENCY" => "UAH",
        );

        $dbPrice = \Bitrix\Catalog\Model\Price::getList([
            "filter" => array(
                "PRODUCT_ID" => $elID,
                "CATALOG_GROUP_ID" => 1
            )]);


        if ($arPrice = $dbPrice->fetch()) {
            $result = \Bitrix\Catalog\Model\Price::update($arPrice["ID"], $arFieldsPrice);
            if ($result->isSuccess())
            {
                echo "Обновили цену у товара у элемента каталога " . $elID . " Цена " . $price . PHP_EOL;
            }
            else {
                echo "Ошибка обновления цены у товара у элемента каталога " . $elID . " Ошибка " . $result->getErrorMessages() . PHP_EOL;
            }
        }else{
            $result = \Bitrix\Catalog\Model\Price::add($arFieldsPrice);
            if ($result->isSuccess())
            {
                echo "Добавили цену у товара у элемента каталога " . $elID . " Цена " . $price . PHP_EOL;
            }
            else {
                echo "Ошибка добавления цены у товара у элемента каталога " . $elID . " Ошибка " . $result->getErrorMessages() . PHP_EOL;
            }
        }
    }
}