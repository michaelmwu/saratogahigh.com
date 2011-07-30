<?
// Michael Wu | saratogahigh.com
// shcp/commentsfaq.php: FAQ for account problems
require '../inc/config.php';
require 'cpvalidation.php';
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title>Account Comments FAQ</title>
	<link rel="stylesheet" type="text/css" href="../shs.css">
	<meta name="vs_targetSchema" content="http://schemas.microsoft.com/intellisense/ie5">
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
	<style type="text/css">
		dt { padding-top: 12px; }
	</style>
</head>
<body>

<? include "inc-header.php" ?>

<h1>User Account Comments FAQ</h1>

<p>People have problems all the time with setting up accounts or remembering passwords. Thus, they send us comments asking for help. These are located under the Admin tab up top, and both General Comments and Password Requests. Since our filtering system isn't perfect, a lot of account requests leak into general comments. Here is a guideline on how to respond to their comments. There are 4 cases of account comments, all covered here.</p>

<p>Our emails are generally supposed to look like this:</p>

<div style="width: 600px; border: 1px solid #000000;">
To: <i>email address</i>
<br>CC: staff@saratogahigh.com
<br>
<br>Subject: <i>appropriate subject</i>
<br>
<br>Dear <i>first name</i> (if not an adult) <b>or</b> <i>Mr./Ms./Mrs. Last Name</i> (if adult),
<br>
<br>&nbsp;&nbsp;&nbsp;&nbsp;<i>appropriate message here</i>
<br>
<br>Sincerely,
<br><i>Your Name</i> and the staff of SaratogaHigh.com</p>
</div>

<dl>
<dt>Case 1: I forgot my password / I can't login</dt>
<dd>
<ol style="list-style-type: decimal;">
<li>Use the Directory Search to find their account.</li>
<li>Go to Edit Account Info.</li>
<li>If necessary, reset their email to the email they sent the comment from.</li>
<li>Send them an email telling them their username and email, and to use the forgot password feature.</li>
</ol>
<div style="padding-top: 12px;"><b>Note:</b> Some people try to "login" when they really need to create accounts. Refer to <b>Case 2</b>.</div>
</dd>

<dt>Case 2: Code doesn't work / Creating account doesn't work.</dt>
<dd>
<ol style="list-style-type: decimal;">
<li>Use the Directory Search to find their account.</li>
<li>Go to Edit Account Info.</li>
<li>Send them an email telling them to fill out their Exact First & Last Name (email them that too), and the first 3 characters of their activation code.</li>
<li>Also tell them to fill out their email, username, and password.</li>
</ol>
</dd>

<dt>Case 3: I don't have a code</dt>
<dd>
<ol style="list-style-type: decimal;">
<li>Use the Directory Search to find their account.</li>
<li>Go to Edit Account Info.</li>
<li>Tell them to go to Mrs. Fong in the office to get one.</li>
<li>As an alternative, you can offer to mail it to them or give it over phone.</li>
</ol>
</dd>

<dt>Case 4: You look them up for a previous case and they don't even exist</dt>
<dd>
<ol style="list-style-type: decimal;">
<li>Use the Create New Account function in the Control Panel to create the accounts.</li>
<li>Don't forget to consider parents or kids.</li>
<li>If you need to link parents to kids, talk to Michael, or at least until Michael gets the parent editing function up.</li>
<li>Tell them to go to Mrs. Fong in the office to get a code.</li>
<li>As an alternative, you can offer to mail it to them or give it over phone.</li>
</ol>
</dd>
</dl>

<p>I'm planning to add an online email replying thing, but that's in the future.</p>

<p>Most important, be courteous! Don't forget to respond in a timely manner either! If you have any questions or troubles, just talk to Michael.</p>

<? $page->footer(); ?>