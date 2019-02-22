<?
IncludeModuleLangFile(__FILE__);

if($APPLICATION->GetGroupRight("valkap.parser")!="D")
{
    CModule::IncludeModule('valkap.parser');
    $aMenu = array(
        "parent_menu" => "global_menu_services",
        "section" => "valkap.parser",
        "sort" => 50,
        "module_id" => "valkap.parser",
        "text" => GetMessage("VALKAP_PARSER_MENU_MAIN"),
        "title" => GetMessage("VALKAP_PARSER_MENU_MAIN_TITLE"),
        "url" => "valkap_parser_admin.php?lang=".LANGUAGE_ID,
        "icon" => "valkap_parser_menu_icon",
        "page_icon" => "valkap_parser_page_icon",
        "items_id" => "menu_valkap_parser",
        "items" => array(
            array(
                "text" => GetMessage("VALKAP_PARSER_MENU_EVENTS"),
                "url" => "valkap_parser_admin.php?lang=".LANGUAGE_ID,
                "more_url" => Array(
                    "valkap_parser_admin.php"
                ),
                "title" => GetMessage("VALKAP_PARSER_MENU_EVENTS_TITLE"),
            ),
        )
    );
    return $aMenu;
}
return false;
?>
