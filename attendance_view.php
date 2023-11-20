<?php


	$currDir=dirname(__FILE__);
	include("$currDir/defaultLang.php");
	include("$currDir/language.php");
	include("$currDir/lib.php");
	@include("$currDir/hooks/attendance.php");
	include("$currDir/attendance_dml.php");

	// mm: can the current member access this page?
	$perm=getTablePermissions('attendance');
	if(!$perm[0]){
		echo error_message($Translation['tableAccessDenied'], false);
		echo '<script>setTimeout("window.location=\'index.php?signOut=1\'", 2000);</script>';
		exit;
	}

	$x = new DataList;
	$x->TableName = "attendance";

	// Fields that can be displayed in the table view
	$x->QueryFieldsTV = array(   
		"IF(    CHAR_LENGTH(`students1`.`name`), CONCAT_WS('',   `students1`.`name`), '') /* Student */" => "student",
		"IF(    CHAR_LENGTH(`students1`.`regno`), CONCAT_WS('',   `students1`.`regno`), '') /* Regno */" => "regno",
		"`attendance`.`week`" => "week",
		"if(`attendance`.`date`,date_format(`attendance`.`date`,'%m/%d/%Y'),'')" => "date",
		"IF(    CHAR_LENGTH(`units1`.`name`), CONCAT_WS('',   `units1`.`name`), '') /* Unit */" => "unit",
		"concat('<img src=\"', if(`attendance`.`attended`, 'checked.gif', 'checkednot.gif'), '\" border=\"0\" />')" => "attended",
		"`attendance`.`id`" => "id"
	);
	// mapping incoming sort by requests to actual query fields
	$x->SortFields = array(   
		1 => '`students1`.`name`',
		2 => '`students1`.`regno`',
		3 => 3,
		4 => '`attendance`.`date`',
		5 => '`units1`.`name`',
		6 => 6,
		7 => '`attendance`.`id`'
	);

	// Fields that can be displayed in the csv file
	$x->QueryFieldsCSV = array(   
		"IF(    CHAR_LENGTH(`students1`.`name`), CONCAT_WS('',   `students1`.`name`), '') /* Student */" => "student",
		"IF(    CHAR_LENGTH(`students1`.`regno`), CONCAT_WS('',   `students1`.`regno`), '') /* Regno */" => "regno",
		"`attendance`.`week`" => "week",
		"if(`attendance`.`date`,date_format(`attendance`.`date`,'%m/%d/%Y'),'')" => "date",
		"IF(    CHAR_LENGTH(`units1`.`name`), CONCAT_WS('',   `units1`.`name`), '') /* Unit */" => "unit",
		"`attendance`.`attended`" => "attended",
		"`attendance`.`id`" => "id"
	);
	// Fields that can be filtered
	$x->QueryFieldsFilters = array(   
		"IF(    CHAR_LENGTH(`students1`.`name`), CONCAT_WS('',   `students1`.`name`), '') /* Student */" => "Student",
		"IF(    CHAR_LENGTH(`students1`.`regno`), CONCAT_WS('',   `students1`.`regno`), '') /* Regno */" => "Regno",
		"`attendance`.`week`" => "Week",
		"`attendance`.`date`" => "Date",
		"IF(    CHAR_LENGTH(`units1`.`name`), CONCAT_WS('',   `units1`.`name`), '') /* Unit */" => "Unit",
		"`attendance`.`attended`" => "Attended",
		"`attendance`.`id`" => "ID"
	);

	// Fields that can be quick searched
	$x->QueryFieldsQS = array(   
		"IF(    CHAR_LENGTH(`students1`.`name`), CONCAT_WS('',   `students1`.`name`), '') /* Student */" => "student",
		"IF(    CHAR_LENGTH(`students1`.`regno`), CONCAT_WS('',   `students1`.`regno`), '') /* Regno */" => "regno",
		"`attendance`.`week`" => "week",
		"if(`attendance`.`date`,date_format(`attendance`.`date`,'%m/%d/%Y'),'')" => "date",
		"IF(    CHAR_LENGTH(`units1`.`name`), CONCAT_WS('',   `units1`.`name`), '') /* Unit */" => "unit",
		"concat('<img src=\"', if(`attendance`.`attended`, 'checked.gif', 'checkednot.gif'), '\" border=\"0\" />')" => "attended",
		"`attendance`.`id`" => "id"
	);

	// Lookup fields that can be used as filterers
	$x->filterers = array(  'student' => 'Student', 'unit' => 'Unit');

	$x->QueryFrom = "`attendance` LEFT JOIN `students` as students1 ON `students1`.`regno`=`attendance`.`student` LEFT JOIN `units` as units1 ON `units1`.`id`=`attendance`.`unit` ";
	$x->QueryWhere = '';
	$x->QueryOrder = '';

	$x->AllowSelection = 1;
	$x->HideTableView = ($perm[2]==0 ? 1 : 0);
	$x->AllowDelete = $perm[4];
	$x->AllowMassDelete = false;
	$x->AllowInsert = $perm[1];
	$x->AllowUpdate = $perm[3];
	$x->SeparateDV = 1;
	$x->AllowDeleteOfParents = 0;
	$x->AllowFilters = 1;
	$x->AllowSavingFilters = 0;
	$x->AllowSorting = 1;
	$x->AllowNavigation = 1;
	$x->AllowPrinting = 1;
	$x->AllowCSV = 1;
	$x->RecordsPerPage = 10;
	$x->QuickSearch = 1;
	$x->QuickSearchText = $Translation["quick search"];
	$x->ScriptFileName = "attendance_view.php";
	$x->RedirectAfterInsert = "attendance_view.php?SelectedID=#ID#";
	$x->TableTitle = "Attendance Record";
	$x->TableIcon = "resources/table_icons/application_view_icons.png";
	$x->PrimaryKey = "`attendance`.`id`";

	$x->ColWidth   = array(  150, 150, 150, 150, 150, 150);
	$x->ColCaption = array("Student", "Regno", "Week", "Date", "Unit", "Attended");
	$x->ColFieldName = array('student', 'regno', 'week', 'date', 'unit', 'attended');
	$x->ColNumber  = array(1, 2, 3, 4, 5, 6);

	// template paths below are based on the app main directory
	$x->Template = 'templates/attendance_templateTV.html';
	$x->SelectedTemplate = 'templates/attendance_templateTVS.html';
	$x->TemplateDV = 'templates/attendance_templateDV.html';
	$x->TemplateDVP = 'templates/attendance_templateDVP.html';

	$x->ShowTableHeader = 1;
	$x->ShowRecordSlots = 0;
	$x->TVClasses = "";
	$x->DVClasses = "";
	$x->HighlightColor = '#FFF0C2';

	// mm: build the query based on current member's permissions
	$DisplayRecords = $_REQUEST['DisplayRecords'];
	if(!in_array($DisplayRecords, array('user', 'group'))){ $DisplayRecords = 'all'; }
	if($perm[2]==1 || ($perm[2]>1 && $DisplayRecords=='user' && !$_REQUEST['NoFilter_x'])){ // view owner only
		$x->QueryFrom.=', membership_userrecords';
		$x->QueryWhere="where `attendance`.`id`=membership_userrecords.pkValue and membership_userrecords.tableName='attendance' and lcase(membership_userrecords.memberID)='".getLoggedMemberID()."'";
	}elseif($perm[2]==2 || ($perm[2]>2 && $DisplayRecords=='group' && !$_REQUEST['NoFilter_x'])){ // view group only
		$x->QueryFrom.=', membership_userrecords';
		$x->QueryWhere="where `attendance`.`id`=membership_userrecords.pkValue and membership_userrecords.tableName='attendance' and membership_userrecords.groupID='".getLoggedGroupID()."'";
	}elseif($perm[2]==3){ // view all
		// no further action
	}elseif($perm[2]==0){ // view none
		$x->QueryFields = array("Not enough permissions" => "NEP");
		$x->QueryFrom = '`attendance`';
		$x->QueryWhere = '';
		$x->DefaultSortField = '';
	}
	// hook: attendance_init
	$render=TRUE;
	if(function_exists('attendance_init')){
		$args=array();
		$render=attendance_init($x, getMemberInfo(), $args);
	}

	if($render) $x->Render();

	// hook: attendance_header
	$headerCode='';
	if(function_exists('attendance_header')){
		$args=array();
		$headerCode=attendance_header($x->ContentType, getMemberInfo(), $args);
	}  
	if(!$headerCode){
		include_once("$currDir/header.php"); 
	}else{
		ob_start(); include_once("$currDir/header.php"); $dHeader=ob_get_contents(); ob_end_clean();
		echo str_replace('<%%HEADER%%>', $dHeader, $headerCode);
	}

	echo $x->HTML;
	// hook: attendance_footer
	$footerCode='';
	if(function_exists('attendance_footer')){
		$args=array();
		$footerCode=attendance_footer($x->ContentType, getMemberInfo(), $args);
	}  
	if(!$footerCode){
		include_once("$currDir/footer.php"); 
	}else{
		ob_start(); include_once("$currDir/footer.php"); $dFooter=ob_get_contents(); ob_end_clean();
		echo str_replace('<%%FOOTER%%>', $dFooter, $footerCode);
	}
?>