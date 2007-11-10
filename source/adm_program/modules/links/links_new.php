<?php
/******************************************************************************
 * Links anlegen und bearbeiten
 *
 * Copyright    : (c) 2004 - 2007 The Admidio Team
 * Homepage     : http://www.admidio.org
 * Module-Owner : Daniel Dieckelmann
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Uebergaben:
 *
 * lnk_id        - ID der Ankuendigung, die bearbeitet werden soll
 * headline      - Ueberschrift, die ueber den Links steht
 *                 (Default) Links
 *
 *****************************************************************************/

require("../../system/common.php");
require("../../system/login_valid.php");
require("../../system/weblink_class.php");

// pruefen ob das Modul ueberhaupt aktiviert ist
if ($g_preferences['enable_weblinks_module'] == 0)
{
    // das Modul ist deaktiviert
    $g_message->show("module_disabled");
}


// Ist ueberhaupt das Recht vorhanden?
if (!$g_current_user->editWeblinksRight())
{
    $g_message->show("norights");
}

// Uebergabevariablen pruefen
if (array_key_exists("lnk_id", $_GET))
{
    if (is_numeric($_GET["lnk_id"]) == false)
    {
        $g_message->show("invalid");
    }
}
else
{
    $_GET["lnk_id"] = 0;
}

if (array_key_exists("headline", $_GET))
{
    $_GET["headline"] = strStripTags($_GET["headline"]);
}
else
{
    $_GET["headline"] = "Links";
}

$_SESSION['navigation']->addUrl(CURRENT_URL);

// Weblinkobjekt anlegen
$link = new Weblink($g_db);

if($_GET["lnk_id"] > 0)
{
    $link->getWeblink($_GET["lnk_id"]);
}

if(isset($_SESSION['links_request']))
{
    // durch fehlerhafte Eingabe ist der User zu diesem Formular zurueckgekehrt
    // nun die vorher eingegebenen Inhalte auslesen
    foreach($_SESSION['links_request'] as $key => $value)
    {
        if(strpos($key, "lnk_") == 0)
        {
            $link->setValue($key, stripslashes($value));
        }        
    }
    unset($_SESSION['links_request']);
}
/*
if (isset($_SESSION['links_request']))
{
    $form_values = strStripSlashesDeep($_SESSION['links_request']);
    unset($_SESSION['links_request']);
}
else
{
    $form_values['linkname']    = "";
    $form_values['description'] = "";
    $form_values['linkurl']     = "";
    $form_values['category']    = 0;

    // Wenn eine Link-ID uebergeben wurde, soll der Link geaendert werden
    // -> Felder mit Daten des Links vorbelegen
    if ($_GET["lnk_id"] != 0)
    {
        $sql    = "SELECT * FROM ". TBL_LINKS. ", ". TBL_CATEGORIES ."
                    WHERE lnk_id     = ". $_GET['lnk_id']. " 
                      AND lnk_cat_id = cat_id
                      AND cat_org_id = ". $g_current_organization->getValue("org_id");
        $result = $g_db->query($sql);

        if ($g_db->num_rows($result) > 0)
        {
            $row_ba = $g_db->fetch_object($result);

            $form_values['linkname']    = $row_ba->lnk_name;
            $form_values['description'] = $row_ba->lnk_description;
            $form_values['linkurl']     = $row_ba->lnk_url;
            $form_values['category']    = $row_ba->lnk_cat_id;
        }
        elseif ($g_db->num_rows($result) == 0)
        {
            //Wenn keine Daten zu der ID gefunden worden bzw. die ID einer anderen Orga gehört ist Schluss mit lustig...
            $g_message->show("invalid");
        }
    }
}
*/
// Html-Kopf ausgeben
$g_layout['title'] = $_GET["headline"];
require(SERVER_PATH. "/adm_program/layout/overall_header.php");

// Html des Modules ausgeben
if($_GET["lnk_id"] > 0)
{
    $new_mode = "3";
}
else
{
    $new_mode = "1";
}

