<?php
/* For licensing terms, see /license.txt */

/**
 *	This script contains the actual html code to display the "header"
 *	or "banner" on top of every Chamilo page.
 *
 *	@package chamilo.include
 */

require_once api_get_path(LIBRARY_PATH).'banner.lib.php';

$session_id     = api_get_session_id();
$session_name   = api_get_session_name($my_session_id);
?>

<div id="wrapper">

  <ul id="navigation">
<?php 
if (!empty($help)) { 
?>
        <li class="help"><a href="<?php echo api_get_path(WEB_CODE_PATH); ?>help/help.php?open=Home&height=400&width=600" class="thickbox" title="<?php echo get_lang('Help'); ?>"><img src="<?php echo api_get_path(WEB_IMG_PATH);?>help.large.png" alt="<?php echo get_lang('Help');?>" title="<?php echo get_lang('Help');?>" /></a> </li>
<?php 
}
if (api_get_setting('show_link_bug_notification') == 'true') { 
?>
    <li class="report"><a href="http://support.chamilo.org/projects/chamilo-18/wiki/How_to_report_bugs" target="_blank"><img src="<?php echo api_get_path(WEB_IMG_PATH) ?>bug.large.png" style="vertical-align: middle;" alt="<?php echo get_lang('ReportABug') ?>" title="<?php echo get_lang('ReportABug');?>"/></a></li>
<?php
}
/*
        <li class="student">            <a href="<?php echo api_get_path(WEB_CODE_PATH); ?>help/help.php?open=Home&height=400&width=600" class="thickbox" title="<?php echo get_lang('Help'); ?>"></a> </li>
        <li class="user-online">        <a href="<?php echo api_get_path(WEB_CODE_PATH); ?>help/help.php?open=Home&height=400&width=600" class="thickbox" title="<?php echo get_lang('Help'); ?>"></a> </li>
        <li class="user-connect">       <a href="<?php echo api_get_path(WEB_CODE_PATH); ?>help/help.php?open=Home&height=400&width=600" class="thickbox" title="<?php echo get_lang('Help'); ?>"></a> </li>
        <li class="student-connect">    <a href="<?php echo api_get_path(WEB_CODE_PATH); ?>help/help.php?open=Home&height=400&width=600" class="thickbox" title="<?php echo get_lang('Help'); ?>"></a> </li>
<?php } 
*/
?>
  </ul>

<div id="header">
	<div id="header1">
		<div id="top_corner"></div>
		<div id="institution">
			<a href="<?php echo api_get_path(WEB_PATH); ?>index.php" target="_top"><?php echo api_get_setting('siteName'); ?></a>
			<?php
			$iurl  = api_get_setting('InstitutionUrl');
			$iname = api_get_setting('Institution');
			if (!empty($iname)) {
	           echo '-&nbsp;<a href="'.$iurl.'" target="_top">'.$iname.'</a>';
			}
			
			/*	External link section a.k.a Department - Department URL */
			
			if (isset($_course['extLink']) && $_course['extLink']['name'] != '') {
				echo '<span class="extLinkSeparator"> - </span>';
				if ($_course['extLink']['url'] != '') {
					echo '<a class="extLink" href="'.$_course['extLink']['url'].'" target="_top">';
					echo $_course['extLink']['name'];
					echo '</a>';
				} else {
					echo $_course['extLink']['name'];
				}
			}
?>
		</div>
<?php
/*	Course title section */

