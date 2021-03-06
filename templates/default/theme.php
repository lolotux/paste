    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
<title><?php echo $page['title']?></title>
<script type="text/javascript" src="<?php echo $CONF['url'] . 'templates/' . $CONF['template']?>/tab.js"></script>
<link rel="shortcut icon" href="<?php echo $CONF['url'] . 'templates/' . $CONF['template']?>/images/favicon.ico" />
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $CONF['url'] . 'templates/' . $CONF['template']?>/style.css" />
<?php if (isset($page['post']['codecss']))
{
 echo '<style type="text/css">'."\n";
 echo $page['post']['codecss'];
 echo '</style>'."\n";
}
?>
</head>
 <body>
  <div class="header">
   <a href="<?php echo $CONF['url'] ?>"><img src="<?php echo $CONF['url'] . 'templates/' . $CONF['template']?>/images/logo.png" alt="<?php echo $CONF['title']?>" title="<?php echo $CONF['title'] ?>" class="logo" /></a>
	<ul class="tabs">
	 <li><a href="<?php echo $CONF['url']?>" title="Submit a new paste">Submit</a></li>
     <li><a href="<?php echo $CONF['url']?>?archive" title="List all public pastes">Archive</a></li>
	</ul>
</div>
<div id="menu">
<h1>RECENT PASTES</h1>
<ul>
<?php  
	foreach($page['recent'] as $idx=>$entry)
	{
		if (isset($pid) && $entry['pid']==$pid)
			$cls=" class=\"highlight\"";
		else
			$cls="";
			
		echo "<li" . $cls . "><a href=\"{$entry['url']}\">";
		echo $entry['poster'];
		echo "</a><br/>{$entry['agefmt']}<br /><br /></li>\n";
	}
?>
</ul>
</div>

<div id="content"><br />
<h1>Welcome! Here you can paste sources and general debugging text, You can even set yourself a password if you want to keep it just for yourself.</h1>

<div id="topInfo">
<?php
// Show errors.
if (count($pastebin->errors))
{
	echo "<h1>ERROR</h1><div id=\"errors\"><ul>";
	foreach($pastebin->errors as $err)
	{
		echo "<li>$err</li>";
	}
	echo "</ul></div>";
   $page['post']['editcode']=$_POST['code2'];
   $page['current_format']=$_POST['format'];
   $page['expiry']=$_POST['expiry'];
   
   if ($_POST['password'] != 'EMPTY') {
      $page['post']['password']=$_POST['password'];
   }
   $page['poster']=$_POST['poster'];
}

// Show a paste.
function showMe()
{
	global $sep;
	global $page;
	global $post;
	global $followups;
	global $CONF;

	if (strlen($page['post']['posttitle']))
	{
			echo "<h1>{$page['post']['posttitle']}";
			if ($page['post']['parent_pid']>0)
			{
				echo " (Modification of post by <a href=\"{$page['post']['parent_url']}\" title=\"View original post\">{$page['post']['parent_poster']}</a>)";
			}

			echo "<br/>";

			$followups=count($page['post']['followups']);
			if ($followups)
			{
				echo "View followups from ";
				$sep="";
				foreach($page['post']['followups'] as $idx=>$followup)
				{
					echo $sep."<a title=\"posted {$followup['postfmt']}\" href=\"{$followup['followup_url']}\">{$followup['poster']}</a>";
					$sep=($idx<($followups-2))?", ":" and ";	
				}
				echo " | ";
			}

			echo "<a href=\"{$page['post']['downloadurl']}\" title=\"Download this paste\">Download</a> | ";
			echo "<a href=\"{$CONF['url']}\" title=\"Make a new paste\">New paste</a>";
			echo "</h1>";
	}
	if (isset($page['post']['pid']))
	{
		echo "<div id=\"syntax\">".$page['post']['codefmt']."</div>";
	}
}

// Check for a password.
if (isset($_POST['thePassword']))
{
$postPass = $_POST['thePassword'];
}

