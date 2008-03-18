<?php // $Id: document.inc.php 13981 2007-12-08 23:43:58Z yannoo
/*
==============================================================================
	Dokeos - elearning and course management software

	Copyright (c) 2004-2008 Dokeos S.A.
	Copyright (c) 2003 Ghent University (UGent)
	Copyright (c) 2001 Universite catholique de Louvain (UCL)
	Copyright (c) various contributors

	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	See the GNU General Public License for more details.

	Contact address: Dokeos, rue du Corbeau, 108, B-1030 Brussels, Belgium
	Mail: info@dokeos.com
==============================================================================
*/

/*
==============================================================================
		EXTRA FUNCTIONS FOR DOCUMENT.PHP/UPLOAD.PHP
==============================================================================
/////////////////////////////////////////////////
//--> leave these here or move them elsewhere? //
/////////////////////////////////////////////////
*/


/**
 * Builds the form thats enables the user to 
 * select a directory to browse/upload in
 *
 * @param array 	An array containing the folders we want to be able to select
 * @param string	The current folder (path inside of the "document" directory, including the prefix "/")
 * @param string	Group directory, if empty, prevents documents to be uploaded (because group documents cannot be uploaded in root)
 * @param	boolean	Whether to change the renderer (this will add a template <span> to the QuickForm object displaying the form)
 * @return string html form
 */
function build_directory_selector($folders,$curdirpath,$group_dir='',$changeRenderer=false)
{
	$folder_titles = array();
	if(get_setting('use_document_title') == 'true')
	{
		$escaped_folders = $folders;
		array_walk($escaped_folders, 'mysql_real_escape_string');
		$folder_sql = implode("','",$escaped_folders);
		$doc_table = Database::get_course_table(TABLE_DOCUMENT);
		$sql = "SELECT * FROM $doc_table WHERE filetype='folder' AND path IN ('".$folder_sql."')";
		$res = api_sql_query($sql,__FILE__,__LINE__);
		$folder_titles = array();
		while($obj = mysql_fetch_object($res))
		{
			$folder_titles[$obj->path] = $obj->title;	
		}
	}
	else
	{
		foreach($folders as $folder)
		{
			$folder_titles[$folder] = basename($folder);	
		}	
	}
	
	require_once (api_get_path(LIBRARY_PATH).'formvalidator/FormValidator.class.php');
	$form = new FormValidator('selector','POST',api_get_self());
	
	$parent_select = $form->addElement('select', 'curdirpath', get_lang('CurrentDirectory'),'','onchange="javascript:document.selector.submit()"');
	
	if($changeRenderer==true){
		$renderer = $form->defaultRenderer();
		$renderer->setElementTemplate('<span>{label} : {element}</span> ','curdirpath');
	}
	
	//group documents cannot be uploaded in the root
	if($group_dir=='')
	{
		$parent_select -> addOption(get_lang('HomeDirectory'),'/');
		if(is_array($folders))
		{
			foreach ($folders as $folder)
			{
				$selected = ($curdirpath==$folder)?' selected="selected"':'';
				$path_parts = explode('/',$folder);
				$label = str_repeat('&nbsp;&nbsp;&nbsp;',count($path_parts)-2).' &mdash; '.$folder_titles[$folder];
				$parent_select -> addOption($label,$folder);
				if($selected!='') $parent_select->setSelected($folder);
			}
		}
	}
	else
	{
		foreach ($folders as $folder)
		{
			$selected = ($curdirpath==$folder)?' selected="selected"':'';
			$label = $folder_titles[$folder];
			if( $folder == $group_dir)
			{
				$label = '/ ('.get_lang('HomeDirectory').')';
			}
			else
			{
				$path_parts = explode('/',str_replace($group_dir,'',$folder));
				$label = str_repeat('&nbsp;&nbsp;&nbsp;',count($path_parts)-2).' &mdash; '.$label;			
			}
			$parent_select -> addOption($label,$folder);
			if($selected!='') $parent_select->setSelected($folder);
		}
	}
	
	$form=$form->toHtml();

	return $form;
}


function display_document_options()
{
	$message = "<a href=\"quota.php?".api_get_cidreq()."\">".get_lang("ShowCourseQuotaUse")."</a>";
	echo 	/*"<div id=\"smallmessagebox\">"
			.*/ "<p>" . $message . "</p>"
			/*. "</div>"*/;
}