if (!empty($_cid) and $_cid != -1 and isset($_course)) {
	//Put the name of the course in the header	
?>
 	<div id="my_courses">
<?php
	/* <div id="my_courses"><a href="<?php echo api_get_path(WEB_COURSE_PATH).$_course['path']; ?>/index.php" target="_top">&nbsp; 	
	echo $_course['name'].' ';
	
	if (api_get_setting('display_coursecode_in_courselist') == 'true') {
		echo $_course['official_code'];
	}
	
	if (api_get_setting('use_session_mode') == 'true' && isset($_SESSION['session_name'])) {
		echo '&nbsp;('.$_SESSION['session_name'].')&nbsp;';
	}
	
	if (api_get_setting('display_coursecode_in_courselist') == 'true' && api_get_setting('display_teacher_in_courselist') == 'true') {
		echo ' - ';
	}	
	if (api_get_setting('display_teacher_in_courselist') == 'true') {
		//This is still necessary? There is the course teacher in the footer
		echo stripslashes($_course['titular']);
	}	
	echo '</a>';*/
	echo '</div>';
	
} elseif (isset($nameTools) && $language_file != 'course_home') {
	//Put the name of the user-tools in the header
	if (!isset($_user['user_id'])) {
		echo '<div id="my_courses"></div>';
	} elseif (!$noPHP_SELF) {
		echo '<div id="my_courses"><a href="'.api_get_self().'?'.api_get_cidreq(), '" target="_top">', $nameTools, '</a></div>';
	} else {
		echo '<div id="my_courses">'.$nameTools.'</div>';
	}	
} else {
	echo '<div id="my_courses"></div>';
}

echo '<div id="plugin-header">';
api_plugin('header');
echo '</div>';

//Don't let the header disappear if there's nothing on the left
echo '<div class="clear">&nbsp;</div>';

/*	Plugins for banner section */
$web_course_path = api_get_path(WEB_COURSE_PATH);
?>
	</div>
	<div id="header2">
		<div id="Header2Right">
			<ul>
<?php
if ((api_get_setting('showonline', 'world') == 'true' AND !$_user['user_id']) OR (api_get_setting('showonline', 'users') == 'true' AND $_user['user_id']) OR (api_get_setting('showonline', 'course') == 'true' AND $_user['user_id'] AND $_cid)) {
	$number = who_is_online_count(api_get_setting('time_limit_whosonline'));	
	
	$number_online_in_course = 0;
	if(!empty($_course['id'])) {
		$number_online_in_course = who_is_online_in_this_course_count(api_get_user_id(), api_get_setting('time_limit_whosonline'), $_course['id']);
	}
	
 	echo '<li>';
	// Display the who's online of the platform
	if ((api_get_setting('showonline', 'world') == 'true' AND !$_user['user_id']) OR (api_get_setting('showonline', 'users') == 'true' AND $_user['user_id'])) {
		echo '<a href="'.api_get_path(WEB_PATH).'whoisonline.php" target="_top" title="'.get_lang('UsersOnline').'" ><img width="12px" src="'.api_get_path(WEB_IMG_PATH).'online.png" title="'.get_lang('UsersOnline').'"> '.get_lang('OnLine').' '.$number.'</a>';
	}

	// Display the who's online for the course
	if (is_array($_course) AND api_get_setting('showonline', 'course') == 'true' AND isset($_course['sysCode'])) {
		echo '(<a href="'.api_get_path(WEB_PATH).'whoisonline.php?cidReq='.$_course['sysCode'].'" target="_top">'.$number_online_in_course.' '.get_lang('InThisCourse').'</a>)';
	}
	
	// Display the who's online for the session 
	//if (api_get_setting('use_session_mode') == 'true' && isset($_user['user_id']) && api_is_coach()) {
	
	if (api_get_setting('use_session_mode') == 'true' && isset($_user['user_id']) && api_get_session_id() != 0) {		
	    echo '<li><a href="'.api_get_path(WEB_PATH).'whoisonlinesession.php?id_coach='.$_user['user_id'].'&amp;referer='.urlencode($_SERVER['REQUEST_URI']).'" target="_top">'.get_lang('UsersConnectedToMySessions').'</a></li>';
	}
	
	echo '</li>';
}

if ($_user['user_id'] && isset($_cid)) {
	if ((api_is_course_admin() || api_is_platform_admin()) && api_get_setting('student_view_enabled') == 'true') {
		echo '<li>&nbsp;|&nbsp;';
		api_display_tool_view_option();
		echo '</li>';
	}
}


