<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/valkap.parser/include.php");
IncludeModuleLangFile(__FILE__);

use Bitrix\Main\Loader;
Loader::includeModule("iblock");
Loader::includeModule('catalog');

$io = CBXVirtualIo::GetInstance();

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
    $DATA_FILE_NAME = "";
    if (isset($_FILES["DATA_FILE"]) && is_uploaded_file($_FILES["DATA_FILE"]["tmp_name"]))
    {
        if (strtolower(GetFileExtension($_FILES["DATA_FILE"]["name"])) != "csv")
        {
            $strError.= GetMessage("VALKAP_PARSER_IMP_NOT_CSV")."<br>";
        }
        else
        {
            $DATA_FILE_NAME = "/".COption::GetOptionString("main", "upload_dir", "upload")."/".basename($_FILES["DATA_FILE"]["name"]);
            if ($APPLICATION->GetFileAccessPermission($DATA_FILE_NAME) >= "W")
                copy($_FILES["DATA_FILE"]["tmp_name"], $_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME);
            else
                $DATA_FILE_NAME = "";
        }
    }

    if (strlen($strError) <= 0)
    {
        if (strlen($DATA_FILE_NAME) <= 0)
        {
            if (strlen($URL_DATA_FILE) > 0)
            {
                $URL_DATA_FILE = trim(str_replace("\\", "/", trim($URL_DATA_FILE)) , "/");
                $FILE_NAME = rel2abs($_SERVER["DOCUMENT_ROOT"], "/".$URL_DATA_FILE);
                if (
                    (strlen($FILE_NAME) > 1)
                    && ($FILE_NAME === "/".$URL_DATA_FILE)
                    && $io->FileExists($_SERVER["DOCUMENT_ROOT"].$FILE_NAME)
                    && ($APPLICATION->GetFileAccessPermission($FILE_NAME) >= "W")
                )
                {
                    $DATA_FILE_NAME = $FILE_NAME;
                }
            }
        }

        if (strlen($DATA_FILE_NAME) <= 0)
            $strError.= GetMessage("IBLOCK_ADM_IMP_NO_DATA_FILE_SIMPLE")."<br>";

        if (!CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "element_edit_any_wf_status"))
            $strError.= GetMessage("IBLOCK_ADM_IMP_NO_IBLOCK")."<br>";
    }

    $iblockID = strval($_POST["IBLOCK_ID"]);
    $countAdd = 0;
    $countUpdate = 0;
    $strError = '';

    require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/csv_data.php");
    $csvFile = new CCSVData('R', true);
    $csvFile->LoadFile($_SERVER["DOCUMENT_ROOT"].$DATA_FILE_NAME);
    $csvFile->SetDelimiter(';');

    while ($arRes = $csvFile->Fetch())
    {
        $res = \Bitrix\Iblock\ElementTable::getList(array(
            'select' => array('ID', 'NAME', 'IBLOCK_ID', 'CODE'),
            'filter' => array('IBLOCK_ID' => $iblockID, "CODE" => $arRes[0])
        ));
        if($ob = $res->Fetch()) //element update
        {
           $elID = $ob["ID"];

            $el = new CIBlockElement;

            $arLoadProductArray = Array(
                "MODIFIED_BY"    => $USER->GetID(),
                "IBLOCK_SECTION" => false,
                "PROPERTY_VALUES"=> ValkapParser::GetPropertyArray($arRes, $iblockID),
                "NAME"           => $arRes[1],
            );

            if($el->Update($elID, $arLoadProductArray))
                $countUpdate++;
            else
                echo "Error: ".$el->LAST_ERROR;


            if (!ValkapParser::Update($elID, $arRes[5], $arRes[4]))
            {
                $strError .= GetMessage("VALKAP_PARSER_ERROR_UPDATE").' '.$elID.'<br>';
            }
        }
        else //element add
        {
            $el = new CIBlockElement;

            $arLoadProductArray = Array(
                "MODIFIED_BY"    => $USER->GetID(),
                "IBLOCK_SECTION_ID" => false,
                "IBLOCK_ID"      => $iblockID,
                "PROPERTY_VALUES"=> ValkapParser::GetPropertyArray($arRes, $iblockID),
                "CODE" => $arRes[0],
                "NAME"           => $arRes[1],
                "ACTIVE"         => "Y",
            );

            if($PRODUCT_ID = $el->Add($arLoadProductArray))
            {
                $fields = array(
                    'ID' => $PRODUCT_ID,
                    'TYPE' => \Bitrix\Catalog\ProductTable::TYPE_PRODUCT,
                );
                \Bitrix\Catalog\Model\Product::Add($fields);

            if (!ValkapParser::Update($PRODUCT_ID, $arRes[5], $arRes[4]))
                {
                    $strError .= GetMessage("VALKAP_PARSER_ERROR_UPDATE").' '.$PRODUCT_ID.'<br>';
                }
                $countAdd ++;
            }

            else
                echo "Error: ".$el->LAST_ERROR;
        }
    }
    header('Location:'.$APPLICATION->GetCurPageParam('err='.$strError.'&add='.$countAdd.'&update='.$countUpdate));
}

?>
<?
$APPLICATION->SetTitle(GetMessage("VALKAP_PARSER_PAGE_TITLE"));
require ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>

<div class="adm-detail-content-item-block">
    <?
    $strError = strval($_GET['err']);
    $countUpdate = intval($_GET['update']);
    $countAdd = intval($_GET['add']);

    if ($countUpdate > 0 || $countAdd > 0 || strlen($strError) > 0)
    {?>
       <div class="result-msg">
           <?=$strError?>
           <p>Добавлено записей - <?=$countAdd?></p>
           <p>Обновлено записей - <?=$countUpdate?></p>
       </div>
    <?}

    ?>
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
    <div class="adm-detail-content-btns-wrap">
        <div class="adm-detail-content-btns">
            <input type="submit" value="Загрузить" name="submit_btn" class="adm-btn-save">
        </div>
    </div>
    </form>
</div>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