/**
 * Create a html hyperlink depending on if it's a folder or a file
 *
 * @param string $www
 * @param string $title
 * @param string $path
 * @param string $filetype (file/folder)
 * @param int $visibility (1/0)
 * @return string url
 */
function create_document_link($www,$title,$path,$filetype,$size,$visibility)
{
	global $dbl_click_id;
	if(isset($_SESSION['_gid']))
	{
		$req_gid = '&amp;gidReq='.$_SESSION['_gid'];
	}
	else 
	{
		$req_gid = '';
	}
	$url_path = urlencode($path);
	//add class="invisible" on invisible files
	$visibility_class= ($visibility==0)?' class="invisible"':'';
	//build download link (icon)
	$forcedownload_link=($filetype=='folder')?api_get_self().'?'.api_get_cidreq().'&action=downloadfolder&amp;path='.$url_path.$req_gid:api_get_self().'?'.api_get_cidreq().'&amp;action=download&amp;id='.$url_path.$req_gid;
	//folder download or file download?
	$forcedownload_icon=($filetype=='folder')?'folder_zip.gif':'filesave.gif';
	//prevent multiple clicks on zipped folder download
	$prevent_multiple_click =($filetype=='folder')?" onclick=\"javascript:if(typeof clic_$dbl_click_id == 'undefined' || clic_$dbl_click_id == false) { clic_$dbl_click_id=true; window.setTimeout('clic_".($dbl_click_id++)."=false;',10000); } else { return false; }\"":'';
	$target='_top';
	if($filetype=='file') {
		//check the extension
		$ext=explode('.',$path);
		$ext=strtolower($ext[sizeof($ext)-1]);
		//"htmlfiles" are shown in a frameset
		if($ext == 'htm' || $ext == 'html' || $ext == 'gif' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'png')
		{
			$url = "showinframes.php?".api_get_cidreq()."&amp;file=".$url_path.$req_gid;
		}
		else 
		{
			//url-encode for problematic characters (we may not call them dangerous characters...)
			$path = str_replace('%2F', '/',$url_path).'?'.api_get_cidreq();
			$url=$www.$path;
		}
		//files that we want opened in a new window
		if($ext=='txt') //add here
		{
			$target='_blank';
		}
	}
	else {
		$url=api_get_self().'?'.api_get_cidreq().'&amp;curdirpath='.$url_path.$req_gid;
	}
	//the little download icon
	$force_download_html = ($size==0)?'':'<a href="'.$forcedownload_link.'" style="float:right"'.$prevent_multiple_click.'><img width="16" height="16" src="'.api_get_path(WEB_CODE_PATH).'img/'.$forcedownload_icon.'" alt="" /></a>';
	
	$tooltip_title = str_replace('?cidReq='.$_GET['cidReq'],'',basename($path));
	return '<a href="'.$url.'" title="'.$tooltip_title.'" target="'.$target.'"'.$visibility_class.' style="float:left">'.$title.'</a>'.$force_download_html;
}

/**
 * Builds an img html tag for the filetype
 *
 * @param string $type (file/folder)
 * @param string $path
 * @return string img html tag
 */
function build_document_icon_tag($type,$path)
{
	$icon='folder_document.gif';
	if($type=='file')
	{
		$icon=choose_image(basename($path));
	}
	return '<img src="'.api_get_path(WEB_CODE_PATH).'img/'.$icon.'" border="0" hspace="5" align="middle" alt="" />';
}

/**
 * Creates the row of edit icons for a file/folder
 *
 * @param string $curdirpath current path (cfr open folder)
 * @param string $type (file/folder)
 * @param string $path dbase path of file/folder
 * @param int $visibility (1/0)
 * @param int $id dbase id of the document
 * @return string html img tags with hyperlinks
 */
