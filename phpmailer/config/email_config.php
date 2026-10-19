<?php
/*
	Authors			: Muhd Eizan Shafiq Bin Abd Aziz, Zazaleena Binti Zakariah, Nursyahidah Binti Alias
	Date created	: 18 June 2011
	Description		: phpmailer config
*/
// define("HOST", "badang.uitm.edu.my");
// define("SMTP_DEBUG", 1);
// define("SMTP_AUTH", false);
// //define("SMTP_SECURE", "ssl");
// define("PORT", 25);
// define("USERNAME", "");
// define("PASSWORD", "");

//new email config
define("SMTP_HOST", "smtp.uitm.edu.my");
define("SMTP_DEBUG", 1);
define("SMTP_AUTH", false);
define("SMTP_AUTO_TLS", true);
define("SMTP_SECURE", "tls");
define("SMTP_PORT", 587);
?>