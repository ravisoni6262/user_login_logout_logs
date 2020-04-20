<?php
/**
 * Plugin Name: Keep log of user login and logout.
 * Plugin URI:  https://codex.wordpress.org/Writing_a_Plugin
 * Description: <code>Create a log file at user defined location which hold entries of user login and logout.
 * Version: 1.0.0
 * Author: Ravi Soni
 * Text Domain: usr_log_in_out
 */
 
if (!defined('ABSPATH')) {
	exit;
}
define('KEEP_LOG_OF_USER_LOGIN_AND_LOGOUT_URL', plugin_dir_url(__FILE__));
class KEEP_LOG_OF_USER_LOGIN_AND_LOGOUT {
	public function __construct()
	{	 
		// after login add data in log file
		add_action('wp_login', array($this, 'afterLogin'), 10, 2);
		
		// Before logout add data in log file
		add_action('clear_auth_cookie', array($this, 'beforeLogout'));

		// add menu in admin dashboard hook
		add_action('admin_menu', array($this, 'auditLogViewer'));

		//add admin wp style hook
		add_action('admin_enqueue_scripts', array($this, 'adminStyleAdd'));

		$getPath = get_option('keep_log_of_login_logout_log_path');
		if (!$getPath) {
	 		update_option('keep_log_of_login_logout_log_path', ABSPATH);
		}
	}
	/**
	 * Function auditLogViewer()
	 *
	 * Created By Ravi Soni.
	 *
	 * Add menu in admin dashboard.
	 *
	 * @param None.
	 *
	 * @return none.
	*/
	public function auditLogViewer()
	{
		add_menu_page(
		    __('Audit Log Viewer', 'usr_log_in_out'),
		    __('Audit Log Viewer', 'usr_log_in_out'),
		    'manage_options',
		    'keep-log-of-user-login-and-logout',
		    array($this, 'auditLogmenuDescription'),
		    'dashicons-visibility'
	 	);
	 	add_submenu_page(
	 		'keep-log-of-user-login-and-logout',
	 		'Download log',
	 		"Download log",
	 		'manage_options',
	 		'log-download',
	 		array($this, 'downloadOption')
	 	);
	}

	/**
	 * Function downloadOption()
	 *
	 * Created By Ravi Soni.
	 *
	 * sub menu page description in admin dashboard.
	 *
	 * @param None.
	 *
	 * @return none.
	*/
	public function downloadOption()
	{
		$path = get_option('keep_log_of_login_logout_log_file_name');
		if (file_exists($path)) {
			$absPath = str_replace(ABSPATH, "", $path);
			if ($absPath) {
				$absPath = '/'.$absPath;
			}
			?>
			<div class="keep-log-download-option">
				<a href="<?php echo site_url($absPath); ?>" download>
					<?php
					esc_html_e("Click here to download Log file.", 'usr_log_in_out');
					?></a>
			</div>
			<?php
		} else {
			echo "<div class='keep-log-download-option'>";
			esc_html_e("File directory not found.", 'usr_log_in_out');
			echo "</div>";
		}
	}

	/**
	 * Function auditLogmenuDescription()
	 *
	 * Created By Ravi Soni.
	 *
	 * menu option.
	 *
	 * @param None.
	 *
	 * @return None.
	*/
	public function auditLogmenuDescription()
	{
		if (isset($_POST['log_submit_file_path'])) {
			$savePath = ABSPATH.$_POST['log_save_file_path'];
 			update_option('keep_log_of_login_logout_log_path', $savePath);
 			$myfile = fopen($savePath."login-logout-log.txt", "w")
				or die("Unable to open file!");
			update_option('keep_log_of_login_logout_log_file_name', $savePath."login-logout-log.txt");
			fclose($myfile);
 		}
		$getPath = get_option('keep_log_of_login_logout_log_path');
	 	$getPath = str_replace(ABSPATH, '', $getPath);
 		?>
 		<div class="select-file-path-form">
 			<h1><?php esc_html_e("Set Log file path", 'usr_log_in_out'); ?></h1>
	 		<form method="post">
	 			<p><?php esc_html_e("Directory name (Must end with /)", 'usr_log_in_out'); ?></p>
	 			<p><input type="text" class="user-file_name" name="log_save_file_path"
	 			value="<?php echo $getPath; ?>">
	 			</p>
	 			<p>(<?php
	 				esc_html_e('Wordpress absolute path is: ', 'usr_log_in_out');
	 				echo ABSPATH;
	 				?> )</p>
	 			<p>
	 			<input type="submit" name="log_submit_file_path"
	 			value="<?php esc_html_e("Submit", 'usr_log_in_out'); ?>"></p>
	 		</form>
 		</div>
 		<?php
	}

	/**
	 * Function afterLogin()
	 *
	 * Created By Ravi Soni.
	 *
	 * After login entry data from log file.
	 *
	 * @param $userName, $current_user.
	 *
	 * @return true.
	*/
	public function afterLogin($userName, $current_user)
	{
		$path = get_option('keep_log_of_login_logout_log_path');
		if ($path) {
			$fileData = file_get_contents($path."login-logout-log.txt");
			$myfile = fopen($path."login-logout-log.txt", "w")
				or die("Unable to open file!");
			if ($fileData) {
				$txt = $fileData;
				fwrite($myfile, $txt);
			}
			$txt = date('d-m-Y h:i:s').", ".$current_user->user_login. ", " .
			$current_user->roles[0].", ". $_SERVER['REMOTE_ADDR'] . ", " . "Login\n";
			fwrite($myfile, $txt);
			fclose($myfile);
		}
		return true;
	}

	/**
	 * Function beforeLogout()
	 *
	 * Created By Ravi Soni.
	 *
	 * Before logout entry data from log file.
	 *
	 * @param global $current_user.
	 *
	 * @return none.
	*/
	public function beforeLogout()
	{
		$path = get_option('keep_log_of_login_logout_log_path');
		if ($path) {
			global $current_user;
			$user = wp_get_current_user();
			$fileData = file_get_contents($path."login-logout-log.txt");
			$myfile = fopen($path."login-logout-log.txt", "w")
			or
			die("Unable to open file!");
			if ($fileData) {
				$txt = $fileData;
				fwrite($myfile, $txt);
			}
			$txt = date('d-m-Y h:i:s').", ".$current_user->user_login. ", "
			.$user->roles[0] .", ". $_SERVER['REMOTE_ADDR'] . ", " . "Logout \n";
			fwrite($myfile, $txt);
			fclose($myfile);
		}
	}

	/**
	 * Function adminStyleAdd().
	 *
	 * Created By Ravi Soni.
	 *
	 * Add enqueue style in admin.
	 *
	 * @param None.
	 *
	 * @return None.
	*/
	public function adminStyleAdd()
	{
	 	wp_enqueue_style(
	    	'keep_log_of_usr_log_in_and_out_style',
	    	KEEP_LOG_OF_USER_LOGIN_AND_LOGOUT_URL.
	    	'asset/css/keep_log_of_usr_log_in_and_out.css'
	    );
	}
}
$KEEP_LOG_OF_USER_LOGIN_AND_LOGOUT = new KEEP_LOG_OF_USER_LOGIN_AND_LOGOUT();