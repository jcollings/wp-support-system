<?php

if(!is_admin())
	return;

// load classes
require 'includes/class-wt-settings.php';
require 'includes/class-wt-admin-departments.php';
require 'includes/class-wt-admin-ticketstatus.php';
require 'includes/class-wt-admin-ticketarchive.php';

// load sections
require 'editor/editor-init.php';