if (api_is_allowed_to_edit()) {
		if (!empty($help)) {			
		echo '<li>';
		// Show help
		if (api_get_setting('show_link_bug_notification') != 'true') {
			echo '|';	
		}
		//echo get_lang('Help');
                /*	
			<a href="#" onclick="javascript: MyWindow=window.open('<?php echo api_get_path(WEB_CODE_PATH).'help/help.php'; ?>?open=<?php echo $help; ?>','MyWindow','toolbar=no,location=no,directories=no,status=yes,menubar=no,scrollbars=yes,resizable=yes,width=620,height=600,left=200,top=20'); return false;">
			<img src="<?php echo api_get_path(WEB_IMG_PATH); ?>help.png" style="vertical-align: middle;" title="<?php echo get_lang('Help'); ?>" alt="<?php echo get_lang('Help'); ?>"/></a>			
		 */?>
		<?php 
		echo "</li>";		
	}
}
?>
		</ul>
	</div>
		<div class="clear">&nbsp;</div>
	</div>
	<div id="header3">
<?php

/*	User section */

if ($_user['user_id']) {
	$login = '';
	if (api_is_anonymous()) {
		$login = '('.get_lang('Anonymous').')';
	} else {
		$uinfo = api_get_user_info(api_get_user_id());
		$login = '('.$uinfo['username'].')';
	}
	?>
	 <!-- start user section line with name, my course, my profile, scorm info, etc -->

	<ul id="logout">
		<li><a href="<?php echo api_get_path(WEB_PATH); ?>index.php?logout=logout&uid=<?php echo $_user['user_id']; ?>" target="_top"><span><?php echo get_lang('Logout').' '.$login; ?></span></a></li>
	</ul>
<?php
}
?>

<ul>
<?php
$navigation = array();
$possible_tabs = get_tabs();

// Campus Homepage
if (api_get_setting('show_tabs', 'campus_homepage') == 'true') {
	$navigation[SECTION_CAMPUS] = $possible_tabs[SECTION_CAMPUS];
} else {
	$menu_navigation[SECTION_CAMPUS] = $possible_tabs[SECTION_CAMPUS];
}

if ($_user['user_id'] && !api_is_anonymous()) {
	// My Courses
	if (api_get_setting('show_tabs', 'my_courses') == 'true') {
		$navigation['mycourses'] = $possible_tabs['mycourses'];
	} else {
		$menu_navigation['mycourses'] = $possible_tabs['mycourses'];
	}

	// My Profile
	if (api_get_setting('show_tabs', 'my_profile') == 'true' && api_get_setting('allow_social_tool') != 'true') {
		$navigation['myprofile'] = $possible_tabs['myprofile'];
	} else {
		$menu_navigation['myprofile'] = $possible_tabs['myprofile'];
	}

	// My Agenda
	if (api_get_setting('show_tabs', 'my_agenda') == 'true') {
		$navigation['myagenda'] = $possible_tabs['myagenda'];
	} else {
		$menu_navigation['myagenda'] = $possible_tabs['myagenda'];
	}

	// Gradebook
	if (api_get_setting('gradebook_enable') == 'true') {
		if (api_get_setting('show_tabs', 'my_gradebook') == 'true') {
			$navigation['mygradebook'] = $possible_tabs['mygradebook'];
		} else{
			$menu_navigation['mygradebook'] = $possible_tabs['mygradebook'];
		}
	}

	// Reporting
	if (api_get_setting('show_tabs', 'reporting') == 'true') {
		if (api_is_allowed_to_create_course() || api_is_drh() || api_is_session_admin()) {
			$navigation['session_my_space'] = $possible_tabs['session_my_space'];
		} else {
			$navigation['session_my_space'] = $possible_tabs['session_my_progress'];
		}
	} else {
		if (api_is_allowed_to_create_course() || api_is_drh() || api_is_session_admin()) {
			$menu_navigation['session_my_space'] = $possible_tabs['session_my_space'];
		} else {
			$menu_navigation['session_my_space'] = $possible_tabs['session_my_progress'];
		}
	}

	// Social Networking
	if (api_get_setting('show_tabs', 'social') == 'true') {
		if (api_get_setting('allow_social_tool') == 'true') {
			$navigation['social'] = $possible_tabs['social'];
		}
	} else{
		$menu_navigation['social'] = $possible_tabs['social'];
	}

	// Dashboard
	if (api_get_setting('show_tabs', 'dashboard') == 'true') {
		if (api_is_platform_admin() || api_is_drh() || api_is_session_admin()) {
			$navigation['dashboard'] = $possible_tabs['dashboard'];
		}
	} else{
		$menu_navigation['dashboard'] = $possible_tabs['dashboard'];
	}

	// Administration
	if (api_is_platform_admin(true)) {
		if (api_get_setting('show_tabs', 'platform_administration') == 'true') {
			$navigation['platform_admin'] = $possible_tabs['platform_admin'];
		} else {
			$menu_navigation['platform_admin'] = $possible_tabs['platform_admin'];
		}
	}
}
// Displaying the tabs
foreach ($navigation as $section => $navigation_info) {
	if (isset($GLOBALS['this_section'])) {
		$current = $section == $GLOBALS['this_section'] ? ' id="current"' : '';
	} else {
		$current = '';
	}
	echo '<li'.$current.'><a  href="'.$navigation_info['url'].'" target="_top"><span id="tab_active">'.$navigation_info['title'].'</span></a></li>'."\n";
}

