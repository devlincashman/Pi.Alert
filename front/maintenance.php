<?php
session_start();

// Turn off php errors
error_reporting(0);

if ($_SESSION["login"] != 1)
  {
      header('Location: index.php');
      exit;
  }

//------------------------------------------------------------------------------
//  Pi.Alert
//  Open Source Network Guard / WIFI & LAN intrusion detector 
//
//  devices.php - Front module. Server side. Manage Devices
//------------------------------------------------------------------------------
//  Puche      2021        pi.alert.application@gmail.com   GNU GPLv3
//  jokob-sk   2022        jokob.sk@gmail.com               GNU GPLv3
//  leiweibau  2022        https://github.com/leiweibau     GNU GPLv3
//------------------------------------------------------------------------------

// Language selector config ----------------------------------------------------
//
// For security reasons, new language files must be entered into this array.
// The files in the language directory are compared with this array and only 
// then accepted.
//
$pia_installed_langs = array('en_us', 
                             'de_de',
                             'es_es');
//
// In addition to this, the language must also be added to the select tag in 
// line 235. Later, the whole thing may become dynamic.

// Skin selector config ----------------------------------------------------
//
// For security reasons, new language files must be entered into this array.
// The files in the language directory are compared with this array and only 
// then accepted.
//
$pia_installed_skins = array('skin-black-light', 
                             'skin-black', 
                             'skin-blue-light', 
                             'skin-blue', 
                             'skin-green-light', 
                             'skin-green', 
                             'skin-purple-light', 
                             'skin-purple', 
                             'skin-red-light', 
                             'skin-red', 
                             'skin-yellow-light', 
                             'skin-yellow');
  

//------------------------------------------------------------------------------
?>

<?php
  require 'php/templates/header.php';
?>
<!-- Page ------------------------------------------------------------------ -->
<div class="content-wrapper">

<!-- Content header--------------------------------------------------------- -->
    <section class="content-header">
    <?php require 'php/templates/notification.php'; ?>
      <h1 id="pageTitle">
         <?php echo lang('Maintenance_Title');?>
      </h1>
    </section>

    <!-- Main content ---------------------------------------------------------- -->
    <section class="content">


  <?php

// Size and last mod of DB ------------------------------------------------------

$pia_db = str_replace('front', 'db', getcwd()).'/pialert.db';
$pia_db_size = number_format((filesize($pia_db) / 1000000),2,",",".") . ' MB';
$pia_db_mod = date ("F d Y H:i:s", filemtime($pia_db));

// Pause Arp Scan ---------------------------------------------------------------

if (!file_exists('../db/setting_stoparpscan')) {
  $execstring = 'ps -f -u root | grep "sudo arp-scan" 2>&1';
  $pia_arpscans = "";
  exec($execstring, $pia_arpscans);
  $pia_arpscans_result = sizeof($pia_arpscans).' '.lang('Maintenance_arp_status_on');
} else {
  $pia_arpscans_result = '<span style="color:red;">arp-Scan '.lang('Maintenance_arp_status_off') .'</span>';
}

// Count and Calc Backups -------------------------------------------------------

$Pia_Archive_Path = str_replace('front', 'db', getcwd()).'/';
$Pia_Archive_count = 0;
$Pia_Archive_diskusage = 0;
$files = glob($Pia_Archive_Path."pialertdb_*.zip");
if ($files){
 $Pia_Archive_count = count($files);
}
foreach ($files as $result) {
    $Pia_Archive_diskusage = $Pia_Archive_diskusage + filesize($result);
}
$Pia_Archive_diskusage = number_format(($Pia_Archive_diskusage / 1000000),2,",",".") . ' MB';

// Find latest Backup for restore -----------------------------------------------

$latestfiles = glob($Pia_Archive_Path."pialertdb_*.zip");
natsort($latestfiles);
$latestfiles = array_reverse($latestfiles,False);
$latestbackup = $latestfiles[0];
$latestbackup_date = date ("Y-m-d H:i:s", filemtime($latestbackup));

