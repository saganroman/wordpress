<?php 	if ( ! defined( 'ABSPATH' ) ) exit;	$default_url =  SAHU_SO_PLUGIN_URL.'assets/img/bg.jpg'; 	$default_url2 =  SAHU_SO_PLUGIN_URL.'assets/img/logo.png'; 		/******************* DASHBOARD *************************************	********************************************************************/			$sahu_dashboard = serialize( array(	'sahu_so_status' 		       => "0",	'so_headline' 		       => "Site Offline",	'so_description' 		       => "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut vel fermentum dui. Pellentesque vitae porttitor ex, euismod sodales magna. Nunc sed felis sed dui pellentesque sodales porta a magna. Donec dui augue, dignissim faucibus lorem nec, fringilla molestie massa. Sed blandit dapibus bibendum. Sed interdum commodo laoreet. Sed mi orci.",	'display_logo' 		       => "0",	'so_logo_url' 		       => $default_url2,		) );	add_option('sahu_so_dashboard', $sahu_dashboard);			/******************* SEO SETTINGS *******************************	********************************************************************/				$sahu_seo = serialize( array(	'sahu_so_favicon' 		       => $default_url2,	'sahu_so_seo_title' 		   => "Site Offline",	'sahu_so_seo_desc' 		       => "",	'sahu_so_seo_analytiso' 	   => "",		) );	add_option('sahu_so_seo', $sahu_seo);			/******************* Design  *******************************	********************************************************************/				$sahu_design = serialize( array(	'sahu_so_select_bg' 		       => "1",	'sahu_so_bg_clr' 		       => "#1e73be",	'sahu_so_bg_img' 		       => $default_url,	'sahu_headeline_ft_clr' 		       => "#ffffff",	'sahu_desc_ft_clr' 		       => "#ffffff",	'sahu_cnt_ft_clr' 		       => "#ffffff",	'sahu_social_clr' 		       => "#ffffff",	'sahu_headline_ft_size' 		       => "80",	'sahu_desc_ft_size' 		       => "21",	'sahu_ft_st' 		       => "Verdana",	'sahu_so_custom_css' 		       => "",		) );	add_option('sahu_so_design', $sahu_design);		/******************* Countdown  *******************************	********************************************************************/				$sahu_countdown = serialize( array(	'cnt_enable' 		       => "yes",	'countdown_date' 		   => "2017/11/25",	'countdown_time' 		   => "10:50",			) );	add_option('sahu_so_countdown', $sahu_countdown);		/******************* Social *******************************	********************************************************************/			$sahu_social = serialize( array(	'sahu_so_fb' 		       => "#",	'sahu_so_twit' 		       => "#",	'sahu_so_ln' 		       => "#",	'sahu_so_gp'    		   => "#",		) );	add_option('sahu_so_social', $sahu_social);		/******************* Contact *******************************	********************************************************************/				$sahu_contact = serialize( array(	'sahu_so_address' 		       => "123 Street, City",	'sahu_so_no' 		       => "(00) 123-4567890",	'sahu_so_email'    		   => "email@example.com",		) );	add_option('sahu_so_contact', $sahu_contact);	?>