if (isset($pid))
{
if ($pid >0)
{
	global $pid;
    $db = new DB;
    $newPID = $db->_escape_string($pid);
    $result = $db->_query("SELECT * from paste where pid = " . $newPID);
    $row = $db->_fetch_array($result);

	$pass = $row['password'];

	if (isset($pass) && ($pass != "EMPTY"))
	{
		if (!isset($postPass))
		{
   			echo "<center><form name=\"editor\" method=\"post\" action=\"\"";
   			echo "<label class=\"passProtected\" for=\"thePass\">Password </label>";
   			echo "<input type=\"password\" name=\"thePassword\" /> ";
   			echo "<input type=\"submit\" name=\"showUs\" value=\"Submit\" />";
   			echo "</form></center>";
		}

		else if (strcmp($postPass, $pass) == 0) {
   		showMe();
		}

		else {
		echo "<h1>Oops!</h1><br />";
	   	echo "<center><span class=\"error\"> Sorry, the password you entered was incorrect.<br /><br /></span></center>";
		}
	}
	
	else {
   	showMe();
	}
	
}
}
if (isset($_GET['archive']))
{
	?>
	<h1>Archive (This could take a while to load)</h1>
	<?php

    $db = new DB;
	$pastes = $db->_query("SELECT * FROM paste ORDER BY posted DESC");


	echo "<table class=\"archive\">";
	echo "<tr><th></th><th>Name</th><th class=\"padright\">Language</th><th>Posted on</th><th>Expires</th></tr>";
	while ($row = $db->_fetch_array($pastes))
	{
        $pass = ($row['password'] == "EMPTY") ? "" : "<img src=\"templates/default/images/lock.png\" title=\"Password protected\" />";
        echo "<tr>";
        echo "<td>" . $pass . "</td>";
		echo "<td class=\"padright\"><a title=\"" . date("l F j, Y, g:i a", strtotime($row['posted'])) . "\" href=\"". $CONF['pastebin'] . "/" . $row['pid'] . "\">" . $row['poster'] . "</a></td>";
		echo "<td>" . $CONF['geshiformats'][$row['format']] . "</td>";
        echo "<td class=\"padright\">" . date("m-d-y, g:i A", strtotime($row['posted'])) . "</td>";
        echo "<td>" . ((is_null($row['expires'])) ? "Never" : date("m-d-y, g:i A", strtotime($row['expires'])))  . "</td>";
		echo "</tr>";
	}

	echo "</table>";
}
else
{
?>
</div>
<? if (!(isset($pass) && strcmp($postPass, $pass) != 0) || $pass == "EMPTY") {?>
<form name="editor" method="post" action="index.php">
 <input type="hidden" name="parent_pid" value="<?php echo $page['post']['pid'] ?>"/>
 <div id="paste">
 <div id="fmt">Language: <select name="format">
<?php
// Show popular GeSHi formats
foreach ($CONF['geshiformats'] as $code=>$name)
{
	if (in_array($code, $CONF['popular_syntax']))
	{
		$sel=($code==$page['current_format'])?"selected=\"selected\"":"";
		echo "<option $sel value=\"$code\">$name</option>";
	}
}

echo "<option value=\"text\">----------------------------</option>";

// Show all GeSHi formats.
foreach ($CONF['geshiformats'] as $code=>$name)
{
	$sel=($code==$page['current_format'])?"selected=\"selected\"":"";
	if (in_array($code, $CONF['popular_syntax']))
		$sel="";
	echo "<option $sel value=\"$code\">$name</option>";
}
?>
 </select>
</div>

<div id="notes">To highlight particular lines, prefix each line with <?php echo $CONF['highlight_prefix'] ?></div>

<!-- Code edit box -->
 <textarea id="code" class="codeedit" name="code2" cols="90" rows="20" onkeydown="return catchTab(this,event)"><?php echo htmlentities($page['post']['editcode'], ENT_COMPAT, "UTF-8") ?></textarea>
</div>

<div id="pasteInfo">
<div class="end"></div>
<!-- The name box -->
<div id="namebox"> <label for="poster">Name/Title (Optional)</label><br/>
 <input type="text" maxlength="24" size="24" id="poster" name="poster" value="<?php echo $page['poster'] ?>" />
 <input type="submit" name="paste" value="Submit"/> <br />
</div>

<!-- The expiry buttons -->
<div id="expirybox">
  <div id="expiryradios"><label>How long should we keep your paste?</label><br/> 
   <input type="radio" id="expiry_day" name="expiry" value="d"   <?php if ($page['expiry']=='d') echo 'checked="checked"'; ?> /> <label id="expiry_day_label" for="expiry_day">One day</label>
   <input type="radio" id="expiry_month" name="expiry" value="m" <?php if ($page['expiry']=='m') echo 'checked="checked"'; ?> /> <label id="expiry_month_label" for="expiry_month">One month</label>
   <input type="radio" id="expiry_forever" name="expiry" value="f" <?php if ($page['expiry']=='f') echo 'checked="checked"'; ?> /> <label id="expiry_forever_label" for="expiry_forever">Forever</label>
  </div>
 <div id="expiryinfo"></div>
</div>

<!-- The password box -->
<div id="password">
 <label class="passProtected" for="password">Password (Optional)</label><br />
 <input type="password" class="bringDown" size="21" value="<?php if (strcmp($page['post']['password'],'EMPTY') != 0) { echo $page['post']['password']; } else { echo ''; } ?>" name="password" />
</div>

<?php
if ($CONF['useRecaptcha']) {
require_once('classes/recaptchalib.php');
?>
 <!-- reCAPTCHA -->
 <script>
var RecaptchaOptions = {
   theme : 'clean'
};
</script>
<div id="recaptcha">
 <?php echo recaptcha_get_html($CONF['pubkey'])."\n"; ?>
</div>
<?php } ?>
 
<?php } ?>
<div class="end"></div>
<?php } ?>
 <br />
  <h1>&copy; <?php echo date("Y"); ?> - Powered by <a href="http://sourceforge.net/projects/phpaste/">PASTE</a> 1.0</h1>
    </div>
   </form>
  </div>
 </body>
</html>