// Skin selector -----------------------------------------------------------------

if (submit && isset($_POST['skinselector_set'])) {
  $pia_skin_set_dir = '../db/';
  $pia_skin_selector = htmlspecialchars($_POST['skinselector']);
  if (in_array($pia_skin_selector, $pia_installed_skins)) {
    foreach ($pia_installed_skins as $file) {
      unlink ($pia_skin_set_dir.'/setting_'.$file);
    }
    foreach ($pia_installed_skins as $file) {
      if (file_exists($pia_skin_set_dir.'/setting_'.$file)) {
          $pia_skin_error = True;
          break;
      } else {
          $pia_skin_error = False;
      }
    }
    if ($pia_skin_error == False) {
      $testskin = fopen($pia_skin_set_dir.'setting_'.$pia_skin_selector, 'w');
      $pia_skin_test = '';
      echo("<meta http-equiv='refresh' content='1'>"); 
    } else {
      $pia_skin_test = '';
      echo("<meta http-equiv='refresh' content='1'>");
    }    
  }
}

// Language selector -----------------------------------------------------------------

?>

      <div class="row">
          <div class="col-md-12">
          <div class="box" id="Maintain-Status">
              <div class="box-header with-border">
                <h3 class="box-title">Status</h3>
              </div>
              <div class="box-body" style="padding-bottom: 5px;">
                <div class="db_info_table">
                    <div class="db_info_table_row">
                        <div class="db_info_table_cell" style="min-width: 140px"><?php echo lang('Maintenance_database_path');?></div>
                        <div class="db_info_table_cell">
                            <?php echo $pia_db;?>
                        </div>
                    </div>
                    <div class="db_info_table_row">
                        <div class="db_info_table_cell"><?php echo lang('Maintenance_database_size');?></div>
                        <div class="db_info_table_cell">
                            <?php echo $pia_db_size;?>
                        </div>
                    </div>
                    <div class="db_info_table_row">
                        <div class="db_info_table_cell"><?php echo lang('Maintenance_database_lastmod');?></div>
                        <div class="db_info_table_cell">
                            <?php echo $pia_db_mod;?>
                        </div>
                    </div>
                    <div class="db_info_table_row">
                        <div class="db_info_table_cell"><?php echo lang('Maintenance_database_backup');?></div>
                        <div class="db_info_table_cell">
                            <?php echo $Pia_Archive_count.' '.lang('Maintenance_database_backup_found').' / '.lang('Maintenance_database_backup_total').': '.$Pia_Archive_diskusage;?>
                        </div>
                    </div>
                    <div class="db_info_table_row">
                        <div class="db_info_table_cell"><?php echo lang('Maintenance_arp_status');?></div>
                        <div class="db_info_table_cell">
                            <?php echo $pia_arpscans_result;?></div>
                    </div>
                </div>                
              </div>
              <!-- /.box-body -->
            </div>
          </div>
      </div>

    <div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active">
          <a id="tab_Settings_id" href="#tab_Settings" data-toggle="tab"><?php echo lang('Maintenance_Tools_Tab_Settings');?></a>
        </li>
        <li>
          <a id="tab_DBTools_id" href="#tab_DBTools" data-toggle="tab"><?php echo lang('Maintenance_Tools_Tab_Tools');?></a>
        </li>
        <li>
          <a id="tab_BackupRestore_id" href="#tab_BackupRestore" data-toggle="tab"><?php echo lang('Maintenance_Tools_Tab_BackupRestore');?></a>
        </li>
        <li>
          <a id="tab_Logging_id" href="#tab_Logging" data-toggle="tab"><?php echo lang('Maintenance_Tools_Tab_Logging');?></a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="tab_Settings">
                <div class="db_info_table">
                    <div class="db_info_table_row">
                        <div class="db_tools_table_cell_a" style="text-align:center;">
                            <form method="post" action="maintenance.php">
                              <div style="display: inline-block;">
                                  <select name="langselector" id="langselector" class="form-control bg-green"  style="width:160px; margin-bottom:5px;">
                                      <option value=""><?php echo lang('Maintenance_lang_selector_empty');?></option>
                                      <option value="en_us"><?php echo lang('Maintenance_lang_en_us');?></option>
                                      <option value="de_de"><?php echo lang('Maintenance_lang_de_de');?></option>
                                      <option value="es_es"><?php echo lang('Maintenance_lang_es_es');?></option>
                                  </select>
                              </div>                            
                            </form>
                        </div>
                        <div class="db_info_table_cell" style="padding: 10px; height:40px; text-align:left; vertical-align: middle;">
                            <?php echo lang('Maintenance_lang_selector_text');?>
                        </div>                    
                    </div>
                    <div class="db_info_table_row">
                        <div class="db_tools_table_cell_a" style="text-align: center;">
                            <form method="post" action="maintenance.php">
                            <div style="display: inline-block; text-align: center;">
                                <select name="skinselector" class="form-control bg-green" style="width:160px; margin-bottom:5px;">
                                    <option value=""><?php echo lang('Maintenance_themeselector_empty');?></option>
                                    <option value="skin-black-light">black light</option>
                                    <option value="skin-black">black</option>
                                    <option value="skin-blue-light">blue light</option>
                                    <option value="skin-blue">blue</option>
                                    <option value="skin-green-light">green light</option>
                                    <option value="skin-green">green</option>
                                    <option value="skin-purple-light">purple light</option>
                                    <option value="skin-purple">purple</option>
                                    <option value="skin-red-light">red light</option>
                                    <option value="skin-red">red</option>
                                    <option value="skin-yellow-light">yellow light</option>
                                    <option value="skin-yellow">yellow</option>
                                </select></div>
                            <div style="display: block;"><input type="submit" name="skinselector_set" value="<?php echo lang('Maintenance_themeselector_apply');?>" class="btn bg-green" style="width:160px;">
                                <?php // echo $pia_skin_test; ?>
                            </div>
                            </form>
                        </div>
                        <div class="db_info_table_cell" style="padding: 10px; height:40px; text-align:left; vertical-align: middle;">
                            <?php echo lang('Maintenance_themeselector_text'); ?>
                        </div>    
                    </div>
                    <div class="db_info_table_row">
                        <div class="db_tools_table_cell_a">
                            <button type="button" class="btn bg-green dbtools-button" id="btnPiaEnableDarkmode" onclick="askPiaEnableDarkmode()"><?php echo lang('Maintenance_Tool_darkmode');?></button>
                        </div>
                        <div class="db_tools_table_cell_b"><?php echo lang('Maintenance_Tool_darkmode_text');?></div>
                    </div>
                    <div class="db_info_table_row">
                        <div class="db_tools_table_cell_a">
                            <button type="button" class="btn bg-yellow dbtools-button" id="btnPiaToggleArpScan" onclick="askPiaToggleArpScan()"><?php echo lang('Maintenance_Tool_arpscansw');?></button>
                        </div>
                        <div class="db_tools_table_cell_b"><?php echo lang('Maintenance_Tool_arpscansw_text');?></div>
                    </div>
                </div>
        </div>
        <div class="tab-pane" id="tab_DBTools">
                <div class="db_info_table">
                    <div class="db_info_table_row">
                        <div class="db_tools_table_cell_a" style="">
                            <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnDeleteMAC" onclick="askDeleteDevicesWithEmptyMACs()"><?php echo lang('Maintenance_Tool_del_empty_macs');?></button>
                        </div>
                        <div class="db_tools_table_cell_b"><?php echo lang('Maintenance_Tool_del_empty_macs_text');?></div>
                    </div>
                    <div class="db_info_table_row">
                        <div class="db_tools_table_cell_a" style="">
                            <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnDeleteMAC" onclick="askDeleteAllDevices()"><?php echo lang('Maintenance_Tool_del_alldev');?></button>
                        </div>
                        <div class="db_tools_table_cell_b"><?php echo lang('Maintenance_Tool_del_alldev_text');?></div>
                    </div>
                    <div class="db_info_table_row">
                        <div class="db_tools_table_cell_a" style="">
                            <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnDeleteUnknown" onclick="askDeleteUnknown()"><?php echo lang('Maintenance_Tool_del_unknowndev');?></button>
                        </div>
                        <div class="db_tools_table_cell_b"><?php echo lang('Maintenance_Tool_del_unknowndev_text');?></div>
                    </div>
                    <div class="db_info_table_row">
                        <div class="db_tools_table_cell_a" style="">
                            <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnDeleteEvents" onclick="askDeleteEvents()"><?php echo lang('Maintenance_Tool_del_allevents');?></button>
                        </div>
                        <div class="db_tools_table_cell_b"><?php echo lang('Maintenance_Tool_del_allevents_text');?></div>
                    </div>
                    <div class="db_info_table_row">
                        <div class="db_tools_table_cell_a" style="">
                            <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnDeleteEvents30" onclick="askDeleteEvents30()"><?php echo lang('Maintenance_Tool_del_allevents30');?></button>
                        </div>
                        <div class="db_tools_table_cell_b"><?php echo lang('Maintenance_Tool_del_allevents30_text');?></div>
                    </div>
                    <div class="db_info_table_row">
                        <div class="db_tools_table_cell_a" style="">
                            <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnDeleteActHistory" onclick="askDeleteActHistory()"><?php echo lang('Maintenance_Tool_del_ActHistory');?></button>
                        </div>
                        <div class="db_tools_table_cell_b"><?php echo lang('Maintenance_Tool_del_ActHistory_text');?></div>
                    </div>
                </div>
        </div>
        <div class="tab-pane" id="tab_BackupRestore">
                <div class="db_info_table">
                    <div class="db_info_table_row">
                        <div class="db_tools_table_cell_a" style="">
                            <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnPiaBackupDBtoArchive" onclick="askPiaBackupDBtoArchive()"><?php echo lang('Maintenance_Tool_backup');?></button>
                        </div>
                        <div class="db_tools_table_cell_b"><?php echo lang('Maintenance_Tool_backup_text');?></div>
                    </div>
                    <div class="db_info_table_row">
                        <div class="db_tools_table_cell_a" style="">
                            <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnPiaRestoreDBfromArchive" onclick="askPiaRestoreDBfromArchive()"><?php echo lang('Maintenance_Tool_restore');?><br><?php echo $latestbackup_date;?></button>
                        </div>
                        <div class="db_tools_table_cell_b"><?php echo lang('Maintenance_Tool_restore_text');?></div>
                    </div>
                    <div class="db_info_table_row">
                        <div class="db_tools_table_cell_a" style="">
                            <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnPiaPurgeDBBackups" onclick="askPiaPurgeDBBackups()"><?php echo lang('Maintenance_Tool_purgebackup');?></button>
                        </div>
                        <div class="db_tools_table_cell_b"><?php echo lang('Maintenance_Tool_purgebackup_text');?></div>
                    </div>
                    <div class="db_info_table_row">
                        <div class="db_tools_table_cell_a" style="">
                            <button type="button" class="btn btn-default pa-btn bg-green dbtools-button" id="btnExportCSV" onclick="askExportCSV()"><?php echo lang('Maintenance_Tool_ExportCSV');?></button>
                        </div>
                        <div class="db_tools_table_cell_b"><?php echo lang('Maintenance_Tool_ExportCSV_text');?></div>
                    </div>
                    <div class="db_info_table_row">
                        <div class="db_tools_table_cell_a" style="">
                            <button type="button" class="btn btn-default pa-btn pa-btn-delete bg-red dbtools-button" id="btnImportCSV" onclick="askImportCSV()"><?php echo lang('Maintenance_Tool_ImportCSV');?></button>
                        </div>
                        <div class="db_tools_table_cell_b"><?php echo lang('Maintenance_Tool_ImportCSV_text');?></div>
                    </div>
                 </div>
        </div>
        <!-- ---------------------------Logging-------------------------------------------- -->
        <div class="tab-pane" id="tab_Logging">
                    <div class="db_info_table">
                        <div class="db_info_table_row">
                            <div class="db_tools_table_cell_a" style="">
                            <div><label>pialert.log</label><span class="span-padding"><a href="./log/pialert.log"><i class="fa fa-download"></i> </a></span></div>
                            <div><button class="btn btn-primary" onclick="logManage('pialert.log','cleanLog')"><?php echo lang('Gen_Purge');?></button></div>
                            </div>
                            <div class="db_tools_table_cell_b">
                              <textarea id="pialert_log" class="logs" cols="70" rows="10" readonly ><?php echo file_get_contents( "./log/pialert.log" ); ?>
                              </textarea>
                            </div>
                        </div>   
                        <div class="db_info_table_row">
                            <div class="db_tools_table_cell_a" style="">                            
                            <div><label>pialert_front.log</label><span class="span-padding"><a href="./log/pialert_front.log"><i class="fa fa-download"></i> </a></span></div>
                            <div><button class="btn btn-primary" onclick="logManage('pialert_front.log','cleanLog')"><?php echo lang('Gen_Purge');?></button></div>
                            </div>
                            <div class="db_tools_table_cell_b">
                              <textarea id="pialert_front_log" class="logs" cols="70" rows="10" wrap='off' readonly><?php echo file_get_contents( "./log/pialert_front.log" ); ?>
                              </textarea>
                            </div>
                        </div>   
                        <div class="db_info_table_row">
                            <div class="db_tools_table_cell_a" style="">                            
                            <div><label>IP_changes.log</label><span class="span-padding"><a href="./log/IP_changes.log"><i class="fa fa-download"></i> </a></span></div>
                            <div><button class="btn btn-primary" onclick="logManage('IP_changes.log','cleanLog')"><?php echo lang('Gen_Purge');?></button></div>
                            </div>
                            <div class="db_tools_table_cell_b">
                              <textarea id="IP_changes_log" class="logs logs-small" cols="70" rows="10" readonly><?php echo file_get_contents( "./log/IP_changes.log" ); ?>
                              </textarea>                              
                            </div>
                        </div> 
                        <div class="db_info_table_row">
                            <div class="db_tools_table_cell_a" style="">                            
                            <div><label>stdout.log</label><span class="span-padding"><a href="./log/stdout.log"><i class="fa fa-download"></i> </a></span></div>
                            <div><button class="btn btn-primary" onclick="logManage('stdout.log','cleanLog')"><?php echo lang('Gen_Purge');?></button></div>
                            </div>
                            <div class="db_tools_table_cell_b">
                              <textarea id="stdout_log" class="logs logs-small" cols="70" rows="10" wrap='off' readonly><?php echo file_get_contents( "./log/stdout.log" ); ?>
                              </textarea>
                            </div>
                        </div> 
                        <div class="db_info_table_row">
                            <div class="db_tools_table_cell_a" style="">
                            <div><label>stderr.log</label><span class="span-padding"><a href="./log/stderr.log"><i class="fa fa-download"></i> </a></span></div>
                            <div><button class="btn btn-primary" onclick="logManage('stderr.log','cleanLog')"><?php echo lang('Gen_Purge');?></button></div>                            
                            </div>
                            <div class="db_tools_table_cell_b">
                              <textarea id="stderr_log" class="logs logs-small" cols="70" rows="10" wrap='off' readonly><?php echo file_get_contents( "./log/stderr.log" ); ?>
                              </textarea>
                            </div>
                        </div>      
                                
                    </div>
              </div>
          </div>
          <!-- ------------------------------------------------------------------------------ -->
      </div>

      <div class="box-body" style="text-align: center;">
        <h5 class="text-aqua" style="font-size: 16px;">
          <span id="lastCommit">
           
          </span>
          <span id="lastDockerUpdate">
           
          </span>          
      </h5>
  </div>
      
      