$lang = ''; //el for "Edit Language"
if (!empty($_SESSION['user_language_choice'])) {
	$lang = $_SESSION['user_language_choice'];
} elseif (!empty($_SESSION['_user']['language'])) {
	$lang = $_SESSION['_user']['language'];
} else {
	$lang = get_setting('platformLanguage');
}

//Preparing home folder for multiple urls
global $_configuration;
if ($_configuration['multiple_access_urls']) {
	$access_url_id = api_get_current_access_url_id();
	if ($access_url_id != -1) {
		$url_info = api_get_access_url($access_url_id);
		$url = api_remove_trailing_slash(preg_replace('/https?:\/\//i', '', $url_info['url']));
		$clean_url = replace_dangerous_char($url);
		$clean_url = str_replace('/', '-', $clean_url);
		$clean_url .= '/';
		$homep            = api_get_path(SYS_PATH).'home/'.$clean_url; //homep for Home Path		       
		//we create the new dir for the new sites
		if (!is_dir($homep)) {
			mkdir($homep, api_get_permissions_for_new_directories());
		}
	}
} else {	
	$homep = api_get_path(SYS_PATH).'home/';
}

$ext = '.html';
$menutabs = 'home_tabs';

if (is_file($homep.$menutabs.'_'.$lang.$ext) && is_readable($homep.$menutabs.'_'.$lang.$ext)) {
	$home_top = @(string)file_get_contents($homep.$menutabs.'_'.$lang.$ext);    
} elseif (is_file($homep.$menutabs.$lang.$ext) && is_readable($homep.$menutabs.$lang.$ext)) {
	$home_top = @(string)file_get_contents($homep.$menutabs.$lang.$ext);
} else {
	$errorMsg = get_lang('HomePageFilesNotReadable');
}

$home_top = api_to_system_encoding($home_top, api_detect_encoding(strip_tags($home_top)));