function build_edit_icons($curdirpath,$type,$path,$visibility,$id,$is_template)
{
	if(isset($_SESSION['_gid']))
	{
		$req_gid = '&amp;gidReq='.$_SESSION['_gid'];
	}
	else 
	{
		$req_gid = '';
	}
	//build URL-parameters for table-sorting
	$sort_params = array();
	if( isset($_GET['column']))
	{
		$sort_params[] = 'column='.$_GET['column'];
	}
	if( isset($_GET['page_nr']))
	{
		$sort_params[] = 'page_nr='.$_GET['page_nr'];
	}
	if( isset($_GET['per_page']))
	{
		$sort_params[] = 'per_page='.$_GET['per_page'];
	}
	if( isset($_GET['direction']))
	{
		$sort_params[] = 'direction='.$_GET['direction'];
	}	
	$sort_params = implode('&amp;',$sort_params);
	$visibility_icon = ($visibility==0)?'invisible':'visible';
	$visibility_command = ($visibility==0)?'set_visible':'set_invisible';
	$curdirpath = urlencode($curdirpath);
	
	$modify_icons = '<a href="edit_document.php?'.api_get_cidreq().'&curdirpath='.$curdirpath.'&amp;file='.urlencode($path).$req_gid.'"><img src="../img/edit.gif" border="0" title="'.get_lang('Modify').'" alt="" /></a>';
	$modify_icons .= '&nbsp;<a href="'.api_get_self().'?'.api_get_cidreq().'&curdirpath='.$curdirpath.'&amp;delete='.urlencode($path).$req_gid.'&amp;'.$sort_params.'" onclick="return confirmation(\''.basename($path).'\');"><img src="../img/delete.gif" border="0" title="'.get_lang('Delete').'" alt="" /></a>';
	$modify_icons .= '&nbsp;<a href="'.api_get_self().'?'.api_get_cidreq().'&curdirpath='.$curdirpath.'&amp;move='.urlencode($path).$req_gid.'"><img src="../img/deplacer_fichier.gif" border="0" title="'.get_lang('Move').'" alt="" /></a>';
	$modify_icons .= '&nbsp;<a href="'.api_get_self().'?'.api_get_cidreq().'&curdirpath='.$curdirpath.'&amp;'.$visibility_command.'='.$id.$req_gid.'&amp;'.$sort_params.'"><img src="../img/'.$visibility_icon.'.gif" border="0" title="'.get_lang('Visible').'" alt="" /></a>';
	
	if($type == 'file' && pathinfo($path,PATHINFO_EXTENSION)=='html'){
		if($is_template==0){
			$modify_icons .= '&nbsp;<a href="'.api_get_self().'?'.api_get_cidreq().'&curdirpath='.$curdirpath.'&amp;add_as_template='.$id.$req_gid.'&amp;'.$sort_params.'"><img src="../img/wizard_small.gif" border="0" title="'.get_lang('AddAsTemplate').'" alt="'.get_lang('AddAsTemplate').'" /></a>';
		}
		else{
			$modify_icons .= '&nbsp;<a href="'.api_get_self().'?'.api_get_cidreq().'&curdirpath='.$curdirpath.'&amp;remove_as_template='.$id.$req_gid.'&amp;'.$sort_params.'"><img src="../img/wizard_gray_small.gif" border="0" title="'.get_lang('RemoveAsTemplate').'" alt=""'.get_lang('RemoveAsTemplate').'" /></a>';
		}
	}
	
	
	return $modify_icons;
}


function build_move_to_selector($folders,$curdirpath,$move_file,$group_dir='')
{
	$form = '<form name="move_to" action="'.api_get_self().'" method="post">'."\n";
	$form .= '<input type="hidden" name="move_file" value="'.$move_file.'" />'."\n";
	$form .= get_lang('MoveTo').' <select name="move_to">'."\n";
	
	//group documents cannot be uploaded in the root
	if($group_dir=='') 
	{
		if($curdirpath!='/')
		{
			$form .= '<option value="/">/ ('.get_lang('HomeDirectory').')</option>';
		}
		if(is_array($folders))
		{
			foreach ($folders AS $folder)
			{	
				//you cannot move a file to:
				//1. current directory
				//2. inside the folder you want to move
				//3. inside a subfolder of the folder you want to move
				if(($curdirpath!=$folder) && ($folder!=$move_file) && (substr($folder,0,strlen($move_file)+1) != $move_file.'/'))
				{
					$form .= '<option value="'.$folder.'">'.$folder.'</option>'."\n";
				}
			}
		}
	}
	else
	{
		foreach ($folders AS $folder)
		{	
			if(($curdirpath!=$folder) && ($folder!=$move_file) && (substr($folder,0,strlen($move_file)+1) != $move_file.'/'))//cannot copy dir into his own subdir
			{
				$display_folder = substr($folder,strlen($group_dir));
				$display_folder = ($display_folder == '')?'/ ('.get_lang('HomeDirectory').')':$display_folder;
				$form .= '<option value="'.$folder.'">'.$display_folder.'</option>'."\n";
			}
		}
	}

	$form .= '</select>'."\n";
	$form .= '<input type="submit" name="move_file_submit" value="'.get_lang('Ok').'" />'."\n";
	$form .= '</form>';

	return $form;
}
?>