</div>





<div style="width: 100%; height: 20px;"></div>
    <!-- ----------------------------------------------------------------------- -->

</section>

    <!-- /.content -->
    <?php
      require 'php/templates/footer.php';
    ?>
  </div>
  <!-- /.content-wrapper -->

<!-- ----------------------------------------------------------------------- -->


<script>

var emptyArr = ['undefined', "", undefined, null];
var selectedTab                 = 'tab_Settings_id';

initializeTabs();

// delete devices with emty macs
function askDeleteDevicesWithEmptyMACs () {
  // Ask 
  showModalWarning('<?php echo lang('Maintenance_Tool_del_empty_macs_noti');?>', '<?php echo lang('Maintenance_Tool_del_empty_macs_noti_text');?>',
    'Cancel', 'Delete', 'deleteDevicesWithEmptyMACs');
}
function deleteDevicesWithEmptyMACs()
{ 
  // Delete device
  $.get('php/server/devices.php?action=deleteAllWithEmptyMACs', function(msg) {
    showMessage (msg);
  });
}

// delete all devices 
function askDeleteAllDevices () {
  // Ask 
  showModalWarning('<?php echo lang('Maintenance_Tool_del_alldev_noti');?>', '<?php echo lang('Maintenance_Tool_del_alldev_noti_text');?>',
    '<?php echo lang('Gen_Cancel');?>', '<?php echo lang('Gen_Delete');?>', 'deleteAllDevices');
}
function deleteAllDevices()
{ 
  // Delete device
  $.get('php/server/devices.php?action=deleteAllDevices', function(msg) {
    showMessage (msg);
  });
}

