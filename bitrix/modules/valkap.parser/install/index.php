<?
IncludeModuleLangFile(__FILE__);
Class valkap_parser extends CModule
{
	const MODULE_ID = 'valkap.parser';
	var $MODULE_ID = 'valkap.parser'; 
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("valkap.parser_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("valkap.parser_MODULE_DESC");

		$this->PARTNER_NAME = GetMessage("valkap.parser_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("valkap.parser_PARTNER_URI");
	}

	function InstallDB($arParams = array())
	{
		RegisterModuleDependences('main', 'OnBuildGlobalMenu', self::MODULE_ID, 'CValkapParser', 'OnBuildGlobalMenu');
		return true;
	}

	function UnInstallDB($arParams = array())
	{
		UnRegisterModuleDependences('main', 'OnBuildGlobalMenu', self::MODULE_ID, 'CValkapParser', 'OnBuildGlobalMenu');
		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles($arParams = array())
	{
        //CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/valkap.parser/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/valkap.parser/install/admin/valkap_parser_admin.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/valkap_parser_admin.php", true, true);
		return true;
	}

    function UnInstallFiles()
    {
        //DeleteDirFilesEx("/bitrix/components/valkap/section.list");
        DeleteDirFilesEx("/bitrix/admin/valkap_parser_admin.php");
        return true;
    }

    function InstallIblock()
    {
        if(!CModule::IncludeModule("iblock"))
            return;

        if(!CModule::IncludeModule("catalog"))
            return;

        //add type iblock
        $res = CIBlockType::GetByID("valkap_parser");
        if(!$v = $res->GetNext())
        {
            $arFields = Array(
                'ID'=>'valkap_parser',
                'SECTIONS'=>'Y',
                'IN_RSS'=>'N',
                'SORT'=>100,
                'LANG'=>Array(
                    'ru'=>Array(
                        'NAME'=>GetMessage("VALKAP_IB_PARSER")
                    )
                )
            );
            $obBlocktype = new CIBlockType;
            $obBlocktype->Add($arFields);
        }
        //add iblock

        $rsSites = CSite::GetList($by="sort", $order="desc", Array());
        $i = 0;
        while ($arSite = $rsSites->Fetch())
        {
            $arSiteID[$i] = $arSite["ID"];
            $i++;
        }
        $res = CIBlock::GetList(
            Array(),
            Array(
                'TYPE'=>'valkap_parser',
                'CODE'=>'valkap_parser'
            ),
            true
        );
        $check_ib = false;
        while($ar_res = $res->Fetch())
            if($ar_res) $check_ib = true;
        if(!$check_ib)
            for($i = 0; $i < count($arSiteID); $i++)
            {
                $ib = new CIBlock;
                $arFields = Array(
                    "ACTIVE" => "Y",
                    "NAME" => GetMessage("VALKAP_IB_PARSER"),
                    "CODE" => "valkap_parser",
                    "IBLOCK_TYPE_ID" => "valkap_parser",
                    "INDEX_ELEMENT" => "N",
                    "INDEX_SECTION" => "N",
                    "WORKFLOW" => "N",
                    "SITE_ID" => $arSiteID[$i]
                );
                $iblockID = $ib->Add($arFields);
            }

        //transform iblock to catalog
		$arFields = array(
            'IBLOCK_ID' => $iblockID,
        );
        $boolResult = CCatalog::Add($arFields);

        if ($boolResult == false)
        {
            if ($ex = $APPLICATION->GetException())
            {
                $strError = $ex->GetString();
                ShowError($strError);
            }
        }

        //add props
        $res = CIBlock::GetList(Array(),Array("CODE"=>'valkap_parser'),true);
        $ar_res = $res->Fetch();
        $rsProp = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$ar_res["ID"]));
        while ($arr=$rsProp->Fetch())
            $arPropsCode[] = $arr["CODE"];
        if(!is_array($arPropsCode)){
            $arPropsCode = array();
        }
        if(!in_array("VENDOR", $arPropsCode))
        {
            $arFields = Array(
                "NAME" => GetMessage("VALKAP_IB_VENDOR"),
                "ACTIVE" => "Y",
                "SORT" => "100",
                "CODE" => "VENDOR",
                "PROPERTY_TYPE" => "L",
                "IBLOCK_ID" => $ar_res['ID']
            );
            $ibp = new CIBlockProperty;
            $PropID = $ibp->Add($arFields);
        }
        if(!in_array("MATERIAL", $arPropsCode))
        {
            $arFields = Array(
                "NAME" => GetMessage("VALKAP_IB_MATERIAL"),
                "ACTIVE" => "Y",
                "SORT" => "100",
                "CODE" => "MATERIAL",
                "PROPERTY_TYPE" => "S",
                "IBLOCK_ID" => $ar_res['ID']
            );
            $ibp = new CIBlockProperty;
            $PropID = $ibp->Add($arFields);
        }
        CIBlock::SetPermission($ar_res['ID'], Array("1"=>"X", "2"=>"R"));
    }

	function UnInstallIblock()
	{
        if(!CModule::IncludeModule("iblock"))
            return;

        $res = CIBlock::GetList(
            Array(),
            Array(
                'TYPE'=>'valkap_parser',
                'CODE'=>'valkap_parser'
            ),
            true
        );
        while($ar_res = $res->Fetch())
            if($ar_res)
            {
                CIBlock::Delete('valkap_parser');
                CIBlockType::Delete('valkap_parser');
            }

	}

	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallFiles();
		$this->InstallDB();
        $this->InstallIblock();
		RegisterModule(self::MODULE_ID);
	}

	function DoUninstall()
	{
		global $APPLICATION;
		UnRegisterModule(self::MODULE_ID);
		$this->UnInstallDB();
		$this->UnInstallFiles();
		$this->UnInstallIblock();
	}
}
?>