if (api_get_self() != '/main/admin/configure_homepage.php') {
	$open = str_replace('{rel_path}',api_get_path(REL_PATH), $home_top);
	$open = api_to_system_encoding($open, api_detect_encoding(strip_tags($open)));
	echo $open;
} else {
	$home_menu = '';
	if (file_exists($homep.$menutabs.'_'.$lang.$ext)) {
		$home_menu = @file($homep.$menutabs.'_'.$lang.$ext);
	} else {
		$home_menu = @file($homep.$menutabs.$ext);
	}
	if (empty($home_menu)) {
		$home_menu = array();
	}
	if (!empty($home_menu)) {
		$home_menu = implode("\n", $home_menu);
		$home_menu = api_to_system_encoding($home_menu, api_detect_encoding(strip_tags($home_menu)));
		$home_menu = explode("\n", $home_menu);
	}
    $tab_counter = 0;
	foreach ($home_menu as $enreg) {
		$enreg = trim($enreg);
		if (!empty($enreg)) {
			$edit_link = '<a href="'.api_get_self().'?action=edit_tabs&amp;link_index='.$tab_counter.'" ><span>'.Display::return_icon('edit.gif', get_lang('Edit')).'</span></a>';
			$delete_link = '<a href="'.api_get_self().'?action=delete_tabs&amp;link_index='.$tab_counter.'"  onclick="javascript: if(!confirm(\''.addslashes(api_htmlentities(get_lang('ConfirmYourChoice'), ENT_QUOTES)).'\')) return false;"><span>'.Display::return_icon('delete.gif', get_lang('Delete')).'</span></a>';
			$tab_string = str_replace(array('href="'.api_get_path(WEB_PATH).'index.php?include=', '</li>'), array('href="'.api_get_path(WEB_CODE_PATH).'admin/'.basename(api_get_self()).'?action=open_link&link=', $edit_link.$delete_link.'</li>'), $enreg);
			echo $tab_string;
            $tab_counter++;
		}
	}
	echo '<li id="insert-link"> <a href="'.api_get_self().'?action=insert_tabs" style="padding-right:0px;"><span>'. Display::return_icon('addd.gif', get_lang('InsertLink'), array('style' => 'vertical-align:middle')).' '.get_lang('InsertLink').'</span></a></li>';
}

if ($_self == 'admin_intro_edition_page')
?>
		</ul>
		<div style="clear: both;" class="clear"> </div>
	</div>
<?php
/*
 * if the user is a coach he can see the users who are logged in its session
 */
$navigation = array();
// part 1: Course Homepage. If we are in a course then the first breadcrumb is a link to the course homepage
// hide_course_breadcrumb the parameter has been added to hide the name of the course, that appeared in the default $interbreadcrumb
$my_session_name = is_null($session_name) ? '' : '&nbsp;('.$session_name.')';
if (isset($_cid) and $_cid != -1 and isset($_course) and !isset($_GET['hide_course_breadcrumb'])) {
	$navigation_item['url'] = $web_course_path . $_course['path'].'/index.php'.(!empty($session_id) ? '?id_session='.$session_id : '');	
	switch (api_get_setting('breadcrumbs_course_homepage')) {
		case 'get_lang':
			$navigation_item['title'] = get_lang('CourseHomepageLink');
			break;
		case 'course_code':
			$navigation_item['title'] = $_course['official_code'];
			break;
		case 'session_name_and_course_title':
			$navigation_item['title'] = $_course['name'].$my_session_name;
			break;
		default:
			if (api_get_setting('use_session_mode') == 'true' && api_get_session_id() != -1 ) {	
				$navigation_item['title'] = $_course['name'].$my_session_name;
			} else {
				$navigation_item['title'] = $_course['name'];
			}
			break;
	}
	$navigation[] = $navigation_item;
}
// part 2: Interbreadcrumbs. If there is an array $interbreadcrumb defined then these have to appear before the last breadcrumb (which is the tool itself)
if (isset($interbreadcrumb) && is_array($interbreadcrumb)) {
	foreach ($interbreadcrumb as $breadcrumb_step) {
		$sep = (strrchr($breadcrumb_step['url'], '?') ? '&amp;' : '?');
		$navigation_item['url'] = $breadcrumb_step['url'].$sep.api_get_cidreq();
		$navigation_item['title'] = $breadcrumb_step['name'];
		// titles for shared folders
		if ($breadcrumb_step['name'] == 'shared_folder') {
			$navigation_item['title'] = get_lang('SharedFolder');
		}			
		elseif(strstr($breadcrumb_step['name'], 'shared_folder_session_')) {
		
			$navigation_item['title'] = get_lang('SharedFolder');
		}
		elseif(strstr($breadcrumb_step['name'], 'sf_user_')) {			
			$userinfo = Database::get_user_info_from_id(substr($breadcrumb_step['name'], 8));
			$navigation_item['title'] = api_get_person_name($userinfo['firstname'], $userinfo['lastname']);	
		}
		
		$navigation[] = $navigation_item;
	}
}
// part 3: The tool itself. If we are on the course homepage we do not want to display the title of the course because this
// is the same as the first part of the breadcrumbs (see part 1)
if (isset($nameTools) && $language_file != 'course_home') { // TODO: This condition $language_file != 'course_home' might bring surprises.
	$navigation_item['url'] = '#';
	$navigation_item['title'] = $nameTools;
	$navigation[] = $navigation_item;
}