// delete all (unknown) devices 
function askDeleteUnknown () {
  // Ask 
  showModalWarning('<?php echo lang('Maintenance_Tool_del_unknowndev_noti');?>', '<?php echo lang('Maintenance_Tool_del_unknowndev_noti_text');?>',
    '<?php echo lang('Gen_Cancel');?>', '<?php echo lang('Gen_Delete');?>', 'deleteUnknownDevices');
}
function deleteUnknownDevices()
{ 
  // Execute
  $.get('php/server/devices.php?action=deleteUnknownDevices', function(msg) {
    showMessage (msg);
  });
}

// delete all Events 
function askDeleteEvents () {
  // Ask 
  showModalWarning('<?php echo lang('Maintenance_Tool_del_allevents_noti');?>', '<?php echo lang('Maintenance_Tool_del_allevents_noti_text');?>',
    '<?php echo lang('Gen_Cancel');?>', '<?php echo lang('Gen_Delete');?>', 'deleteEvents');
}
function deleteEvents()
{ 
  // Execute
  $.get('php/server/devices.php?action=deleteEvents', function(msg) {
    showMessage (msg);
  });
}

// delete all Events older than 30 days
function askDeleteEvents30 () {
  // Ask 
  showModalWarning('<?php echo lang('Maintenance_Tool_del_allevents30_noti');?>', '<?php echo lang('Maintenance_Tool_del_allevents30_noti_text');?>',
    '<?php echo lang('Gen_Cancel');?>', '<?php echo lang('Gen_Delete');?>', 'deleteEvents30');
}
function deleteEvents30()
{ 
  // Execute
  $.get('php/server/devices.php?action=deleteEvents30', function(msg) {
    showMessage (msg);
  });
}

