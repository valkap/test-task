<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("iblock");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

//require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/valkap.parser/include.php");
//require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/valkap.parser/prolog.php");

//IncludeModuleLangFile(__FILE__);
?>
<?
$APPLICATION->SetTitle(GetMessage("VALKAP_PARSER_PAGE_TITLE"));
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<div class="adm-detail-content-item-block">
    <form method="POST" action="<?=$APPLICATION->GetCurPage();?>?lang=<?=LANGUAGE_ID; ?>" ENCTYPE="multipart/form-data" name="dataload" id="dataload">
        <table class="adm-detail-content-table edit-table">
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><?echo GetMessage("VALKAP_PARSER_IMP_DATA_FILE"); ?></td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input type="text" name="URL_DATA_FILE" value="<?echo htmlspecialcharsbx($URL_DATA_FILE); ?>" size="30">
                <input type="button" value="<?echo GetMessage("VALKAP_PARSER_IMP_OPEN"); ?>" OnClick="BtnClick()">
                <?CAdminFileDialog::ShowScript(array(
                    "event" => "BtnClick",
                    "arResultDest" => array(
                        "FORM_NAME" => "dataload",
                        "FORM_ELEMENT_NAME" => "URL_DATA_FILE",
                    ) ,
                    "arPath" => array(
                        "SITE" => SITE_ID,
                        "PATH" => "/".COption::GetOptionString("main", "upload_dir", "upload"),
                    ) ,
                    "select" => 'F', // F - file only, D - folder only
                    "operation" => 'O', // O - open, S - save
                    "showUploadTab" => true,
                    "showAddToMenuTab" => false,
                    "fileFilter" => 'csv',
                    "allowAllFiles" => true,
                    "SaveConfig" => true,
                ));
                ?>
            </td>
        </tr>
            <tr>
                <td class="adm-detail-content-cell-l"><?echo GetMessage("VALKAP_PARSER_IMP_INFOBLOCK"); ?></td>
                <td class="adm-detail-content-cell-r">
                    <?echo GetIBlockDropDownList($IBLOCK_ID, 'IBLOCK_TYPE_ID', 'IBLOCK_ID', false, 'class="adm-detail-iblock-types"', 'class="adm-detail-iblock-list"'); ?>
                </td>
            </tr>
        </table>
    </form>
</div>
<div class="adm-detail-content-btns-wrap">
    <div class="adm-detail-content-btns">
        <input type="submit" value="Загрузить" name="submit_btn" class="adm-btn-save">
    </div>
</div>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