$final_navigation = array();	
foreach ($navigation as $index => $navigation_info) {
	if (!empty($navigation_info['title'])) {
		$final_navigation[$index] = '<a href="'.$navigation_info['url'].'" class="breadcrumb breadcrumb'.$index.'" target="_top">'.$navigation_info['title'].'</a>';
	}
}

if (!empty($final_navigation)) {
	echo '<div id="header4">';
	echo implode(' &gt; ', $final_navigation);
	echo '</div>';
} else {
	echo '<div id="header4">';
	echo '</div>';
}
if (api_get_setting('show_toolshortcuts') == 'true') {
	echo '<div id="toolshortcuts">';
	require_once 'tool_navigation_menu.inc.php';
 	show_navigation_tool_shortcuts();
  	echo '</div>';
}

if (isset($dokeos_database_connection)) {
	// connect to the main database.
	// if single database, don't pefix table names with the main database name in SQL queries
	// (ex. SELECT * FROM `table`)
	// if multiple database, prefix table names with the course database name in SQL queries (or no prefix if the table is in
	// the main database)
	// (ex. SELECT * FROM `table_from_main_db`  -  SELECT * FROM `courseDB`.`table_from_course_db`)
	Database::select_db($_configuration['main_database'], $dokeos_database_connection);
}
?>

</div> <!-- end of the whole #header section -->
<div class="clear">&nbsp;</div>
<?php
//to mask the main div, set $header_hide_main_div to true in any script just before calling Display::display_header();
global $header_hide_main_div;
if (!empty($header_hide_main_div) && $header_hide_main_div === true) {
	//do nothing
} else {
?>

<div id="main"> <!-- start of #main wrapper for #content and #menu divs -->
<?php
}

/*	"call for chat" module section */

$chat = strpos(api_get_self(), 'chat_banner.php');
if (!$chat) {
	include_once api_get_path(LIBRARY_PATH).'online.inc.php';
	//echo $accept;
	$chatcall = chatcall();
	if ($chatcall) {
		Display :: display_normal_message($chatcall);
	}
}

/*	Navigation menu section */

if (api_get_setting('show_navigation_menu') != 'false' && api_get_setting('show_navigation_menu') != 'icons') {
	Display::show_course_navigation_menu($_GET['isHidden']);
	$course_id = api_get_course_id();
	if (!empty($course_id) && ($course_id != -1)) {
		echo '<div id="menuButton">';
 		echo $output_string_menu;
 		echo '</div>';
		if (isset($_SESSION['hideMenu'])) {
			if ($_SESSION['hideMenu'] == 'shown') {
 				if (isset($_cid)) {
					echo '<div id="centerwrap"> <!-- start of #centerwrap -->';
					echo '<div id="center"> <!-- start of #center -->';
				}
			}
 		} else {
			if (isset($_cid)) {
				echo '<div id="centerwrap"> <!-- start of #centerwrap -->';
				echo '<div id="center"> <!-- start of #center -->';
			}
 		}
 	}
}

?>
<!--   Begin Of script Output   -->