// delete History 
function askDeleteActHistory () {
  // Ask 
  showModalWarning('<?php echo lang('Maintenance_Tool_del_ActHistory_noti');?>', '<?php echo lang('Maintenance_Tool_del_ActHistory_noti_text');?>',
    '<?php echo lang('Gen_Cancel');?>', '<?php echo lang('Gen_Delete');?>', 'deleteActHistory');
}
function deleteActHistory()
{ 
  // Execute
  $.get('php/server/devices.php?action=deleteActHistory', function(msg) {
    showMessage (msg);
  });
}

// Backup DB to Archive 
function askPiaBackupDBtoArchive () {
  // Ask 
  showModalWarning('<?php echo lang('Maintenance_Tool_backup_noti');?>', '<?php echo lang('Maintenance_Tool_backup_noti_text');?>',
    '<?php echo lang('Gen_Cancel');?>', '<?php echo lang('Gen_Backup');?>', 'PiaBackupDBtoArchive');
}
function PiaBackupDBtoArchive()
{ 
  // Execute
  $.get('php/server/devices.php?action=PiaBackupDBtoArchive', function(msg) {
    showMessage (msg);
  });
}

// Restore DB from Archive 
function askPiaRestoreDBfromArchive () {
  // Ask 
  showModalWarning('<?php echo lang('Maintenance_Tool_restore_noti');?>', '<?php echo lang('Maintenance_Tool_restore_noti_text');?>',
    '<?php echo lang('Gen_Cancel');?>', '<?php echo lang('Gen_Restore');?>', 'PiaRestoreDBfromArchive');
}
function PiaRestoreDBfromArchive()
{ 
  // Execute
  $.get('php/server/devices.php?action=PiaRestoreDBfromArchive', function(msg) {
    showMessage (msg);
  });
}