echo "
<form action=\"$g_root_path/adm_program/modules/links/links_function.php?lnk_id=". $_GET["lnk_id"]. "&amp;headline=". $_GET['headline']. "&amp;mode=$new_mode\" method=\"post\">
<div class=\"formLayout\" id=\"edit_links_form\">
    <div class=\"formHead\">";
        if($_GET["lnk_id"] > 0)
        {
            echo $_GET["headline"]. " &auml;ndern";
        }
        else
        {
            echo $_GET["headline"]. " anlegen";
        }
    echo "</div>
    <div class=\"formBody\">
        <ul class=\"formFieldList\">
            <li>
                <dl>
                    <dt><label for=\"lnk_name\">Linkname:</label></dt>
                    <dd>
                        <input type=\"text\" id=\"lnk_name\" name=\"lnk_name\" tabindex=\"1\" style=\"width: 350px;\" maxlength=\"250\" value=\"". $link->getValue("lnk_name"). "\" />
                        <span class=\"mandatoryFieldMarker\" title=\"Pflichtfeld\">*</span>
                    </dd>
                </dl>
            </li>
            <li>
                <dl>
                    <dt><label for=\"lnk_url\">Linkadresse:</label></dt>
                    <dd>
                        <input type=\"text\" id=\"lnk_url\" name=\"lnk_url\" tabindex=\"2\" style=\"width: 350px;\" maxlength=\"250\" value=\"". $link->getValue("lnk_url"). "\" />
                        <span class=\"mandatoryFieldMarker\" title=\"Pflichtfeld\">*</span>
                    </dd>
                </dl>
            </li>
            <li>
                <dl>
                    <dt><label for=\"lnk_cat_id\">Kategorie:</label></dt>
                    <dd>
                        <select id=\"lnk_cat_id\" name=\"lnk_cat_id\" size=\"1\" tabindex=\"3\">
                            <option value=\" \""; 
                                if($form_values['category'] == 0) 
                                {
                                    echo " selected=\"selected\"";
                                }
                                echo ">- Bitte w&auml;hlen -</option>";

                            $sql = "SELECT * FROM ". TBL_CATEGORIES. "
                                     WHERE cat_org_id = ". $g_current_organization->getValue("org_id"). "
                                       AND cat_type   = 'LNK'
                                     ORDER BY cat_sequence ASC ";
                            $result = $g_db->query($sql);

                            while($row = $g_db->fetch_object($result))
                            {
                                echo "<option value=\"$row->cat_id\"";
                                    if($link->getValue("lnk_cat_id") == $row->cat_id)
                                    {
                                        echo " selected=\"selected\" ";
                                    }
                                echo ">$row->cat_name</option>";
                            }
                        echo "</select>
                        <span class=\"mandatoryFieldMarker\" title=\"Pflichtfeld\">*</span>
                    </dd>
                </dl>
            </li>
            <li>
                <dl>
                    <dt><label for=\"lnk_description\">Beschreibung:</label>";
                        if($g_preferences['enable_bbcode'] == 1)
                        {
                          echo "<br /><br />
                          <a href=\"#\" onclick=\"window.open('$g_root_path/adm_program/system/msg_window.php?err_code=bbcode','Message','width=600,height=600,left=310,top=200,scrollbars=yes')\" tabindex=\"7\">Text formatieren</a>";
                        }
                    echo "</dt>
                    <dd>
                        <textarea id=\"lnk_description\" name=\"lnk_description\" tabindex=\"4\" style=\"width: 350px;\" rows=\"10\" cols=\"40\">". $link->getValue("lnk_description"). "</textarea>
                        <span class=\"mandatoryFieldMarker\" title=\"Pflichtfeld\">*</span>
                    </dd>
                </dl>
            </li>
        </ul>

        <hr />

        <div class=\"formSubmit\">
            <button name=\"speichern\" type=\"submit\" value=\"speichern\" tabindex=\"5\">
                <img src=\"$g_root_path/adm_program/images/disk.png\" alt=\"Speichern\" />
                &nbsp;Speichern</button>
        </div>
    </div>
</div>
</form>

<ul class=\"iconTextLinkList\">
    <li>
        <span class=\"iconTextLink\">
            <a href=\"$g_root_path/adm_program/system/back.php\"><img 
            src=\"$g_root_path/adm_program/images/back.png\" alt=\"Zurück\" /></a>
            <a href=\"$g_root_path/adm_program/system/back.php\">Zurück</a>
        </span>
    </li>
</ul>

<script type=\"text/javascript\"><!--
    document.getElementById('linkname').focus();
--></script>";

require(SERVER_PATH. "/adm_program/layout/overall_footer.php");

?>