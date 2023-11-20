<?php


	$currDir=dirname(__FILE__);
	include("$currDir/defaultLang.php");
	include("$currDir/language.php");
	include("$currDir/lib.php");
	@include("$currDir/hooks/students.php");
	include("$currDir/students_dml.php");

	// mm: can the current member access this page?
	$perm=getTablePermissions('students');
	if(!$perm[0]){
		echo error_message($Translation['tableAccessDenied'], false);
		echo '<script>setTimeout("window.location=\'index.php?signOut=1\'", 2000);</script>';
		exit;
	}

	$x = new DataList;
	$x->TableName = "students";

	// Fields that can be displayed in the table view
	$x->QueryFieldsTV = array(   
		"`students`.`regno`" => "regno",
		"`students`.`name`" => "name",
		"IF(    CHAR_LENGTH(`courses1`.`name`), CONCAT_WS('',   `courses1`.`name`), '') /* Course */" => "course"
	);
	// mapping incoming sort by requests to actual query fields
	$x->SortFields = array(   
		1 => 1,
		2 => 2,
		3 => '`courses1`.`name`'
	);

	// Fields that can be displayed in the csv file
	$x->QueryFieldsCSV = array(   
		"`students`.`regno`" => "regno",
		"`students`.`name`" => "name",
		"IF(    CHAR_LENGTH(`courses1`.`name`), CONCAT_WS('',   `courses1`.`name`), '') /* Course */" => "course"
	);
	// Fields that can be filtered
	$x->QueryFieldsFilters = array(   
		"`students`.`regno`" => "Regno",
		"`students`.`name`" => "Name",
		"IF(    CHAR_LENGTH(`courses1`.`name`), CONCAT_WS('',   `courses1`.`name`), '') /* Course */" => "Course"
	);

	// Fields that can be quick searched
	$x->QueryFieldsQS = array(   
		"`students`.`regno`" => "regno",
		"`students`.`name`" => "name",
		"IF(    CHAR_LENGTH(`courses1`.`name`), CONCAT_WS('',   `courses1`.`name`), '') /* Course */" => "course"
	);

	// Lookup fields that can be used as filterers
	$x->filterers = array(  'course' => 'Course');

	$x->QueryFrom = "`students` LEFT JOIN `courses` as courses1 ON `courses1`.`id`=`students`.`course` ";
	$x->QueryWhere = '';
	$x->QueryOrder = '';

	$x->AllowSelection = 1;
	$x->HideTableView = ($perm[2]==0 ? 1 : 0);
	$x->AllowDelete = $perm[4];
	$x->AllowMassDelete = true;
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
	$x->ScriptFileName = "students_view.php";
	$x->RedirectAfterInsert = "students_view.php?SelectedID=#ID#";
	$x->TableTitle = "Students";
	$x->TableIcon = "resources/table_icons/group.png";
	$x->PrimaryKey = "`students`.`regno`";

	$x->ColWidth   = array(  150, 150, 150);
	$x->ColCaption = array("Regno", "Name", "Course");
	$x->ColFieldName = array('regno', 'name', 'course');
	$x->ColNumber  = array(1, 2, 3);

	// template paths below are based on the app main directory
	$x->Template = 'templates/students_templateTV.html';
	$x->SelectedTemplate = 'templates/students_templateTVS.html';
	$x->TemplateDV = 'templates/students_templateDV.html';
	$x->TemplateDVP = 'templates/students_templateDVP.html';

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
		$x->QueryWhere="where `students`.`regno`=membership_userrecords.pkValue and membership_userrecords.tableName='students' and lcase(membership_userrecords.memberID)='".getLoggedMemberID()."'";
	}elseif($perm[2]==2 || ($perm[2]>2 && $DisplayRecords=='group' && !$_REQUEST['NoFilter_x'])){ // view group only
		$x->QueryFrom.=', membership_userrecords';
		$x->QueryWhere="where `students`.`regno`=membership_userrecords.pkValue and membership_userrecords.tableName='students' and membership_userrecords.groupID='".getLoggedGroupID()."'";
	}elseif($perm[2]==3){ // view all
		// no further action
	}elseif($perm[2]==0){ // view none
		$x->QueryFields = array("Not enough permissions" => "NEP");
		$x->QueryFrom = '`students`';
		$x->QueryWhere = '';
		$x->DefaultSortField = '';
	}
	// hook: students_init
	$render=TRUE;
	if(function_exists('students_init')){
		$args=array();
		$render=students_init($x, getMemberInfo(), $args);
	}

	if($render) $x->Render();

	// hook: students_header
	$headerCode='';
	if(function_exists('students_header')){
		$args=array();
		$headerCode=students_header($x->ContentType, getMemberInfo(), $args);
	}  
	if(!$headerCode){
		include_once("$currDir/header.php"); 
	}else{
		ob_start(); include_once("$currDir/header.php"); $dHeader=ob_get_contents(); ob_end_clean();
		echo str_replace('<%%HEADER%%>', $dHeader, $headerCode);
	}

	echo $x->HTML;
	// hook: students_footer
	$footerCode='';
	if(function_exists('students_footer')){
		$args=array();
		$footerCode=students_footer($x->ContentType, getMemberInfo(), $args);
	}  
	if(!$footerCode){
		include_once("$currDir/footer.php"); 
	}else{
		ob_start(); include_once("$currDir/footer.php"); $dFooter=ob_get_contents(); ob_end_clean();
		echo str_replace('<%%FOOTER%%>', $dFooter, $footerCode);
	}
?>