// Purge Backups 
function askPiaPurgeDBBackups() {
  // Ask 
  showModalWarning('<?php echo lang('Maintenance_Tool_purgebackup_noti');?>', '<?php echo lang('Maintenance_Tool_purgebackup_noti_text');?>',
    '<?php echo lang('Gen_Cancel');?>', '<?php echo lang('Gen_Purge');?>', 'PiaPurgeDBBackups');
}
function PiaPurgeDBBackups()
{ 
  // Execute
  $.get('php/server/devices.php?action=PiaPurgeDBBackups', function(msg) {
    showMessage (msg);
  });
}

// Export CSV
function askExportCSV() {
  // Ask 
  showModalWarning('<?php echo lang('Maintenance_Tool_ExportCSV_noti');?>', '<?php echo lang('Maintenance_Tool_ExportCSV_noti_text');?>',
    '<?php echo lang('Gen_Cancel');?>', '<?php echo lang('Gen_Okay');?>', 'ExportCSV');
}
function ExportCSV()
{ 
  // Execute
  openInNewTab(window.location.origin + "/php/server/devices.php?action=ExportCSV")
}

// Import CSV
function askImportCSV() {
  // Ask 
  showModalWarning('<?php echo lang('Maintenance_Tool_ImportCSV_noti');?>', '<?php echo lang('Maintenance_Tool_ImportCSV_noti_text');?>',
    '<?php echo lang('Gen_Cancel');?>', '<?php echo lang('Gen_Okay');?>', 'ImportCSV');
}
function ImportCSV()
{   
  // Execute
  $.get('/php/server/devices.php?action=ImportCSV', function(msg) {
    showMessage (msg);
  });
}

// Switch Darkmode 
function askPiaEnableDarkmode() {
  // Ask 
  showModalWarning('<?php echo lang('Maintenance_Tool_darkmode_noti');?>', '<?php echo lang('Maintenance_Tool_darkmode_noti_text');?>',
    '<?php echo lang('Gen_Cancel');?>', '<?php echo lang('Gen_Switch');?>', 'PiaEnableDarkmode');
}
function PiaEnableDarkmode()
{ 
  // Execute
  $.get('php/server/devices.php?action=PiaEnableDarkmode', function(msg) {
    showMessage (msg);
  });
}

// Toggle the Arp-Scans 
function askPiaToggleArpScan () {
  // Ask 
  showModalWarning('<?php echo lang('Maintenance_Tool_arpscansw_noti');?>', '<?php echo lang('Maintenance_Tool_arpscansw_noti_text');?>',
    '<?php echo lang('Gen_Cancel');?>', '<?php echo lang('Gen_Switch');?>', 'PiaToggleArpScan');
}
function PiaToggleArpScan()
{ 
  // Execute
  $.get('php/server/devices.php?action=PiaToggleArpScan', function(msg) {
    showMessage (msg);
  });
}


// Clean log file 
var targetLogFile = "";
var logFileAction = "";

function logManage(callback) {
  targetLogFile = arguments[0];  // target
  logFileAction = arguments[1];  // action
  // Ask 
  showModalWarning('<?php echo lang('Gen_Purge');?>' + ' ' + arguments[1], '<?php echo lang('Gen_AreYouSure');?>',
    '<?php echo lang('Gen_Cancel');?>', '<?php echo lang('Gen_Okay');?>', "performLogManage");
}

function performLogManage() { 
  // Execute
  console.log("targetLogFile:" + targetLogFile)
  console.log("logFileAction:" + logFileAction)
  
  $.ajax({
    method: "POST",
    url: "php/server/util.php",
    data: { function: logFileAction, settings: targetLogFile  },
    success: function(data, textStatus) {
        showModalOk ('Result', data );
    }
  })
  }

function scrollDown()
{
  var tempArea = $('#pialert_log');
  $(tempArea[0]).scrollTop(tempArea[0].scrollHeight);

  tempArea = $('#pialert_front_log');
  $(tempArea[0]).scrollTop(tempArea[0].scrollHeight);

  tempArea = $('#IP_changes_log');
  $(tempArea[0]).scrollTop(tempArea[0].scrollHeight);

  tempArea = $('#stdout_log');
  $(tempArea[0]).scrollTop(tempArea[0].scrollHeight);

  tempArea = $('#stderr_log');
  $(tempArea[0]).scrollTop(tempArea[0].scrollHeight);

}

function initializeTabs () {  
  // Activate panel
  if(!emptyArr.includes(getCache("activeMaintenanceTab")))
  {
    selectedTab = getCache("activeMaintenanceTab");
  }
  $('.nav-tabs a[id='+ selectedTab +']').tab('show');

  // When changed save new current tab
  $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    setCache("activeMaintenanceTab", $(e.target).attr('id'))
  });

  // events on tab change
  $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    var target = $(e.target).attr("href") // activated tab
    //alert(target);
    if(target == "#tab_Logging")
    {
      scrollDown();
    }
  });
}

// save language in a cookie
$('#langselector').on('change', function (e) {
    var optionSelected = $("option:selected", this);
    var valueSelected = this.value;    
    setCookie("language",valueSelected )
    location.reload();
  });


// load footer asynchronously not to block the page load/other sections
window.onload = function asyncFooter()
{
  scrollDown();

  $("#lastCommit").append('<a href="https://github.com/jokob-sk/Pi.Alert/commits" target="_blank"><img  alt="GitHub last commit" src="https://img.shields.io/github/last-commit/jokob-sk/pi.alert/main?logo=github"></a>');

  $("#lastDockerUpdate").append(
    '<a href="https://hub.docker.com/r/jokobsk/pi.alert/tags" target="_blank"><img alt="Docker last pushed" src="https://img.shields.io/badge/dynamic/json?color=blue&label=Last%20pushed&query=last_updated&url=https%3A%2F%2Fhub.docker.com%2Fv2%2Frepositories%2Fjokobsk%2Fpi.alert%2F&logo=docker&?link=http://left&link=https://hub.docker.com/repository/docker/jokobsk/pi.alert"></a>');

}

</script>


